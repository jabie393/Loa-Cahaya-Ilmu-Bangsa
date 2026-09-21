<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\PaymentGateways\PaymentGatewayManager;
use App\Services\SubmissionPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected PaymentGatewayManager $qrisService;
    protected SubmissionPricingService $pricingService;

    public function __construct(PaymentGatewayManager $qrisService, SubmissionPricingService $pricingService)
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
            $pricing = $this->pricingService->calculate($submission, $currentUser);

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

        $latestPayment = $submission->payments()->where('type', 'submission')->latest()->first();

        if (!$latestPayment) {
            return response()->json([
                'status' => 'no_payment',
                'is_paid' => false,
            ]);
        }

        // Check if gateway has changed while this payment is still pending
        $activeGateway = $this->qrisService->getActiveGatewayName();
        if ($latestPayment->payment_status === 'pending' && ($latestPayment->gateway ?: 'midtrans') !== $activeGateway) {
            $this->qrisService->cancelAndExpirePayment($latestPayment);

            $newPayment = $this->qrisService->getOrCreatePayment($submission);

            return response()->json([
                'status' => 'pending',
                'is_paid' => false,
                'is_expired' => false,
                'gateway' => $newPayment->gateway,
                'gateway_changed' => true,
                'order_id' => $newPayment->order_id,
                'qris_url' => $newPayment->qris_url,
                'qr_string' => $newPayment->qr_string,
                'expired_at' => $newPayment->expired_at ? $newPayment->expired_at->toIso8601String() : null,
                'paid_at' => null,
                'message' => 'Platform gateway diubah. QRIS baru berhasil digenerate otomatis.',
            ]);
        }

        // Check status directly with gateway to ensure instant sync
        $payment = $this->qrisService->checkStatusFromMidtrans($latestPayment);

        return response()->json([
            'status' => $payment->payment_status,
            'is_paid' => $payment->isPaid(),
            'is_expired' => $payment->isExpired(),
            'gateway' => $payment->gateway ?: 'midtrans',
            'order_id' => $payment->order_id,
            'qris_url' => $payment->qris_url,
            'qr_string' => $payment->qr_string,
            'expired_at' => $payment->expired_at ? $payment->expired_at->toIso8601String() : null,
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
     * Simulate successful payment in Sandbox environment.
     */
    public function simulateSandbox(Request $request, int $id): JsonResponse
    {
        $submission = Submission::with(['payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if ($submission->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prohibit in production
        if (config('services.belibayar.is_production', false) && config('services.midtrans.is_production', false)) {
            return response()->json(['message' => 'Simulasi hanya diperbolehkan pada mode Sandbox.'], 403);
        }

        $query = $submission->payments()->where('payment_status', 'pending');
        if ($request->filled('order_id')) {
            $query->where('order_id', $request->input('order_id'));
        } elseif ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $latestPayment = $query->latest()->first();
        if (!$latestPayment) {
            $latestPayment = $submission->payments()->where('payment_status', 'pending')->latest()->first();
        }

        if (!$latestPayment) {
            return response()->json(['message' => 'Tidak ada transaksi pending yang dapat disimulasikan.'], 400);
        }

        $this->qrisService->fulfillment()->markAsPaid($latestPayment, 'settlement', [
            'simulated' => true,
            'source' => 'sandbox_button',
            'simulated_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'status' => 'paid',
            'message' => 'Pembayaran berhasil disimulasikan sebagai LUNAS!',
        ]);
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

        $pricing = $this->pricingService->calculateDoiAddon($currentUser);
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

        // Check if gateway has changed while this DOI payment is still pending
        $activeGateway = $this->qrisService->getActiveGatewayName();
        if ($latestPayment->payment_status === 'pending' && ($latestPayment->gateway ?: 'midtrans') !== $activeGateway) {
            $this->qrisService->cancelAndExpirePayment($latestPayment);

            $newPayment = $this->qrisService->getOrCreateDoiPayment($submission);

            return response()->json([
                'status' => 'pending',
                'is_paid' => false,
                'is_expired' => false,
                'gateway' => $newPayment->gateway,
                'gateway_changed' => true,
                'order_id' => $newPayment->order_id,
                'qris_url' => $newPayment->qris_url,
                'qr_string' => $newPayment->qr_string,
                'expired_at' => $newPayment->expired_at ? $newPayment->expired_at->toIso8601String() : null,
                'message' => 'Platform gateway diubah. QRIS DOI baru berhasil digenerate otomatis.',
            ]);
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
            'gateway' => $payment->gateway ?: 'midtrans',
            'order_id' => $payment->order_id,
            'qris_url' => $payment->qris_url,
            'qr_string' => $payment->qr_string,
            'expired_at' => $payment->expired_at ? $payment->expired_at->toIso8601String() : null,
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
     * Show Replace PDF payment page.
     */
    public function showReplacePdf(int $id)
    {
        $submission = Submission::findOrFail($id);
        return redirect()->to(\App\Filament\Resources\Submissions\SubmissionResource::getUrl('payment.replace_pdf', ['record' => $submission]));
    }

    /**
     * Check status for Replace PDF payment.
     */
    public function checkReplacePdfStatus(int $id): JsonResponse
    {
        $submission = Submission::with(['payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if ($submission->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $paidPayment = $submission->payments()
            ->where('type', 'replace_pdf')
            ->where('payment_status', 'paid')
            ->latest()
            ->first();

        $latestPayment = $submission->payments()->where('type', 'replace_pdf')->latest()->first();

        if (!$latestPayment) {
            return response()->json(['status' => 'no_payment', 'is_paid' => false]);
        }

        // If an older paid payment exists and latest payment is ghost/unpaid without a valid temp file:
        if ($paidPayment && $latestPayment->id !== $paidPayment->id && !$latestPayment->isPaid()) {
            $raw = is_array($latestPayment->raw_response) ? $latestPayment->raw_response : [];
            $tempPath = $raw['new_pdf_path'] ?? null;
            if (!$tempPath || !Storage::disk('public')->exists($tempPath) || $latestPayment->isExpired()) {
                $latestPayment->update([
                    'payment_status' => 'expired',
                    'transaction_status' => 'expire',
                ]);
                $latestPayment = $paidPayment;
            }
        }

        // Check if gateway has changed while Replace PDF payment is pending
        $activeGateway = $this->qrisService->getActiveGatewayName();
        if ($latestPayment->payment_status === 'pending' && ($latestPayment->gateway ?: 'midtrans') !== $activeGateway) {
            $this->qrisService->cancelAndExpirePayment($latestPayment);

            $newPayment = $this->qrisService->getOrCreateReplacePdfPayment($submission);

            return response()->json([
                'status' => 'pending',
                'is_paid' => false,
                'is_expired' => false,
                'gateway' => $newPayment->gateway,
                'gateway_changed' => true,
                'order_id' => $newPayment->order_id,
                'qris_url' => $newPayment->qris_url,
                'qr_string' => $newPayment->qr_string,
                'expired_at' => $newPayment->expired_at ? $newPayment->expired_at->toIso8601String() : null,
                'message' => 'Platform gateway diubah. QRIS ganti PDF baru berhasil digenerate otomatis.',
            ]);
        }

        $payment = $this->qrisService->checkStatusFromMidtrans($latestPayment);

        if ($payment->isPaid()) {
            $this->qrisService->applyReplacePdfForSubmission($submission, $payment);

            return response()->json([
                'status' => 'paid',
                'is_paid' => true,
                'message' => 'File PDF naskah berhasil diperbarui dan disinkronkan ke OJS!',
            ]);
        }

        return response()->json([
            'status' => $payment->payment_status,
            'is_paid' => $payment->isPaid(),
            'is_expired' => $payment->isExpired(),
            'gateway' => $payment->gateway ?: 'midtrans',
            'order_id' => $payment->order_id,
            'qris_url' => $payment->qris_url,
            'qr_string' => $payment->qr_string,
            'expired_at' => $payment->expired_at ? $payment->expired_at->toIso8601String() : null,
            'message' => $payment->isPaid() ? 'Pembayaran berhasil!' : ($payment->isExpired() ? 'QRIS kedaluwarsa.' : 'Menunggu pembayaran.'),
        ]);
    }

    /**
     * Re-generate QRIS for Replace PDF Service.
     */
    public function regenerateReplacePdf(int $id): JsonResponse
    {
        $submission = Submission::with(['payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if ($submission->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $latest = $submission->payments()->where('type', 'replace_pdf')->latest()->first();
            $tempPath = $latest?->raw_response['new_pdf_path'] ?? '';

            if (empty($tempPath)) {
                $allPrev = $submission->payments()->where('type', 'replace_pdf')->latest()->get();
                foreach ($allPrev as $prev) {
                    if (!empty($prev->raw_response['new_pdf_path'])) {
                        $tempPath = $prev->raw_response['new_pdf_path'];
                        break;
                    }
                }
            }

            // Mark existing pending payment as expired
            $submission->payments()
                ->where('type', 'replace_pdf')
                ->where('payment_status', 'pending')
                ->update(['payment_status' => 'expired']);

            $payment = $this->qrisService->chargeReplacePdfQris($submission, $tempPath);

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
                'message' => 'Gagal membuat QRIS: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check status for Bulk Payment transaction.
     */
    public function checkBulkStatus(int $paymentId): JsonResponse
    {
        $payment = \App\Models\Payment::findOrFail($paymentId);

        // Check if gateway has changed while this bulk payment is pending
        $activeGateway = $this->qrisService->getActiveGatewayName();
        if ($payment->payment_status === 'pending' && ($payment->gateway ?: 'midtrans') !== $activeGateway) {
            $submissionIds = $payment->submission_ids ?: ($payment->submission_id ? [$payment->submission_id] : []);
            $submissions = \App\Models\Submission::whereIn('id', $submissionIds)->get();

            $this->qrisService->cancelAndExpirePayment($payment);

            $newPayment = $this->qrisService->getOrCreateBulkPayment($submissions);

            return response()->json([
                'status' => 'pending',
                'payment_id' => $newPayment->id,
                'is_paid' => false,
                'is_expired' => false,
                'gateway' => $newPayment->gateway,
                'gateway_changed' => true,
                'order_id' => $newPayment->order_id,
                'qris_url' => $newPayment->qris_url,
                'qr_string' => $newPayment->qr_string,
                'expired_at' => $newPayment->expired_at ? $newPayment->expired_at->toIso8601String() : null,
                'message' => 'Platform gateway diubah. QRIS kolektif baru berhasil digenerate otomatis.',
            ]);
        }

        if (!$payment->isPaid()) {
            $payment = $this->qrisService->checkStatusFromMidtrans($payment);
        }

        $isPaid = $payment->isPaid() || $payment->payment_status === 'paid' || $payment->transaction_status === 'settlement';

        return response()->json([
            'status' => $isPaid ? 'paid' : $payment->payment_status,
            'payment_id' => $payment->id,
            'is_paid' => $isPaid,
            'is_expired' => $payment->isExpired(),
            'gateway' => $payment->gateway ?: 'midtrans',
            'order_id' => $payment->order_id,
            'qris_url' => $payment->qris_url,
            'qr_string' => $payment->qr_string,
            'expired_at' => $payment->expired_at ? $payment->expired_at->toIso8601String() : null,
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

    public function simulateBulk(int $paymentId): JsonResponse
    {
        $payment = \App\Models\Payment::findOrFail($paymentId);

        $currentUser = Auth::user();
        if ($payment->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prohibit in production
        if (config('services.belibayar.is_production', false) && config('services.midtrans.is_production', false)) {
            return response()->json(['message' => 'Simulasi hanya diperbolehkan pada mode Sandbox.'], 403);
        }

        if ($payment->isPaid()) {
            return response()->json(['message' => 'Pembayaran sudah lunas.'], 400);
        }

        $this->qrisService->fulfillment()->markAsPaid($payment, 'settlement', [
            'simulated' => true,
            'source' => 'sandbox_bulk_button',
            'simulated_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'status' => 'paid',
            'message' => 'Pembayaran kolektif berhasil disimulasikan sebagai LUNAS!',
        ]);
    }

    /**
     * Download QRIS image directly to the user's device (proxied via backend to avoid CORS & blank tab).
     */
    public function downloadQris(Request $request)
    {
        $orderId = $request->query('order_id');
        $url = $request->query('url');

        $payment = null;
        if ($orderId) {
            $payment = \App\Models\Payment::where('order_id', $orderId)->first();
        }

        if ($payment && !empty($payment->qris_url)) {
            $url = $payment->qris_url;
        } elseif ($payment && !empty($payment->qr_string)) {
            $url = 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&margin=15&data=' . urlencode($payment->qr_string);
        }

        if (!$url) {
            abort(404, 'Gambar QRIS tidak ditemukan.');
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->withoutVerifying()
                ->get($url);

            if (!$response->successful() && $orderId) {
                // Fallback QR code generator if original image URL fails
                $fallbackUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&margin=15&data=' . urlencode($orderId);
                $response = \Illuminate\Support\Facades\Http::timeout(15)->withoutVerifying()->get($fallbackUrl);
            }

            $filename = 'QRIS-' . ($orderId ?: 'CIB') . '.png';
            $contentType = $response->header('Content-Type') ?: 'image/png';

            return response($response->body(), 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, private',
            ]);
        } catch (\Exception $e) {
            abort(500, 'Gagal mengunduh QRIS: ' . $e->getMessage());
        }
    }

    /**
     * Switch payment gateway on the fly from QRIS page and immediately regenerate fresh QRIS.
     */
    public function switchGateway(Request $request, int $id): JsonResponse
    {
        $submission = Submission::with(['payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasAnyRole(['ryu_dev', 'super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $gateway = $request->input('gateway');
        if (!in_array($gateway, ['midtrans', 'belibayar'], true)) {
            return response()->json(['message' => 'Gateway tidak didukung.'], 400);
        }

        $this->qrisService->setActiveGateway($gateway);

        // Cancel and expire current pending submission payment
        $latestPayment = $submission->payments()
            ->where('type', 'submission')
            ->where('payment_status', 'pending')
            ->latest()
            ->first();

        if ($latestPayment) {
            $this->qrisService->cancelAndExpirePayment($latestPayment);
        }

        try {
            $newPayment = $this->qrisService->getOrCreatePayment($submission);

            return response()->json([
                'success' => true,
                'gateway' => $newPayment->gateway,
                'order_id' => $newPayment->order_id,
                'qris_url' => $newPayment->qris_url,
                'qr_string' => $newPayment->qr_string,
                'expired_at' => $newPayment->expired_at ? $newPayment->expired_at->toIso8601String() : null,
                'message' => 'Gateway berhasil dialihkan ke ' . ($gateway === 'belibayar' ? 'Belibayar.id' : 'Midtrans') . '. QRIS sebelumnya telah dibuat expired.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate QRIS gateway baru: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Switch payment gateway on the fly for DOI Add-on page.
     */
    public function switchDoiGateway(Request $request, int $id): JsonResponse
    {
        $submission = Submission::with(['payments'])->findOrFail($id);

        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasAnyRole(['ryu_dev', 'super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $gateway = $request->input('gateway');
        if (!in_array($gateway, ['midtrans', 'belibayar'], true)) {
            return response()->json(['message' => 'Gateway tidak didukung.'], 400);
        }

        $this->qrisService->setActiveGateway($gateway);

        $latestPayment = $submission->payments()
            ->where('type', 'doi_addon')
            ->where('payment_status', 'pending')
            ->latest()
            ->first();

        if ($latestPayment) {
            $this->qrisService->cancelAndExpirePayment($latestPayment);
        }

        try {
            $newPayment = $this->qrisService->getOrCreateDoiPayment($submission);

            return response()->json([
                'success' => true,
                'gateway' => $newPayment->gateway,
                'order_id' => $newPayment->order_id,
                'qris_url' => $newPayment->qris_url,
                'qr_string' => $newPayment->qr_string,
                'expired_at' => $newPayment->expired_at ? $newPayment->expired_at->toIso8601String() : null,
                'message' => 'Gateway berhasil dialihkan ke ' . ($gateway === 'belibayar' ? 'Belibayar.id' : 'Midtrans') . '. QRIS sebelumnya telah dibuat expired.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate QRIS DOI: ' . $e->getMessage(),
            ], 500);
        }
    }
}
