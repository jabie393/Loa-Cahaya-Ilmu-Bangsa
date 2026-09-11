<?php

namespace App\Services\PaymentGateways;

use App\Models\Payment;
use App\Models\Submission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentFulfillmentService
{
    /**
     * Mark a payment as paid and process fulfillment (auto-approval, DOI, Replace PDF).
     */
    public function markAsPaid(Payment $payment, string $transStatus = 'settlement', array $rawResponse = []): void
    {
        $existingRaw = is_array($payment->raw_response) ? $payment->raw_response : [];
        $mergedRaw = array_merge($existingRaw, $rawResponse);
        if (!empty($existingRaw['new_pdf_path']) && empty($mergedRaw['new_pdf_path'])) {
            $mergedRaw['new_pdf_path'] = $existingRaw['new_pdf_path'];
        }

        $payment->update([
            'payment_status' => 'paid',
            'transaction_status' => $transStatus,
            'paid_at' => $payment->paid_at ?: now(),
            'raw_response' => $mergedRaw,
        ]);

        $payment->ensureInvoiceNumber();

        if ($payment->type === 'bulk_submission') {
            $submissions = !empty($payment->submission_ids)
                ? Submission::whereIn('id', $payment->submission_ids)->get()
                : ($payment->submission ? collect([$payment->submission]) : collect());

            foreach ($submissions as $sub) {
                $sub->update(['payment_status' => 'paid']);
                $sub->approveAndProcess();
            }
            Log::info("PaymentFulfillment: Bulk Submissions auto-approved for Payment #{$payment->id}");
        } elseif ($payment->type === 'doi_addon') {
            $submission = $payment->submission;
            if ($submission) {
                $this->activateDoiForSubmission($submission);
                Log::info("PaymentFulfillment: Submission #{$submission->id} DOI activated successfully");
            }
        } elseif ($payment->type === 'replace_pdf') {
            $submission = $payment->submission;
            if ($submission) {
                $this->applyReplacePdfForSubmission($submission, $payment);
                Log::info("PaymentFulfillment: Submission #{$submission->id} PDF replaced and synced to OJS successfully");
            }
        } else {
            $submission = $payment->submission;
            if ($submission) {
                $submission->update(['payment_status' => 'paid']);
                $submission->approveAndProcess();
                Log::info("PaymentFulfillment: Submission #{$submission->id} auto-approved after payment");
            }
        }
    }

    /**
     * Mark a payment as expired.
     */
    public function markAsExpired(Payment $payment, array $rawResponse = []): void
    {
        $existingRaw = is_array($payment->raw_response) ? $payment->raw_response : [];
        $mergedRaw = array_merge($existingRaw, $rawResponse);

        $payment->update([
            'payment_status' => 'expired',
            'transaction_status' => 'expire',
            'raw_response' => $mergedRaw,
        ]);

        Log::info("PaymentFulfillment: Payment for Order ID {$payment->order_id} marked as expired");
    }

    /**
     * Mark a payment as failed / cancelled / denied.
     */
    public function markAsFailed(Payment $payment, string $transStatus = 'deny', array $rawResponse = []): void
    {
        $existingRaw = is_array($payment->raw_response) ? $payment->raw_response : [];
        $mergedRaw = array_merge($existingRaw, $rawResponse);

        $payment->update([
            'payment_status' => 'failed',
            'transaction_status' => $transStatus,
            'raw_response' => $mergedRaw,
        ]);

        Log::info("PaymentFulfillment: Payment for Order ID {$payment->order_id} marked as failed ({$transStatus})");
    }

    /**
     * Activate DOI for submission upon successful payment.
     */
    public function activateDoiForSubmission(Submission $submission): void
    {
        $submission->update([
            'want_doi' => true,
            'has_doi' => true,
        ]);

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
                Log::error("PaymentFulfillment: DOI generation failed for submission #{$submission->id}: " . $e->getMessage());
            }
        }

        try {
            \App\Services\OjsSubmissionService::submitInBackground($submission);
        } catch (\Throwable $e) {
            Log::warning("PaymentFulfillment: OJS sync failed for submission #{$submission->id}: " . $e->getMessage());
        }
    }

    /**
     * Apply new PDF manuscript to submission upon successful replace_pdf payment.
     */
    public function applyReplacePdfForSubmission(Submission $submission, Payment $payment): void
    {
        $raw = is_array($payment->raw_response) ? $payment->raw_response : [];
        $tempPath = $raw['new_pdf_path'] ?? null;

        // Fallback 1: Search previous replace_pdf payments for this submission
        if (empty($tempPath) || !Storage::disk('public')->exists($tempPath)) {
            $prevPayments = $submission->payments()->where('type', 'replace_pdf')->latest()->get();
            foreach ($prevPayments as $prev) {
                $pRaw = is_array($prev->raw_response) ? $prev->raw_response : [];
                $pPath = $pRaw['new_pdf_path'] ?? null;
                if ($pPath && Storage::disk('public')->exists($pPath)) {
                    $tempPath = $pPath;
                    break;
                }
            }
        }

        // Fallback 2: Check files in temp_replace_pdf specifically scoped to this submission
        if (empty($tempPath) || !Storage::disk('public')->exists($tempPath)) {
            $tempFiles = Storage::disk('public')->files('temp_replace_pdf');
            $matchingFiles = array_filter($tempFiles, fn($f) => str_contains(basename($f), 'replace_sub_' . $submission->id . '_'));
            if (!empty($matchingFiles)) {
                usort($matchingFiles, function ($a, $b) {
                    return Storage::disk('public')->lastModified($b) <=> Storage::disk('public')->lastModified($a);
                });
                $tempPath = reset($matchingFiles);
            }
        }

        if ($tempPath && Storage::disk('public')->exists($tempPath)) {
            $extension = pathinfo($tempPath, PATHINFO_EXTENSION) ?: 'pdf';
            $targetPath = "manuscripts/file-{$submission->id}.{$extension}";

            Storage::disk('public')->put($targetPath, Storage::disk('public')->get($tempPath));
            Storage::disk('public')->delete($tempPath);

            $submission->manuscript_file = $targetPath;
            $submission->save();
            $submission->touch();

            Log::info("PaymentFulfillment: Replace PDF applied successfully for Submission #{$submission->id}. Target: {$targetPath}");

            try {
                \App\Services\OjsSubmissionService::submitInBackground($submission);
            } catch (\Throwable $e) {
                Log::error("PaymentFulfillment: Failed to dispatch OJS sync after PDF replacement for Submission #{$submission->id}: " . $e->getMessage());
            }
        } else {
            Log::warning("PaymentFulfillment: Replace PDF: No valid temp file found for Submission #{$submission->id}, Payment #{$payment->id}");
        }
    }
}
