<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Services\PaymentGateways\PaymentFulfillmentService;
use App\Services\PaymentGateways\PaymentGatewayInterface;

class MidtransQrisService implements PaymentGatewayInterface
{
    protected SubmissionPricingService $pricingService;
    protected PaymentFulfillmentService $fulfillmentService;

    public function __construct(
        SubmissionPricingService $pricingService,
        ?PaymentFulfillmentService $fulfillmentService = null
    ) {
        $this->pricingService = $pricingService;
        $this->fulfillmentService = $fulfillmentService ?? app(PaymentFulfillmentService::class);
    }

    public function getGatewayName(): string
    {
        return 'midtrans';
    }

    public function getServerKey(): string
    {
        $key = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY', '');
        return trim((string) $key);
    }

    public function getClientKey(): string
    {
        $key = config('services.midtrans.client_key') ?: env('MIDTRANS_CLIENT_KEY', '');
        return trim((string) $key);
    }

    public function isProduction(): bool
    {
        $isProd = config('services.midtrans.is_production');
        if ($isProd === null) {
            $isProd = env('MIDTRANS_IS_PRODUCTION', false);
        }
        return (bool) $isProd;
    }

    public function getBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    public function cancelAndExpirePendingPayment(Payment $payment): void
    {
        try {
            $this->cancelPayment($payment);
        } catch (\Throwable $e) {
            Log::warning("Midtrans cancelPayment error during expiration: " . $e->getMessage());
        }

        $payment->update([
            'payment_status' => 'expired',
            'transaction_status' => 'expire',
        ]);
    }

    /**
     * Get an existing active pending payment or create a new one.
     */
    public function getOrCreatePayment(Submission $submission): Payment
    {
        // 1. Check if submission already has a paid payment
        $paidPayment = $submission->payments()
            ->where('payment_status', 'paid')
            ->where('type', 'submission')
            ->first();
        if ($paidPayment) {
            $paidPayment->ensureInvoiceNumber();
            return $paidPayment;
        }

        // Calculate current pricing
        $pricing = $this->pricingService->calculate($submission);
        $currentGross = (int) round($pricing['gross_amount']);

        // 2. Check for latest pending payment specifically for single submission
        $latestPayment = $submission->payments()
            ->where('type', 'submission')
            ->latest()
            ->first();

        if ($latestPayment && $latestPayment->payment_status === 'pending') {
            $paymentGross = (int) round($latestPayment->gross_amount);

            // Check if expired OR if the amount no longer matches current pricing (e.g. DOI changed or authors changed)
            if (($latestPayment->expired_at && now()->greaterThanOrEqualTo($latestPayment->expired_at)) || $paymentGross !== $currentGross) {
                $this->cancelAndExpirePendingPayment($latestPayment);
            } else {
                // Still active and valid single QRIS with matching amount
                if (empty($latestPayment->qris_url) && !empty($latestPayment->qr_string)) {
                    $latestPayment->qris_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($latestPayment->qr_string);
                    $latestPayment->save();
                }
                return $latestPayment;
            }
        }

        // 3. Generate a new QRIS payment specifically for this single submission
        return $this->chargeQris($submission);
    }

    /**
     * Force generate a new QRIS transaction (e.g., when user clicks 'Buat QRIS Baru').
     */
    public function forceNewPayment(Submission $submission): Payment
    {
        // If already paid, do not generate new
        $paidPayment = $submission->payments()->where('payment_status', 'paid')->first();
        if ($paidPayment) {
            $paidPayment->ensureInvoiceNumber();
            return $paidPayment;
        }

        // Cancel and mark any lingering pending payments as expired
        $submission->payments()
            ->where('payment_status', 'pending')
            ->get()
            ->each(fn ($p) => $this->cancelAndExpirePendingPayment($p));

        return $this->chargeQris($submission);
    }

