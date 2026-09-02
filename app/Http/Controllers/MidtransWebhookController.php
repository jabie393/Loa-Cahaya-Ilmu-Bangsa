<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    /**
     * Handle incoming notification from Midtrans.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received:', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            Log::warning('Midtrans Webhook: Incomplete payload data');
            return response()->json(['message' => 'Incomplete data'], 400);
        }

        // 1. Verify Signature Key
        // SHA512(order_id + status_code + gross_amount + ServerKey)
        $serverKey = (string) config('services.midtrans.server_key', '');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::error("Midtrans Webhook: Invalid signature for Order ID {$orderId}");
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 2. Find Payment Record
        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            Log::error("Midtrans Webhook: Payment record not found for Order ID {$orderId}");
            return response()->json(['message' => 'Payment record not found'], 404);
        }

        // 3. Verify gross amount
        $incomingAmount = (float) $grossAmount;
        $expectedAmount = (float) $payment->gross_amount;
        if (abs($incomingAmount - $expectedAmount) > 0.01) {
            Log::error("Midtrans Webhook: Gross amount mismatch for {$orderId}. Expected: {$expectedAmount}, Got: {$incomingAmount}");
            return response()->json(['message' => 'Gross amount mismatch'], 400);
        }

        // 4. Idempotency Check
        // If payment is already marked as paid, return 200 without reprocessing
        if ($payment->isPaid()) {
            Log::info("Midtrans Webhook: Duplicate notification for already paid Order ID {$orderId}. Ignored.");
            return response()->json(['message' => 'Already paid, duplicate ignored'], 200);
        }

        // 5. Update Status within Database Transaction
        DB::transaction(function () use ($payment, $transactionStatus, $transactionId, $payload) {
            $updateData = [
                'transaction_id' => $transactionId ?: $payment->transaction_id,
                'transaction_status' => $transactionStatus,
                'raw_response' => $payload,
            ];

            if (in_array($transactionStatus, ['capture', 'settlement'])) {
                $updateData['payment_status'] = 'paid';
                $updateData['paid_at'] = now();

                $payment->update($updateData);

                $submission = $payment->submission;
                if ($submission) {
                    if ($payment->type === 'doi_addon') {
                        // Activate DOI immediately
                        $submission->update([
                            'want_doi' => true,
                            'has_doi' => true,
                        ]);

                        // Generate DOI identifier if not yet set
                        if (empty($submission->repository_identifier)) {
                            try {
                                $identifierService = new \App\Services\RepositoryIdentifierService();
                                $identifier = $identifierService->generate($submission);
                                $repoUrl = rtrim(config('services.repo_url', 'http://127.0.0.1:8001'), '/');
                                $redirectUrl = $repoUrl . '/' . $identifier;
                                $landingPage = "/article/submission-{$submission->id}";

                                $submission->update([
                                    'repository_identifier' => $identifier,
                                    'repository_landing_page' => $landingPage,
                                    'repository_redirect_url' => $redirectUrl,
                                    'repository_identifier_status' => 'active',
                                    'repository_identifier_generated_at' => now(),
                                ]);
                            } catch (\Throwable $e) {
                                Log::error("Midtrans Webhook: DOI generation failed for Submission #{$submission->id}: " . $e->getMessage());
                            }
                        }

                        // Resync to OJS if already approved/submitted
                        try {
                            \App\Services\OjsSubmissionService::submitInBackground($submission);
                        } catch (\Throwable $e) {
                            Log::warning("Midtrans Webhook: OJS resync failed for Submission #{$submission->id}: " . $e->getMessage());
                        }

                        Log::info("Midtrans Webhook: DOI Add-on Order ID {$payment->order_id} marked as PAID & DOI Activated.");
                    } else {
                        // Standard submission payment: mark paid and auto-approve LOA
                        $submission->update(['payment_status' => 'paid']);
                        $submission->approveAndProcess();
                        Log::info("Midtrans Webhook: Order ID {$payment->order_id} successfully marked as PAID.");
                    }
                }
            } elseif ($transactionStatus === 'expire') {
                $updateData['payment_status'] = 'expired';
                $payment->update($updateData);

                $submission = $payment->submission;
                if ($submission && $submission->payment_status !== 'paid') {
                    $submission->update(['payment_status' => 'pending']);
                }

                Log::info("Midtrans Webhook: Order ID {$payment->order_id} marked as EXPIRED.");
            } elseif (in_array($transactionStatus, ['deny', 'cancel'])) {
                $updateData['payment_status'] = 'failed';
                $payment->update($updateData);

                Log::info("Midtrans Webhook: Order ID {$payment->order_id} marked as FAILED.");
            } else {
                $payment->update($updateData);
            }
        });

        return response()->json(['message' => 'Webhook handled successfully'], 200);
    }
}
