<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentGateways\BelibayarQrisService;
use App\Services\PaymentGateways\PaymentFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BelibayarWebhookController extends Controller
{
    protected BelibayarQrisService $belibayarService;
    protected PaymentFulfillmentService $fulfillmentService;

    public function __construct(
        BelibayarQrisService $belibayarService,
        PaymentFulfillmentService $fulfillmentService
    ) {
        $this->belibayarService = $belibayarService;
        $this->fulfillmentService = $fulfillmentService;
    }

    /**
     * Handle incoming callback webhook from Belibayar.id
     */
    public function handle(Request $request): JsonResponse
    {
        $rawContent = $request->getContent();
        $signature = (string) (
            $request->header('X-Belibayar-Signature') 
            ?? $request->header('X-Signature') 
            ?? $request->header('X-Callback-Signature') 
            ?? $request->header('Signature') 
            ?? ''
        );
        $webhookSecret = $this->belibayarService->getWebhookSecret();
        $isProduction = $this->belibayarService->isProduction();

        $payload = $request->json()->all();
        if (empty($payload)) {
            $payload = $request->all();
        }

        $secretKey = $this->belibayarService->getSecretKey();

        Log::info('Belibayar Webhook Received', [
            'signature_header' => $signature,
            'body' => $payload,
        ]);

        // 1. Handle Belibayar dashboard test ping ("Kirim Tes Webhook") immediately with 200 OK
        $isTestEvent = ($request->header('X-Belibayar-Test') === 'true')
            || ($request->header('X-Belibayar-Event') === 'payment.test')
            || (isset($payload['event']) && $payload['event'] === 'payment.test')
            || (isset($payload['channel']) && $payload['channel'] === 'TEST')
            || (isset($payload['reference']) && str_starts_with((string) $payload['reference'], 'TEST-'));

        if ($isTestEvent) {
            Log::info('Belibayar Webhook: Test ping event received from Belibayar dashboard');
            return response()->json([
                'success' => true,
                'messages' => 'Belibayar webhook test ping received successfully',
            ], 200);
        }

        // 2. Extract payment data early
        $data = (isset($payload['data']) && is_array($payload['data'])) ? $payload['data'] : $payload;

        $orderId = $data['reference'] 
            ?? ($data['order_id'] 
            ?? ($data['external_id'] 
            ?? ($payload['reference'] 
            ?? ($payload['order_id'] ?? null))));

        $status = strtolower($data['status'] ?? ($payload['status'] ?? ''));
        $transactionId = $data['transaction_id'] ?? ($payload['transaction_id'] ?? null);

        // 3. Verify Signature (check against webhook_secret, secret_key, api_key with trimmed variations)
        $apiKey = $this->belibayarService->getApiKey();
        $candidateKeys = array_filter(array_unique([
            $webhookSecret,
            $secretKey,
            $apiKey,
        ]));

        $signatureMatches = false;
        if (!empty($signature) && !empty($candidateKeys)) {
            $candidateBodies = [
                $rawContent,
                trim($rawContent),
                rtrim($rawContent, "\r\n"),
            ];

            foreach ($candidateKeys as $key) {
                foreach ($candidateBodies as $body) {
                    $calc = hash_hmac('sha256', $body, $key);
                    if (hash_equals($calc, $signature)) {
                        $signatureMatches = true;
                        break 2;
                    }
                }
            }
        }

        if (!$signatureMatches && (!empty($webhookSecret) || !empty($secretKey))) {
            Log::warning('Belibayar Webhook: Signature mismatch', [
                'received_signature' => $signature,
                'is_production' => $isProduction,
                'order_id' => $orderId,
            ]);

            // FALLBACK SAFETY NET:
            // If signature verification fails (e.g. rotated secret or outdated .env on server),
            // verify legitimacy directly with Belibayar via Status Inquiry API.
            $verifiedViaInquiry = false;
            if ($orderId) {
                $candidatePayment = Payment::where('order_id', $orderId)->first();
                if ($candidatePayment) {
                    try {
                        Log::info("Belibayar Webhook: Verifying order {$orderId} directly via Belibayar Status Inquiry API");
                        $refreshedPayment = $this->belibayarService->checkStatus($candidatePayment);
                        if ($refreshedPayment && $refreshedPayment->isPaid()) {
                            $verifiedViaInquiry = true;
                            Log::info("Belibayar Webhook: Verified and fulfilled via direct status inquiry for order {$orderId}");
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Belibayar Webhook: Fallback status inquiry failed: " . $e->getMessage());
                    }
                }
            }

            if ($verifiedViaInquiry) {
                return response()->json([
                    'success' => true,
                    'messages' => 'Webhook verified via direct Belibayar status inquiry',
                ], 200);
            }

            if ($isProduction) {
                return response()->json([
                    'success' => false,
                    'messages' => 'Invalid signature',
                ], 401);
            } else {
                Log::info('Belibayar Webhook (Sandbox): Continuing despite signature mismatch for development/simulator testing.');
            }
        }

        // 4. Filter out withdrawal events if any
        if (isset($payload['event']) && is_string($payload['event']) && str_starts_with($payload['event'], 'withdrawal.')) {
            Log::info("Belibayar Webhook: Withdrawal notification ignored: {$payload['event']}");
            return response()->json(['success' => true, 'messages' => 'Withdrawal event acknowledged'], 200);
        }

        if (!$orderId) {
            Log::warning('Belibayar Webhook: Missing reference / order_id in payload');
            return response()->json(['success' => false, 'messages' => 'Reference is required'], 400);
        }

        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            Log::warning("Belibayar Webhook: Payment record not found for reference {$orderId}");
            return response()->json(['success' => false, 'messages' => 'Payment not found'], 404);
        }

        if ($transactionId && empty($payment->transaction_id)) {
            $payment->update(['transaction_id' => $transactionId]);
        }

        // 4. Process Status Updates
        $isPaid = in_array($status, ['paid', 'success', 'settlement', 'completed', 'berhasil']);
        $isExpired = in_array($status, ['expired', 'expire', 'kadaluwarsa']);
        $isFailed = in_array($status, ['cancelled', 'canceled', 'failed', 'rejected', 'gagal']);

        if ($isPaid) {
            $this->fulfillmentService->markAsPaid($payment, 'settlement', $payload);
            Log::info("Belibayar Webhook: Payment #{$payment->id} (Order: {$orderId}) marked as PAID");
        } elseif ($isExpired) {
            $this->fulfillmentService->markAsExpired($payment, $payload);
            Log::info("Belibayar Webhook: Payment #{$payment->id} (Order: {$orderId}) marked as EXPIRED");
        } elseif ($isFailed) {
            $this->fulfillmentService->markAsFailed($payment, $status, $payload);
            Log::info("Belibayar Webhook: Payment #{$payment->id} (Order: {$orderId}) marked as FAILED/CANCELLED");
        } else {
            // Still pending or other state, merge raw response
            $existingRaw = is_array($payment->raw_response) ? $payment->raw_response : [];
            $payment->update([
                'raw_response' => array_merge($existingRaw, $payload),
            ]);
        }

        return response()->json([
            'success' => true,
            'messages' => 'Webhook processed successfully',
        ], 200);
    }
}
