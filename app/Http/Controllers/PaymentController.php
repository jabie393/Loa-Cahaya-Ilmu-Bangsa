<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\MidtransQrisService;
use App\Services\SubmissionPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected MidtransQrisService $qrisService;
    protected SubmissionPricingService $pricingService;

    public function __construct(MidtransQrisService $qrisService, SubmissionPricingService $pricingService)
    {
        $this->qrisService = $qrisService;
        $this->pricingService = $pricingService;
    }

    /**
     * Show payment page for a specific submission.
     */
    public function show(int $id): View
    {
        $submission = Submission::with(['journal', 'user', 'payments'])->findOrFail($id);

        // Authorization: only the owner or super_admin / admin can view
        $currentUser = Auth::user();
        if ($submission->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman pembayaran naskah ini.');
        }

        // Check if metadata extraction is still running
        $isExtracting = ($submission->review_status === 'processing');

        $pricing = null;
        $payment = null;
        $errorMessage = null;

        if (!$isExtracting) {
            $pricing = $this->pricingService->calculate($submission);

            try {
                $payment = $this->qrisService->getOrCreatePayment($submission);
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        return view('payment.show', [
            'submission' => $submission,
            'isExtracting' => $isExtracting,
            'pricing' => $pricing,
            'payment' => $payment,
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * Check transaction status via AJAX.
     */
    public function checkStatus(int $id): JsonResponse
    {
        $submission = Submission::with(['payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if ($submission->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 1. If still extracting metadata
        if ($submission->review_status === 'processing') {
            return response()->json([
                'status' => 'extracting',
                'is_paid' => false,
                'message' => 'Sedang memproses ekstraksi artikel...',
            ]);
        }

        // 2. If already paid on submission level
        if ($submission->payment_status === 'paid') {
            return response()->json([
                'status' => 'paid',
                'is_paid' => true,
                'message' => 'Pembayaran telah berhasil diverifikasi.',
            ]);
        }

        $latestPayment = $submission->payments()->latest()->first();

        if (!$latestPayment) {
            return response()->json([
                'status' => 'no_payment',
                'is_paid' => false,
            ]);
        }

        // Check status directly with Midtrans to ensure instant sync
        $payment = $this->qrisService->checkStatusFromMidtrans($latestPayment);

        return response()->json([
            'status' => $payment->payment_status,
            'is_paid' => $payment->isPaid(),
            'is_expired' => $payment->isExpired(),
            'paid_at' => $payment->paid_at ? $payment->paid_at->format('d M Y H:i:s') : null,
            'message' => $payment->isPaid() ? 'Pembayaran berhasil!' : ($payment->isExpired() ? 'QRIS kedaluwarsa.' : 'Menunggu pembayaran.'),
        ]);
    }

    /**
     * Re-generate a fresh QRIS when expired.
     */
    public function regenerate(int $id): JsonResponse
    {
        $submission = Submission::with(['payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if ($submission->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($submission->payment_status === 'paid') {
            return response()->json([
                'status' => 'paid',
                'message' => 'Naskah ini sudah berstatus lunas.',
            ]);
        }

        try {
            $payment = $this->qrisService->forceNewPayment($submission);

            return response()->json([
                'success' => true,
                'order_id' => $payment->order_id,
                'qris_url' => $payment->qris_url,
                'qr_string' => $payment->qr_string,
                'expired_at' => $payment->expired_at ? $payment->expired_at->toIso8601String() : null,
                'message' => 'QRIS baru berhasil dibuat.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QRIS baru: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show DOI Add-on payment page.
     */
    public function showDoi(int $id): View
    {
        $submission = Submission::with(['journal', 'user', 'payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if ($submission->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $pricing = $this->pricingService->calculateDoiAddon();
        $payment = null;
        $errorMessage = null;

        try {
            $payment = $this->qrisService->getOrCreateDoiPayment($submission);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        return view('filament.resources.submissions.pages.payment-doi', [
            'record' => $submission,
            'pricing' => $pricing,
            'payment' => $payment,
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * Check status for DOI Add-on payment.
     */
    public function checkDoiStatus(int $id): JsonResponse
    {
        $submission = Submission::with(['payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if ($submission->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($submission->has_doi && !empty($submission->repository_identifier)) {
            return response()->json([
                'status' => 'paid',
                'is_paid' => true,
                'doi_identifier' => $submission->repository_identifier,
                'doi_url' => $submission->repository_redirect_url,
                'message' => 'DOI telah aktif!',
            ]);
        }

        $latestPayment = $submission->payments()->where('type', 'doi_addon')->latest()->first();

        if (!$latestPayment) {
            return response()->json(['status' => 'no_payment', 'is_paid' => false]);
        }

        $payment = $this->qrisService->checkStatusFromMidtrans($latestPayment);

        // If payment is paid, ensure DOI is activated on submission
        if ($payment->isPaid()) {
            if (!$submission->has_doi || empty($submission->repository_identifier)) {
                $this->qrisService->activateDoiForSubmission($submission);
                $submission->refresh();
            }

            return response()->json([
                'status' => 'paid',
                'is_paid' => true,
                'doi_identifier' => $submission->repository_identifier,
                'doi_url' => $submission->repository_redirect_url,
                'message' => 'DOI telah aktif!',
            ]);
        }

        return response()->json([
            'status' => $payment->payment_status,
            'is_paid' => $payment->isPaid(),
            'is_expired' => $payment->isExpired(),
            'message' => $payment->isPaid() ? 'Pembayaran DOI berhasil!' : ($payment->isExpired() ? 'QRIS kedaluwarsa.' : 'Menunggu pembayaran.'),
        ]);
    }

    /**
     * Re-generate QRIS for DOI Add-on.
     */
    public function regenerateDoi(int $id): JsonResponse
    {
        $submission = Submission::with(['payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if ($submission->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Mark existing pending DOI payment as expired
            $submission->payments()
                ->where('type', 'doi_addon')
                ->where('payment_status', 'pending')
                ->update(['payment_status' => 'expired']);

            $payment = $this->qrisService->chargeDoiAddonQris($submission);

            return response()->json([
                'success' => true,
                'order_id' => $payment->order_id,
                'qris_url' => $payment->qris_url,
                'qr_string' => $payment->qr_string,
                'expired_at' => $payment->expired_at ? $payment->expired_at->toIso8601String() : null,
                'message' => 'QRIS baru berhasil dibuat.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QRIS DOI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check status for Bulk Payment transaction.
     */
    public function checkBulkStatus(int $paymentId): JsonResponse
    {
        $payment = \App\Models\Payment::findOrFail($paymentId);

        if (!$payment->isPaid()) {
            $payment = $this->qrisService->checkStatusFromMidtrans($payment);
        }

        $isPaid = $payment->isPaid() || $payment->payment_status === 'paid' || $payment->transaction_status === 'settlement';

        return response()->json([
            'status' => $isPaid ? 'paid' : $payment->payment_status,
            'is_paid' => $isPaid,
            'is_expired' => $payment->isExpired(),
            'message' => $isPaid ? 'Pembayaran kolektif berhasil diverifikasi!' : ($payment->isExpired() ? 'QRIS Kedaluwarsa' : 'Menunggu pembayaran...'),
        ]);
    }


    /**
     * Regenerate expired Bulk QRIS payment for multiple submissions.
     */
    public function regenerateBulk(int $paymentId): JsonResponse
    {
        $oldPayment = \App\Models\Payment::findOrFail($paymentId);

        if ($oldPayment->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran ini sudah lunas, tidak dapat di-generate ulang.',
            ], 400);
        }

        $submissionIds = $oldPayment->submission_ids;
        if (empty($submissionIds)) {
            $submissionIds = $oldPayment->submission_id ? [$oldPayment->submission_id] : [];
        }

        $submissions = \App\Models\Submission::whereIn('id', $submissionIds)->get();

        if ($submissions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data naskah tidak ditemukan.',
            ], 404);
        }

        // Mark old payment as expired
        $oldPayment->update([
            'payment_status' => 'expired',
            'transaction_status' => 'expire',
        ]);

        try {
            $newPayment = $this->qrisService->chargeBulkQris($submissions);

            return response()->json([
                'success' => true,
                'payment_id' => $newPayment->id,
                'order_id' => $newPayment->order_id,
                'qris_url' => $newPayment->qris_url,
                'qr_string' => $newPayment->qr_string,
                'expired_at' => $newPayment->expired_at?->toIso8601String(),
                'gross_amount' => $newPayment->gross_amount,
                'check_url' => route('payments.check.bulk', ['paymentId' => $newPayment->id]),
                'regenerate_url' => route('payments.regenerate.bulk', ['paymentId' => $newPayment->id]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QRIS Baru: ' . $e->getMessage(),
            ], 500);
        }
    }

}
