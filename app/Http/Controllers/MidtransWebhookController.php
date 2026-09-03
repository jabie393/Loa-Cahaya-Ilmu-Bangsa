<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Submission;
use App\Services\MidtransQrisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    protected MidtransQrisService $qrisService;

    public function __construct(MidtransQrisService $qrisService)
    {
        $this->qrisService = $qrisService;
    }

    /**
     * Handle incoming notification webhook from Midtrans.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received: ', [
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'fraud_status' => $payload['fraud_status'] ?? null,
            'status_code' => $payload['status_code'] ?? null,
        ]);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;

        if (!$orderId) {
            return response()->json(['message' => 'Order ID is required'], 400);
        }

        // Verify Signature Key for Security
        $serverKey = config('services.midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning("Midtrans Webhook: Invalid signature key for Order ID {$orderId}");
            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        // Find payment record
        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            Log::warning("Midtrans Webhook: Payment record not found for Order ID {$orderId}");
            return response()->json(['message' => 'Payment record not found'], 404);
        }

        // Process status based on Midtrans transaction_status
        $updateData = [
            'transaction_id' => $transactionId ?: $payment->transaction_id,
            'transaction_status' => $transactionStatus,
            'raw_response' => $payload,
        ];

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            $updateData['payment_status'] = 'paid';
            $updateData['paid_at'] = now();

            $payment->update($updateData);

            if ($payment->type === 'bulk_submission') {
                $submissions = !empty($payment->submission_ids)
                    ? Submission::whereIn('id', $payment->submission_ids)->get()
                    : ($payment->submission ? collect([$payment->submission]) : collect());

                foreach ($submissions as $sub) {
                    $sub->update(['payment_status' => 'paid']);
                    $sub->approveAndProcess();
                }
                Log::info("Midtrans Webhook: Bulk Submissions auto-approved for Payment #{$payment->id}");
            } elseif ($payment->type === 'doi_addon') {
                $submission = $payment->submission;
                if ($submission) {
                    $this->qrisService->activateDoiForSubmission($submission);
                    Log::info("Midtrans Webhook: Submission #{$submission->id} DOI activated successfully");
                }
            } else {
                $submission = $payment->submission;
                if ($submission) {
                    $submission->update(['payment_status' => 'paid']);
                    $submission->approveAndProcess();
                    Log::info("Midtrans Webhook: Submission #{$submission->id} auto-approved after payment");
                }
            }
        } elseif ($transactionStatus === 'expire') {
            $updateData['payment_status'] = 'expired';
            $payment->update($updateData);
            Log::info("Midtrans Webhook: Payment for Order ID {$orderId} has expired");
        } elseif (in_array($transactionStatus, ['deny', 'cancel'])) {
            $updateData['payment_status'] = 'failed';
            $payment->update($updateData);
            Log::info("Midtrans Webhook: Payment for Order ID {$orderId} failed with status {$transactionStatus}");
        } else {
            $payment->update($updateData);
        }

        return response()->json(['message' => 'Webhook handled successfully'], 200);
    }
}