    /**
     * Call Midtrans Core API /v2/charge with payment_type = 'qris'.
     */
    public function chargeQris(Submission $submission): Payment
    {
        $pricing = $this->pricingService->calculate($submission);
        $grossAmount = (int) round($pricing['gross_amount']);

        // Generate unique order_id: SUB-{id}-{timestamp}-{random}
        $orderId = 'SUB-' . $submission->id . '-' . time() . '-' . Str::upper(Str::random(4));

        $serverKey = $this->getServerKey();
        $authHeader = 'Basic ' . base64_encode($serverKey . ':');

        $userId = $submission->user_id ?? Auth::id();
        $customerName = !empty($submission->author_name) ? $submission->author_name : ($submission->user?->name ?? 'Author');
        $customerEmail = !empty($submission->email) ? $submission->email : ($submission->user?->email ?? 'author@cib.institute');

        $itemName = $pricing['tier_name'] . ' - ' . ($submission->journal?->name ?? 'Jurnal CIB');

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
            'customer_details' => [
                'first_name' => Str::limit($customerName, 45, ''),
                'email' => $customerEmail,
            ],
            'item_details' => [
                [
                    'id' => 'SUBMISSION-' . $submission->id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => Str::limit($itemName, 50, ''),
                ]
            ],
        ];

        Log::info("Midtrans QRIS charge initiating for Submission #{$submission->id}", [
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $authHeader,
        ])->timeout(20)->post($this->getBaseUrl() . '/v2/charge', $payload);

        $responseData = $response->json();

        Log::info("Midtrans QRIS charge response for {$orderId}:", [
            'status' => $response->status(),
            'body' => $responseData,
        ]);

        if (!$response->successful() || empty($responseData)) {
            $errorMsg = $responseData['status_message'] ?? 'Gagal menghubungi server Midtrans.';
            throw new \Exception("Gagal membuat QRIS: " . $errorMsg);
        }

        // Extract QR URL from actions
        $qrisUrl = null;
        if (!empty($responseData['actions']) && is_array($responseData['actions'])) {
            foreach ($responseData['actions'] as $action) {
                if (($action['name'] ?? '') === 'generate-qr-code') {
                    $qrisUrl = $action['url'] ?? null;
                    break;
                }
            }
        }

        $qrString = $responseData['qr_string'] ?? null;
        if (empty($qrisUrl) && !empty($qrString)) {
            $qrisUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($qrString);
        }

        // Parse expiry time
        $expiredAt = null;
        if (!empty($responseData['expiry_time'])) {
            try {
                $expiredAt = Carbon::parse($responseData['expiry_time']);
            } catch (\Exception $e) {
                $expiredAt = now()->addMinutes(15);
            }
        } else {
            $expiredAt = now()->addMinutes(15);
        }

        $payment = Payment::create([
            'user_id' => $userId,
            'submission_id' => $submission->id,
            'order_id' => $orderId,
            'transaction_id' => $responseData['transaction_id'] ?? null,
            'gateway' => 'midtrans',
            'payment_method' => 'qris',
            'type' => 'submission',
            'payer_name' => $customerName,
            'payer_email' => $customerEmail,
            'original_amount' => $pricing['original_amount'] ?? $grossAmount,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'gross_amount' => $grossAmount,
            'journal_share' => $pricing['journal_share'],
            'developer_gross_share' => $pricing['developer_gross_share'],
            'mdr_amount' => $pricing['mdr_amount'],
            'developer_net_share' => $pricing['developer_net_share'],
            'transaction_status' => $responseData['transaction_status'] ?? 'pending',
            'payment_status' => 'pending',
            'qris_url' => $qrisUrl,
            'qr_string' => $qrString,
            'expired_at' => $expiredAt,
            'raw_response' => $responseData,
        ]);

        // Create unified PaymentItem for this payment
        PaymentItem::create([
            'payment_id' => $payment->id,
            'submission_id' => $submission->id,
            'item_type' => 'publication',
            'item_name' => $itemName,
            'original_amount' => $pricing['original_amount'] ?? $grossAmount,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'gross_amount' => $grossAmount,
            'journal_share' => $pricing['journal_share'],
            'developer_gross_share' => $pricing['developer_gross_share'],
            'mdr_amount' => $pricing['mdr_amount'],
            'developer_net_share' => $pricing['developer_net_share'],
        ]);

        // Keep submission in pending payment
        $submission->update(['payment_status' => 'pending']);

        return $payment;
    }

    /**
     * Check transaction status directly from Midtrans API /v2/{order_id}/status.
     */
    public function checkStatusFromMidtrans(Payment $payment): Payment
    {
        if ($payment->isPaid()) {
            $payment->ensureInvoiceNumber();
            return $payment;
        }

        $serverKey = $this->getServerKey();
        $authHeader = 'Basic ' . base64_encode($serverKey . ':');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => $authHeader,
            ])->timeout(10)->get($this->getBaseUrl() . '/v2/' . $payment->order_id . '/status');

            if ($response->successful()) {
                $data = $response->json();
                $transStatus = $data['transaction_status'] ?? null;

                if (in_array($transStatus, ['capture', 'settlement'])) {
                    $this->fulfillmentService->markAsPaid($payment, $transStatus, $data);
                } elseif ($transStatus === 'expire') {
                    $this->fulfillmentService->markAsExpired($payment, $data);
                } elseif (in_array($transStatus, ['deny', 'cancel'])) {
                    $this->fulfillmentService->markAsFailed($payment, $transStatus, $data);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Failed to check status from Midtrans for {$payment->order_id}: " . $e->getMessage());
        }

        return $payment->fresh();
    }

    public function checkStatus(Payment $payment): Payment
    {
        return $this->checkStatusFromMidtrans($payment);
    }

    public function cancelPayment(Payment $payment): bool
    {
        $serverKey = $this->getServerKey();
        $authHeader = 'Basic ' . base64_encode($serverKey . ':');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => $authHeader,
            ])->timeout(10)->post($this->getBaseUrl() . '/v2/' . $payment->order_id . '/cancel');

            if ($response->successful()) {
                $this->fulfillmentService->markAsFailed($payment, 'cancel', $response->json() ?? []);
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("Midtrans cancelPayment failed for {$payment->order_id}: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Get or create pending payment specifically for DOI Addon.
     */
    public function getOrCreateDoiPayment(Submission $submission): Payment
    {
        // 1. Check if submission already has a paid DOI addon payment
        $paidDoi = $submission->payments()->where('type', 'doi_addon')->where('payment_status', 'paid')->first();
        if ($paidDoi) {
            $paidDoi->ensureInvoiceNumber();
            return $paidDoi;
        }

        // 2. If submission already has active DOI (e.g. from initial package or admin), create/return a paid record
        if ($submission->has_doi && !empty($submission->repository_identifier)) {
            $existing = $submission->payments()->where('type', 'doi_addon')->first();
            if ($existing) {
                $existing->update(['payment_status' => 'paid', 'transaction_status' => 'settlement']);
                $existing->ensureInvoiceNumber();
                return $existing;
            }
            $payment = Payment::create([
                'user_id' => $submission->user_id ?? Auth::id(),
                'submission_id' => $submission->id,
                'order_id' => 'DOI-' . $submission->id . '-INITIAL',
                'payment_method' => 'qris',
                'type' => 'doi_addon',
                'payer_name' => $submission->author_name ?: 'Author',
                'payer_email' => $submission->email ?: '',
                'gross_amount' => 20000.00,
                'journal_share' => 14860.00,
                'developer_gross_share' => 5000.00,
                'mdr_amount' => 140.00,
                'developer_net_share' => 5000.00,
                'transaction_status' => 'settlement',
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            PaymentItem::create([
                'payment_id' => $payment->id,
                'submission_id' => $submission->id,
                'item_type' => 'doi_addon',
                'item_name' => 'Add-on Repository Identifier (DOI Resmi) - ' . ($submission->journal?->name ?? 'Jurnal CIB'),
                'gross_amount' => 20000.00,
                'journal_share' => 14860.00,
                'developer_gross_share' => 5000.00,
                'mdr_amount' => 140.00,
                'developer_net_share' => 5000.00,
            ]);

            $payment->ensureInvoiceNumber();
            return $payment;
        }

        $user = $submission->user ?? Auth::user();
        $pricing = $this->pricingService->calculateDoiAddon($user);
        $currentGross = (int) round($pricing['gross_amount']);

        // Check for latest pending DOI payment
        $latestDoi = $submission->payments()->where('type', 'doi_addon')->latest()->first();

        if ($latestDoi && $latestDoi->payment_status === 'pending') {
            if (($latestDoi->expired_at && now()->greaterThanOrEqualTo($latestDoi->expired_at)) || (int) $latestDoi->gross_amount !== $currentGross) {
                $this->cancelAndExpirePendingPayment($latestDoi);
            } else {
                if (empty($latestDoi->qris_url) && !empty($latestDoi->qr_string)) {
                    $latestDoi->qris_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($latestDoi->qr_string);
                    $latestDoi->save();
                }
                return $latestDoi;
            }
        }

        return $this->chargeDoiAddonQris($submission);
    }

    public function chargeDoiQris(Submission $submission): Payment
    {
        return $this->chargeDoiAddonQris($submission);
    }

    public function forceNewDoiPayment(Submission $submission): Payment
    {
        $paidDoi = $submission->payments()->where('type', 'doi_addon')->where('payment_status', 'paid')->first();
        if ($paidDoi) {
            $paidDoi->ensureInvoiceNumber();
            return $paidDoi;
        }

        $submission->payments()->where('type', 'doi_addon')->where('payment_status', 'pending')
            ->get()
            ->each(fn ($p) => $this->cancelAndExpirePendingPayment($p));

        return $this->chargeDoiAddonQris($submission);
    }

    /**
     * Charge QRIS for DOI Add-on (Rp 20,000).
     */
    public function chargeDoiAddonQris(Submission $submission): Payment
    {
        $userId = $submission->user_id ?? Auth::id();
        $user = $submission->user ?? Auth::user();
        $pricing = $this->pricingService->calculateDoiAddon($user);
        $grossAmount = (int) round($pricing['gross_amount']);

        $orderId = 'DOI-' . $submission->id . '-' . time() . '-' . Str::upper(Str::random(4));

        $serverKey = $this->getServerKey();
        $authHeader = 'Basic ' . base64_encode($serverKey . ':');

        $customerName = !empty($submission->author_name) ? $submission->author_name : ($submission->user?->name ?? 'Author');
        $customerEmail = !empty($submission->email) ? $submission->email : ($submission->user?->email ?? 'author@cib.institute');

        $itemName = 'Add-on DOI - ' . ($submission->journal?->name ?? 'Jurnal CIB');

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
            'customer_details' => [
                'first_name' => Str::limit($customerName, 45, ''),
                'email' => $customerEmail,
            ],
            'item_details' => [
                [
                    'id' => 'DOI-' . $submission->id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => Str::limit($itemName, 50, ''),
                ]
            ],
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $authHeader,
        ])->timeout(20)->post($this->getBaseUrl() . '/v2/charge', $payload);

        $responseData = $response->json();

        if (!$response->successful() || empty($responseData)) {
            $errorMsg = $responseData['status_message'] ?? 'Gagal menghubungi server Midtrans.';
            throw new \Exception("Gagal membuat QRIS Add-on DOI: " . $errorMsg);
        }

        $qrisUrl = null;
        if (!empty($responseData['actions']) && is_array($responseData['actions'])) {
            foreach ($responseData['actions'] as $action) {
                if (($action['name'] ?? '') === 'generate-qr-code') {
                    $qrisUrl = $action['url'] ?? null;
                    break;
                }
            }
        }

        $qrString = $responseData['qr_string'] ?? null;
        if (empty($qrisUrl) && !empty($qrString)) {
            $qrisUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($qrString);
        }
        $expiredAt = now()->addMinutes(15);
        if (!empty($responseData['expiry_time'])) {
            try {
                $expiredAt = Carbon::parse($responseData['expiry_time']);
            } catch (\Exception $e) {}
        }

        $payment = Payment::create([
            'user_id' => $userId,
            'submission_id' => $submission->id,
            'order_id' => $orderId,
            'transaction_id' => $responseData['transaction_id'] ?? null,
            'gateway' => 'midtrans',
            'payment_method' => 'qris',
            'type' => 'doi_addon',
            'payer_name' => $customerName,
            'payer_email' => $customerEmail,
            'original_amount' => $pricing['original_amount'] ?? $grossAmount,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'gross_amount' => $grossAmount,
            'journal_share' => $pricing['journal_share'],
            'developer_gross_share' => $pricing['developer_gross_share'],
            'mdr_amount' => $pricing['mdr_amount'],
            'developer_net_share' => $pricing['developer_net_share'],
            'transaction_status' => $responseData['transaction_status'] ?? 'pending',
            'payment_status' => 'pending',
            'qris_url' => $qrisUrl,
            'qr_string' => $qrString,
            'expired_at' => $expiredAt,
            'raw_response' => $responseData,
        ]);

        PaymentItem::create([
            'payment_id' => $payment->id,
            'submission_id' => $submission->id,
            'item_type' => 'doi_addon',
            'item_name' => $itemName,
            'original_amount' => $pricing['original_amount'] ?? $grossAmount,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'gross_amount' => $grossAmount,
            'journal_share' => $pricing['journal_share'],
            'developer_gross_share' => $pricing['developer_gross_share'],
            'mdr_amount' => $pricing['mdr_amount'],
            'developer_net_share' => $pricing['developer_net_share'],
        ]);

        return $payment;
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
                Log::error("DOI generation failed for submission #{$submission->id}: " . $e->getMessage());
            }
        }

        try {
            \App\Services\OjsSubmissionService::submitInBackground($submission);
        } catch (\Throwable $e) {
            Log::warning("OJS sync failed for submission #{$submission->id}: " . $e->getMessage());
        }
    }

    /**
     * Get or create pending QRIS payment for Replace PDF service.
     */
    public function getOrCreateReplacePdfPayment(Submission $submission, ?string $tempFilePath = null): Payment
    {
        // 1. If no explicit new tempFilePath is provided, check if replace_pdf has already been paid
        if (empty($tempFilePath)) {
            $paid = $submission->payments()
                ->where('type', 'replace_pdf')
                ->where('payment_status', 'paid')
                ->latest()
                ->first();

            if ($paid) {
                // Check if there is a valid newer unexpired replacement in progress
                $pending = $submission->payments()
                    ->where('type', 'replace_pdf')
                    ->where('payment_status', 'pending')
                    ->where('id', '>', $paid->id)
                    ->latest()
                    ->first();

                if ($pending && !$pending->isExpired()) {
                    $pRaw = is_array($pending->raw_response) ? $pending->raw_response : [];
                    $pPath = $pRaw['new_pdf_path'] ?? null;
                    if ($pPath && Storage::disk('public')->exists($pPath)) {
                        $latest = $pending;
                    } else {
                        $pending->update([
                            'payment_status' => 'expired',
                            'transaction_status' => 'expire',
                        ]);
                        $paid->ensureInvoiceNumber();
                        return $paid;
                    }
                } else {
                    if ($pending) {
                        $pending->update([
                            'payment_status' => 'expired',
                            'transaction_status' => 'expire',
                        ]);
                    }
                    $paid->ensureInvoiceNumber();
                    return $paid;
                }
            }
        }

        if (empty($tempFilePath)) {
            $prev = $submission->payments()->where('type', 'replace_pdf')->latest()->first();
            $tempFilePath = $prev?->raw_response['new_pdf_path'] ?? null;
        }

        $user = $submission->user ?? Auth::user();
        $pricing = $this->pricingService->calculateReplacePdf($user);
        $currentGross = (int) round($pricing['gross_amount']);

        // Check for latest pending Replace PDF payment
        $latest = $submission->payments()->where('type', 'replace_pdf')->latest()->first();

        if ($latest && $latest->payment_status === 'pending') {
            if (($latest->expired_at && now()->greaterThanOrEqualTo($latest->expired_at)) || (int) $latest->gross_amount !== $currentGross) {
                $this->cancelAndExpirePendingPayment($latest);
            } else {
                if ($tempFilePath) {
                    $raw = is_array($latest->raw_response) ? $latest->raw_response : [];
                    $raw['new_pdf_path'] = $tempFilePath;
                    $latest->raw_response = $raw;
                    $latest->save();
                }

                if (empty($latest->qris_url) && !empty($latest->qr_string)) {
                    $latest->qris_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($latest->qr_string);
                    $latest->save();
                }
                return $latest;
            }
        }

        return $this->chargeReplacePdfQris($submission, $tempFilePath ?: '');
    }

    public function forceNewReplacePdfPayment(Submission $submission, string $tempFilePath = ''): Payment
    {
        $submission->payments()->where('type', 'replace_pdf')->where('payment_status', 'pending')
            ->get()
            ->each(fn ($p) => $this->cancelAndExpirePendingPayment($p));

        return $this->chargeReplacePdfQris($submission, $tempFilePath);
    }

    /**
     * Charge QRIS for Replace PDF Service (Rp 25,000).
     */
    public function chargeReplacePdfQris(Submission $submission, string $tempFilePath = ''): Payment
    {
        if (empty($tempFilePath)) {
            $prev = $submission->payments()->where('type', 'replace_pdf')->latest()->first();
            $tempFilePath = $prev?->raw_response['new_pdf_path'] ?? '';
        }

        $userId = $submission->user_id ?? Auth::id();
        $user = $submission->user ?? Auth::user();
        $pricing = $this->pricingService->calculateReplacePdf($user);
        $grossAmount = (int) round($pricing['gross_amount']);

        $orderId = 'REPLACE-PDF-' . $submission->id . '-' . time() . '-' . Str::upper(Str::random(4));

        $serverKey = $this->getServerKey();
        $authHeader = 'Basic ' . base64_encode($serverKey . ':');

        $customerName = !empty($submission->author_name) ? $submission->author_name : ($submission->user?->name ?? 'Author');
        $customerEmail = !empty($submission->email) ? $submission->email : ($submission->user?->email ?? 'author@cib.institute');

        $itemName = 'Ganti PDF Naskah - ' . ($submission->journal?->name ?? 'Jurnal CIB');

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
            'customer_details' => [
                'first_name' => Str::limit($customerName, 45, ''),
                'email' => $customerEmail,
            ],
            'item_details' => [
                [
                    'id' => 'REPLACE-PDF-' . $submission->id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => Str::limit($itemName, 50, ''),
                ]
            ],
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $authHeader,
        ])->timeout(20)->post($this->getBaseUrl() . '/v2/charge', $payload);

        $responseData = $response->json();

        if (!$response->successful() || empty($responseData)) {
            $errorMsg = $responseData['status_message'] ?? 'Gagal menghubungi server Midtrans.';
            throw new \Exception("Gagal membuat QRIS Ganti PDF: " . $errorMsg);
        }

        $qrisUrl = null;
        if (!empty($responseData['actions']) && is_array($responseData['actions'])) {
            foreach ($responseData['actions'] as $action) {
                if (($action['name'] ?? '') === 'generate-qr-code') {
                    $qrisUrl = $action['url'] ?? null;
                    break;
                }
            }
        }

        $qrString = $responseData['qr_string'] ?? null;
        if (empty($qrisUrl) && !empty($qrString)) {
            $qrisUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($qrString);
        }
        $expiredAt = now()->addMinutes(15);
        if (!empty($responseData['expiry_time'])) {
            try {
                $expiredAt = Carbon::parse($responseData['expiry_time']);
            } catch (\Exception $e) {}
        }

        $responseData['new_pdf_path'] = $tempFilePath;

        $payment = Payment::create([
            'user_id' => $userId,
            'submission_id' => $submission->id,
            'order_id' => $orderId,
            'transaction_id' => $responseData['transaction_id'] ?? null,
            'gateway' => 'midtrans',
            'payment_method' => 'qris',
            'type' => 'replace_pdf',
            'payer_name' => $customerName,
            'payer_email' => $customerEmail,
            'original_amount' => $pricing['original_amount'] ?? $grossAmount,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'gross_amount' => $grossAmount,
            'journal_share' => $pricing['journal_share'],
            'developer_gross_share' => $pricing['developer_gross_share'],
            'mdr_amount' => $pricing['mdr_amount'],
            'developer_net_share' => $pricing['developer_net_share'],
            'transaction_status' => $responseData['transaction_status'] ?? 'pending',
            'payment_status' => 'pending',
            'qris_url' => $qrisUrl,
            'qr_string' => $qrString,
            'expired_at' => $expiredAt,
            'raw_response' => $responseData,
        ]);

        PaymentItem::create([
            'payment_id' => $payment->id,
            'submission_id' => $submission->id,
            'item_type' => 'replace_pdf',
            'item_name' => $itemName,
            'original_amount' => $pricing['original_amount'] ?? $grossAmount,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'gross_amount' => $grossAmount,
            'journal_share' => $pricing['journal_share'],
            'developer_gross_share' => $pricing['developer_gross_share'],
            'mdr_amount' => $pricing['mdr_amount'],
            'developer_net_share' => $pricing['developer_net_share'],
        ]);

        return $payment;
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

            // Move temp file to permanent location
            Storage::disk('public')->put($targetPath, Storage::disk('public')->get($tempPath));
            Storage::disk('public')->delete($tempPath);

            $submission->manuscript_file = $targetPath;
            $submission->save();
            $submission->touch();

            Log::info("Replace PDF applied successfully for Submission #{$submission->id}. Target: {$targetPath}");

            // Automatically sync updated PDF file to OJS in background
            try {
                \App\Services\OjsSubmissionService::submitInBackground($submission);
            } catch (\Throwable $e) {
                Log::error("Failed to dispatch OJS sync after PDF replacement for Submission #{$submission->id}: " . $e->getMessage());
            }
        } else {
            Log::warning("Replace PDF: No valid temp file found for Submission #{$submission->id}, Payment #{$payment->id}");
        }
    }

    /**
     * Get or create pending QRIS payment for multiple submissions.
     */
    public function getOrCreateBulkPayment($submissions): Payment
    {
        $submissionIds = $submissions->pluck('id')->sort()->values()->toArray();

        // 1. Check if there is already a PAID bulk payment for these exact submissions
        $paidPayment = Payment::where('type', 'bulk_submission')
            ->where('payment_status', 'paid')
            ->get()
            ->first(function ($p) use ($submissionIds) {
                $ids = is_array($p->submission_ids) ? $p->submission_ids : [];
                sort($ids);
                return $ids === $submissionIds;
            });

        if ($paidPayment) {
            $paidPayment->ensureInvoiceNumber();
            return $paidPayment;
        }

        // 2. Check if all submissions are already approved/paid
        $allPaid = $submissions->every(fn($s) => $s->payment_status === 'paid' || $s->status === 'Approved');
        if ($allPaid) {
            $anyPayment = Payment::where('type', 'bulk_submission')
                ->whereIn('submission_id', $submissionIds)
                ->where('payment_status', 'paid')
                ->latest()
                ->first();
            if ($anyPayment) {
                $anyPayment->ensureInvoiceNumber();
                return $anyPayment;
            }
        }

        // 3. Check if there is already an active pending bulk payment for the exact same submissions
        $payerUser = Auth::user() ?? $submissions->first()->user;
        $currentPricing = $this->pricingService->calculateBulk($submissions, $payerUser);
        $currentGross = (int) round($currentPricing['gross_amount']);

        $pendingPayment = Payment::where('type', 'bulk_submission')
            ->where('payment_status', 'pending')
            ->where('expired_at', '>', now())
            ->whereNotNull('qris_url')
            ->get()
            ->first(function ($p) use ($submissionIds) {
                $ids = is_array($p->submission_ids) ? $p->submission_ids : [];
                sort($ids);
                return $ids === $submissionIds;
            });

        if ($pendingPayment) {
            $paymentGross = (int) round($pendingPayment->gross_amount);
            if ($paymentGross !== $currentGross) {
                $this->cancelAndExpirePendingPayment($pendingPayment);
            } else {
                return $pendingPayment;
            }
        }

        return $this->chargeBulkQris($submissions);
    }

    public function forceNewBulkPayment($submissions): Payment
    {
        $submissionIds = $submissions->pluck('id')->sort()->values()->toArray();

        Payment::where('type', 'bulk_submission')
            ->where('payment_status', 'pending')
            ->get()
            ->each(function ($p) use ($submissionIds) {
                $ids = is_array($p->submission_ids) ? $p->submission_ids : [];
                sort($ids);
                if ($ids === $submissionIds) {
                    $this->cancelAndExpirePendingPayment($p);
                }
            });

        return $this->chargeBulkQris($submissions);
    }

    /**
     * Create bulk QRIS transaction for multiple submissions.
     */
    public function chargeBulkQris($submissions): Payment
    {
        $payerUser = Auth::user() ?? $submissions->first()->user;
        $pricingData = $this->pricingService->calculateBulk($submissions, $payerUser);
        $submissionIds = $submissions->pluck('id')->sort()->values()->toArray();
        $firstId = $submissionIds[0] ?? 0;
        $orderId = 'BULK-' . count($submissionIds) . 'SUB-' . $firstId . '-' . time() . '-' . strtoupper(Str::random(4));

        $itemDetails = [];
        foreach ($pricingData['items'] as $item) {
            $sub = $item['submission'];
            $pr = $item['pricing'];
            $itemDetails[] = [
                'id' => 'SUB-' . $sub->id,
                'price' => (int) $pr['gross_amount'],
                'quantity' => 1,
                'name' => Str::limit('Naskah #' . $sub->id . ' - ' . ($sub->title ?: 'Artikel'), 45),
            ];
        }

        $payerName = $payerUser?->name ?? ($submissions->first()->author_name ?: 'Author Kolektif');
        $payerEmail = $payerUser?->email ?? ($submissions->first()->email ?: 'author@example.com');

        $customer = [
            'first_name' => Str::limit($payerName, 45, ''),
            'email' => $payerEmail,
        ];

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $pricingData['gross_amount'],
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customer,
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        $serverKey = $this->getServerKey();
        $authHeader = 'Basic ' . base64_encode($serverKey . ':');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $authHeader,
        ])->post($this->getBaseUrl() . '/v2/charge', $payload);

        $responseData = $response->json();

        if (!$response->successful()) {
            Log::error('Midtrans Bulk QRIS charge failed: ' . json_encode($responseData));
            throw new \Exception('Gagal membuat transaksi Midtrans QRIS Bulk: ' . ($responseData['status_message'] ?? 'Error tidak diketahui'));
        }

        $qrisUrl = null;
        $qrString = $responseData['qr_string'] ?? null;

        if (!empty($responseData['actions'])) {
            foreach ($responseData['actions'] as $action) {
                if (($action['name'] ?? '') === 'generate-qr-code') {
                    $qrisUrl = $action['url'] ?? null;
                    break;
                }
            }
        }

        if (empty($qrisUrl) && !empty($qrString)) {
            $qrisUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($qrString);
        }

        $expiredAt = !empty($responseData['expiry_time'])
            ? Carbon::parse($responseData['expiry_time'])
            : now()->addMinutes(15);

        $payment = Payment::create([
            'user_id' => $payerUser?->id,
            'submission_id' => $firstId,
            'submission_ids' => $submissionIds,
            'order_id' => $orderId,
            'transaction_id' => $responseData['transaction_id'] ?? null,
            'gateway' => 'midtrans',
            'payment_method' => 'qris',
            'type' => 'bulk_submission',
            'payer_name' => $payerName,
            'payer_email' => $payerEmail,
            'original_amount' => $pricingData['original_amount'] ?? $pricingData['gross_amount'],
            'discount_amount' => $pricingData['discount_amount'] ?? 0,
            'gross_amount' => $pricingData['gross_amount'],
            'journal_share' => $pricingData['journal_share'],
            'developer_gross_share' => $pricingData['developer_gross_share'],
            'mdr_amount' => $pricingData['mdr_amount'],
            'developer_net_share' => $pricingData['developer_net_share'],
            'transaction_status' => $responseData['transaction_status'] ?? 'pending',
            'payment_status' => 'pending',
            'qris_url' => $qrisUrl,
            'qr_string' => $qrString,
            'expired_at' => $expiredAt,
            'raw_response' => $responseData,
        ]);

        // Create individual payment items
        foreach ($pricingData['items'] as $item) {
            $sub = $item['submission'];
            $pr = $item['pricing'];
            PaymentItem::create([
                'payment_id' => $payment->id,
                'submission_id' => $sub->id,
                'item_type' => 'publication',
                'item_name' => 'Naskah #' . $sub->id . ' - ' . ($sub->title ?: 'Artikel') . ' (' . ($sub->journal?->name ?? 'Jurnal') . ')',
                'original_amount' => $pr['original_amount'] ?? $pr['gross_amount'],
                'discount_amount' => $pr['discount_amount'] ?? 0,
                'gross_amount' => $pr['gross_amount'],
                'journal_share' => $pr['journal_share'],
                'developer_gross_share' => $pr['developer_gross_share'],
                'mdr_amount' => $pr['mdr_amount'],
                'developer_net_share' => $pr['developer_net_share'],
            ]);
        }

        return $payment;
    }
}
