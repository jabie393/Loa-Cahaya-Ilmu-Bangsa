<?php

namespace App\Services\PaymentGateways;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Submission;
use App\Services\SubmissionPricingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BelibayarQrisService implements PaymentGatewayInterface
{
    protected SubmissionPricingService $pricingService;
    protected PaymentFulfillmentService $fulfillmentService;

    public function __construct(
        SubmissionPricingService $pricingService,
        PaymentFulfillmentService $fulfillmentService
    ) {
        $this->pricingService = $pricingService;
        $this->fulfillmentService = $fulfillmentService;
    }

    public function getGatewayName(): string
    {
        return 'belibayar';
    }

    public function getApiKey(): string
    {
        $key = config('services.belibayar.api_key') ?: env('BELIBAYAR_API_KEY', '');
        return trim((string) $key);
    }

    public function getSecretKey(): string
    {
        $key = config('services.belibayar.secret_key') ?: env('BELIBAYAR_SECRET_KEY', '');
        return trim((string) $key);
    }

    public function getWebhookSecret(): string
    {
        $secret = config('services.belibayar.webhook_secret') ?: env('BELIBAYAR_WEBHOOK_SECRET', '');
        $secret = trim((string) $secret);
        return !empty($secret) ? $secret : $this->getSecretKey();
    }

    public function isProduction(): bool
    {
        $isProd = config('services.belibayar.is_production');
        if ($isProd === null) {
            $isProd = env('BELIBAYAR_IS_PRODUCTION', false);
        }
        return (bool) $isProd;
    }

    public function getBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.belibayar.id/direct/v1'
            : 'https://api.belibayar.id/direct/v1/sandbox';
    }

    /**
     * Sanitize customer name to meet Belibayar requirement:
     * 5-25 chars alphanumeric, space, underscore, dash (without PT/CV prefix).
     */
    public function sanitizeCustomerName(?string $name): string
    {
        $name = $name ?: 'Author CIB';
        // Remove accents and any characters other than alphanumeric, space, underscore, dash
        $name = preg_replace('/[^a-zA-Z0-9\s_-]/', '', $name);
        // Collapse multiple whitespace
        $name = preg_replace('/\s+/', ' ', trim($name));
        // Strip PT / CV prefix
        $name = preg_replace('/^(pt|cv)\.?\s+/i', '', $name);

        if (strlen($name) < 5) {
            $name = str_pad($name, 5, ' User');
        }

        if (strlen($name) > 25) {
            $name = substr($name, 0, 25);
        }

        return trim($name);
    }

    /**
     * Check if a given QR URL or SVG string is an empty/dummy stub.
     * Belibayar Sandbox returns: data:image/svg+xml;base64,PHN2Zy8+ (<svg/>).
     */
    public function isDummyQr(?string $qr): bool
    {
        if (empty($qr)) {
            return true;
        }
        if (str_starts_with($qr, 'data:image/svg+xml;base64,')) {
            $decoded = base64_decode(substr($qr, strlen('data:image/svg+xml;base64,')));
            $cleaned = trim(strtolower($decoded));
            if ($cleaned === '<svg/>' || $cleaned === '<svg></svg>' || strlen($cleaned) < 20 || !str_contains($cleaned, 'path')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Resolve a valid, renderable QR URL or QR content.
     * If an empty/dummy SVG is received from Sandbox, generate a clean QR code image URL.
     */
    public function resolveQrUrl(?string $rawQr, ?string $rawContent, string $fallbackData): string
    {
        if (!empty($rawQr) && !$this->isDummyQr($rawQr)) {
            return $rawQr;
        }

        $content = !empty($rawContent) ? $rawContent : $fallbackData;
        return 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($content);
    }

    /**
     * Send signed POST request to Belibayar API.
     */
    protected function postRequest(string $endpoint, array $payload): array
    {
        $rawBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $rawBody, $this->getSecretKey());

        $url = rtrim($this->getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');

        Log::info("Belibayar POST {$url}", ['payload' => $payload]);

        $response = Http::withHeaders([
            'X-Api-Key' => $this->getApiKey(),
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
        ])->withBody($rawBody, 'application/json')->timeout(25)->post($url);

        $responseData = $response->json();

        Log::info("Belibayar Response [{$response->status()}]", ['body' => $responseData]);

        if (!$response->successful() || empty($responseData)) {
            $errorMessage = $responseData['messages'] ?? ($responseData['message'] ?? 'Gagal menghubungi server Belibayar.id');
            if (!empty($responseData['data']['fields']) && is_array($responseData['data']['fields'])) {
                $fields = [];
                foreach ($responseData['data']['fields'] as $fKey => $fVal) {
                    $fields[] = "{$fKey}: {$fVal}";
                }
                $errorMessage .= ' (' . implode(', ', $fields) . ')';
            }
            throw new \Exception("Belibayar Error ({$response->status()}): {$errorMessage}");
        }

        return $responseData;
    }

    /**
     * Send signed GET request to Belibayar API.
     */
    protected function getRequest(string $endpoint): array
    {
        $ts = (string) time();
        $signature = hash_hmac('sha256', ':' . $ts, $this->getSecretKey());

        $url = rtrim($this->getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');

        Log::info("Belibayar GET {$url}");

        $response = Http::withHeaders([
            'X-Api-Key' => $this->getApiKey(),
            'X-Timestamp' => $ts,
            'X-Signature' => $signature,
            'Accept' => 'application/json',
        ])->withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
        ])->timeout(20)->get($url);

        $responseData = $response->json();

        Log::info("Belibayar GET Response [{$response->status()}]", ['body' => $responseData]);

        if (!$response->successful() || empty($responseData)) {
            $errorMessage = $responseData['messages'] ?? ($responseData['message'] ?? 'Gagal memverifikasi status Belibayar.id');
            if (!empty($responseData['data']['fields']) && is_array($responseData['data']['fields'])) {
                $fields = [];
                foreach ($responseData['data']['fields'] as $fKey => $fVal) {
                    $fields[] = "{$fKey}: {$fVal}";
                }
                $errorMessage .= ' (' . implode(', ', $fields) . ')';
            }
            throw new \Exception("Belibayar Error ({$response->status()}): {$errorMessage}");
        }

        return $responseData;
    }

    /**
     * --------------------------------------------------------------------------
     * 1. Single Submission Payment
     * --------------------------------------------------------------------------
     */

    public function getOrCreatePayment(Submission $submission): Payment
    {
        $paidPayment = $submission->payments()
            ->where('payment_status', 'paid')
            ->where('type', 'submission')
            ->first();
        if ($paidPayment) {
            $paidPayment->ensureInvoiceNumber();
            return $paidPayment;
        }

        $pricing = $this->pricingService->calculate($submission);
        $currentGross = (int) round($pricing['gross_amount']);

        $latestPayment = $submission->payments()
            ->where('type', 'submission')
            ->latest()
            ->first();

        if ($latestPayment && $latestPayment->payment_status === 'pending') {
            $paymentGross = (int) round($latestPayment->gross_amount);

            // If gateway matches and amount matches and not expired
            if ($latestPayment->gateway === 'belibayar' && !$latestPayment->isExpired() && $paymentGross === $currentGross) {
                if ($this->isDummyQr($latestPayment->qris_url)) {
                    $latestPayment = $this->checkStatus($latestPayment);
                }
                if (!$this->isDummyQr($latestPayment->qris_url)) {
                    return $latestPayment;
                }
            }

            // Otherwise expire current pending and generate new
            $latestPayment->update([
                'payment_status' => 'expired',
                'transaction_status' => 'expire',
            ]);
        }

        return $this->chargeQris($submission);
    }

    public function forceNewPayment(Submission $submission): Payment
    {
        $paidPayment = $submission->payments()->where('payment_status', 'paid')->first();
        if ($paidPayment) {
            $paidPayment->ensureInvoiceNumber();
            return $paidPayment;
        }

        $submission->payments()
            ->where('payment_status', 'pending')
            ->update([
                'payment_status' => 'expired',
                'transaction_status' => 'expire',
            ]);

        return $this->chargeQris($submission);
    }

    public function chargeQris(Submission $submission): Payment
    {
        $pricing = $this->pricingService->calculate($submission);
        $grossAmount = (int) round($pricing['gross_amount']);

        $orderId = 'BYR-SUB-' . $submission->id . '-' . time() . '-' . Str::upper(Str::random(4));

        $userId = $submission->user_id ?? Auth::id();
        $rawName = !empty($submission->author_name) ? $submission->author_name : ($submission->user?->name ?? 'Author');
        $customerName = $this->sanitizeCustomerName($rawName);
        $customerEmail = !empty($submission->email) ? $submission->email : ($submission->user?->email ?? 'author@cib.institute');
        $itemName = $pricing['tier_name'] . ' - ' . ($submission->journal?->name ?? 'Jurnal CIB');

        $expiredTime = time() + 86400; // 24 hours

        $payload = [
            'reference' => $orderId,
            'amount' => $grossAmount,
            'pay_method' => [
                'method' => 'qris',
            ],
            'static_qr' => false,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'expired_time' => $expiredTime,
            'callback_url' => url('/api/belibayar/webhook'),
        ];

        $response = $this->postRequest('/payment/charge', $payload);
        $data = $response['data'] ?? [];

        $rawQr = $data['qr_code'] ?? ($data['qr_url'] ?? null);
        $qrContent = $data['qr_content'] ?? ($data['qr_string'] ?? null);
        $qrUrl = $this->resolveQrUrl($rawQr, $qrContent, $orderId);

        $expiredAt = !empty($data['expired_at']) ? Carbon::createFromTimestamp($data['expired_at']) : Carbon::createFromTimestamp($expiredTime);

        $payment = Payment::create([
            'user_id' => $userId,
            'submission_id' => $submission->id,
            'order_id' => $orderId,
            'transaction_id' => $data['transaction_id'] ?? null,
            'gateway' => 'belibayar',
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
            'transaction_status' => $data['status'] ?? 'pending',
            'payment_status' => 'pending',
            'qris_url' => $qrUrl,
            'qr_string' => $qrContent ?? $qrUrl,
            'expired_at' => $expiredAt,
            'raw_response' => $response,
        ]);

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

        $submission->update(['payment_status' => 'pending']);

        return $payment;
    }

    /**
     * --------------------------------------------------------------------------
     * 2. DOI Add-on Payment
     * --------------------------------------------------------------------------
     */

    public function getOrCreateDoiPayment(Submission $submission): Payment
    {
        $paidDoi = $submission->payments()->where('type', 'doi_addon')->where('payment_status', 'paid')->first();
        if ($paidDoi) {
            $paidDoi->ensureInvoiceNumber();
            return $paidDoi;
        }

        $latest = $submission->payments()->where('type', 'doi_addon')->latest()->first();
        if ($latest && $latest->payment_status === 'pending') {
            if ($latest->gateway === 'belibayar' && !$latest->isExpired()) {
                if ($this->isDummyQr($latest->qris_url)) {
                    $latest = $this->checkStatus($latest);
                }
                if (!$this->isDummyQr($latest->qris_url)) {
                    return $latest;
                }
            }

            $latest->update([
                'payment_status' => 'expired',
                'transaction_status' => 'expire',
            ]);
        }

        return $this->chargeDoiQris($submission);
    }

    public function forceNewDoiPayment(Submission $submission): Payment
    {
        $paidDoi = $submission->payments()->where('type', 'doi_addon')->where('payment_status', 'paid')->first();
        if ($paidDoi) {
            $paidDoi->ensureInvoiceNumber();
            return $paidDoi;
        }

        $submission->payments()->where('type', 'doi_addon')->where('payment_status', 'pending')->update([
            'payment_status' => 'expired',
            'transaction_status' => 'expire',
        ]);

        return $this->chargeDoiQris($submission);
    }

    public function chargeDoiAddonQris(Submission $submission): Payment
    {
        return $this->chargeDoiQris($submission);
    }

    public function chargeDoiQris(Submission $submission): Payment
    {
        $userId = $submission->user_id ?? Auth::id();
        $user = $submission->user ?? Auth::user();
        $pricing = $this->pricingService->calculateDoiAddon($user);
        $grossAmount = (int) round($pricing['gross_amount']);

        $orderId = 'BYR-DOI-' . $submission->id . '-' . time() . '-' . Str::upper(Str::random(4));

        $rawName = !empty($submission->author_name) ? $submission->author_name : ($submission->user?->name ?? 'Author');
        $customerName = $this->sanitizeCustomerName($rawName);
        $customerEmail = !empty($submission->email) ? $submission->email : ($submission->user?->email ?? 'author@cib.institute');
        $itemName = 'Aktivasi DOI Resmi CIB - ' . ($submission->journal?->name ?? 'Jurnal CIB');

        $expiredTime = time() + 86400;

        $payload = [
            'reference' => $orderId,
            'amount' => $grossAmount,
            'pay_method' => ['method' => 'qris'],
            'static_qr' => false,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'expired_time' => $expiredTime,
            'callback_url' => url('/api/belibayar/webhook'),
        ];

        $response = $this->postRequest('/payment/charge', $payload);
        $data = $response['data'] ?? [];

        $rawQr = $data['qr_code'] ?? ($data['qr_url'] ?? null);
        $qrContent = $data['qr_content'] ?? ($data['qr_string'] ?? null);
        $qrUrl = $this->resolveQrUrl($rawQr, $qrContent, $orderId);

        $expiredAt = !empty($data['expired_at']) ? Carbon::createFromTimestamp($data['expired_at']) : Carbon::createFromTimestamp($expiredTime);

        $payment = Payment::create([
            'user_id' => $userId,
            'submission_id' => $submission->id,
            'order_id' => $orderId,
            'transaction_id' => $data['transaction_id'] ?? null,
            'gateway' => 'belibayar',
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
            'transaction_status' => $data['status'] ?? 'pending',
            'payment_status' => 'pending',
            'qris_url' => $qrUrl,
            'qr_string' => $qrContent ?? $qrUrl,
            'expired_at' => $expiredAt,
            'raw_response' => $response,
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
     * --------------------------------------------------------------------------
     * 3. Replace PDF Service Payment
     * --------------------------------------------------------------------------
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

        $latest = $submission->payments()->where('type', 'replace_pdf')->latest()->first();
        if ($latest && $latest->payment_status === 'pending') {
            if ($latest->gateway === 'belibayar' && !$latest->isExpired()) {
                if ($tempFilePath) {
                    $raw = is_array($latest->raw_response) ? $latest->raw_response : [];
                    $raw['new_pdf_path'] = $tempFilePath;
                    $latest->raw_response = $raw;
                    $latest->save();
                }

                if ($this->isDummyQr($latest->qris_url)) {
                    $latest = $this->checkStatus($latest);
                }
                if (!$this->isDummyQr($latest->qris_url)) {
                    return $latest;
                }
            }

            $latest->update([
                'payment_status' => 'expired',
                'transaction_status' => 'expire',
            ]);
        }

        return $this->chargeReplacePdfQris($submission, $tempFilePath ?: '');
    }

    public function forceNewReplacePdfPayment(Submission $submission, string $tempFilePath = ''): Payment
    {
        $paid = $submission->payments()->where('type', 'replace_pdf')->where('payment_status', 'paid')->first();
        if ($paid) {
            $paid->ensureInvoiceNumber();
            return $paid;
        }

        $submission->payments()->where('type', 'replace_pdf')->where('payment_status', 'pending')->update([
            'payment_status' => 'expired',
            'transaction_status' => 'expire',
        ]);

        return $this->chargeReplacePdfQris($submission, $tempFilePath);
    }

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

        $orderId = 'BYR-REPLACE-PDF-' . $submission->id . '-' . time() . '-' . Str::upper(Str::random(4));

        $rawName = !empty($submission->author_name) ? $submission->author_name : ($submission->user?->name ?? 'Author');
        $customerName = $this->sanitizeCustomerName($rawName);
        $customerEmail = !empty($submission->email) ? $submission->email : ($submission->user?->email ?? 'author@cib.institute');
        $itemName = 'Ganti PDF Naskah - ' . ($submission->journal?->name ?? 'Jurnal CIB');

        $expiredTime = time() + 86400;

        $payload = [
            'reference' => $orderId,
            'amount' => $grossAmount,
            'pay_method' => ['method' => 'qris'],
            'static_qr' => false,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'expired_time' => $expiredTime,
            'callback_url' => url('/api/belibayar/webhook'),
        ];

        $response = $this->postRequest('/payment/charge', $payload);
        $data = $response['data'] ?? [];

        $rawQr = $data['qr_code'] ?? ($data['qr_url'] ?? null);
        $qrContent = $data['qr_content'] ?? ($data['qr_string'] ?? null);
        $qrUrl = $this->resolveQrUrl($rawQr, $qrContent, $orderId);

        $expiredAt = !empty($data['expired_at']) ? Carbon::createFromTimestamp($data['expired_at']) : Carbon::createFromTimestamp($expiredTime);

        $response['new_pdf_path'] = $tempFilePath;

        $payment = Payment::create([
            'user_id' => $userId,
            'submission_id' => $submission->id,
            'order_id' => $orderId,
            'transaction_id' => $data['transaction_id'] ?? null,
            'gateway' => 'belibayar',
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
            'transaction_status' => $data['status'] ?? 'pending',
            'payment_status' => 'pending',
            'qris_url' => $qrUrl,
            'qr_string' => $qrContent ?? $qrUrl,
            'expired_at' => $expiredAt,
            'raw_response' => $response,
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
     * --------------------------------------------------------------------------
     * 4. Bulk Submission Payment
     * --------------------------------------------------------------------------
     */

    public function getOrCreateBulkPayment($submissions): Payment
    {
        $submissionIds = $submissions->pluck('id')->sort()->values()->toArray();

        // 1. Check paid bulk payment
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

        // 2. Check pending payment
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
            if ($pendingPayment->gateway === 'belibayar' && !$pendingPayment->isExpired() && $paymentGross === $currentGross) {
                if (empty($pendingPayment->qris_url)) {
                    $pendingPayment = $this->checkStatus($pendingPayment);
                }
                if (!empty($pendingPayment->qris_url)) {
                    return $pendingPayment;
                }
            }

            $pendingPayment->update([
                'payment_status' => 'expired',
                'transaction_status' => 'expire',
            ]);
        }

        return $this->chargeBulkQris($submissions);
    }

    public function forceNewBulkPayment($submissions): Payment
    {
        $submissionIds = $submissions->pluck('id')->sort()->values()->toArray();

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

        Payment::where('type', 'bulk_submission')
            ->where('payment_status', 'pending')
            ->get()
            ->each(function ($p) use ($submissionIds) {
                $ids = is_array($p->submission_ids) ? $p->submission_ids : [];
                sort($ids);
                if ($ids === $submissionIds) {
                    $p->update([
                        'payment_status' => 'expired',
                        'transaction_status' => 'expire',
                    ]);
                }
            });

        return $this->chargeBulkQris($submissions);
    }

    public function chargeBulkQris($submissions): Payment
    {
        $payerUser = Auth::user() ?? $submissions->first()->user;
        $pricingData = $this->pricingService->calculateBulk($submissions, $payerUser);
        $submissionIds = $submissions->pluck('id')->sort()->values()->toArray();
        $firstId = $submissionIds[0] ?? 0;

        $orderId = 'BYR-BULK-' . count($submissionIds) . 'SUB-' . $firstId . '-' . time() . '-' . Str::upper(Str::random(4));
        $payerName = $this->sanitizeCustomerName($payerUser?->name ?? ($submissions->first()->author_name ?: 'Author Kolektif'));
        $payerEmail = $payerUser?->email ?? ($submissions->first()->email ?: 'author@example.com');

        $grossAmount = (int) round($pricingData['gross_amount']);
        $expiredTime = time() + 86400;

        $payload = [
            'reference' => $orderId,
            'amount' => $grossAmount,
            'pay_method' => ['method' => 'qris'],
            'static_qr' => false,
            'customer_name' => $payerName,
            'customer_email' => $payerEmail,
            'expired_time' => $expiredTime,
            'callback_url' => url('/api/belibayar/webhook'),
        ];

        $response = $this->postRequest('/payment/charge', $payload);
        $data = $response['data'] ?? [];

        $rawQr = $data['qr_code'] ?? ($data['qr_url'] ?? null);
        $qrContent = $data['qr_content'] ?? ($data['qr_string'] ?? null);
        $qrUrl = $this->resolveQrUrl($rawQr, $qrContent, $orderId);

        $expiredAt = !empty($data['expired_at']) ? Carbon::createFromTimestamp($data['expired_at']) : Carbon::createFromTimestamp($expiredTime);

        $payment = Payment::create([
            'user_id' => $payerUser?->id,
            'submission_id' => $firstId,
            'submission_ids' => $submissionIds,
            'order_id' => $orderId,
            'transaction_id' => $data['transaction_id'] ?? null,
            'gateway' => 'belibayar',
            'payment_method' => 'qris',
            'type' => 'bulk_submission',
            'payer_name' => $payerName,
            'payer_email' => $payerEmail,
            'original_amount' => $pricingData['original_amount'] ?? $grossAmount,
            'discount_amount' => $pricingData['discount_amount'] ?? 0,
            'gross_amount' => $grossAmount,
            'journal_share' => $pricingData['journal_share'],
            'developer_gross_share' => $pricingData['developer_gross_share'],
            'mdr_amount' => $pricingData['mdr_amount'],
            'developer_net_share' => $pricingData['developer_net_share'],
            'transaction_status' => $data['status'] ?? 'pending',
            'payment_status' => 'pending',
            'qris_url' => $qrUrl,
            'qr_string' => $qrContent ?? $qrUrl,
            'expired_at' => $expiredAt,
            'raw_response' => $response,
        ]);

        foreach ($pricingData['items'] as $item) {
            $sub = $item['submission'];
            $pr = $item['pricing'];
            PaymentItem::create([
                'payment_id' => $payment->id,
                'submission_id' => $sub->id,
                'item_type' => 'publication',
                'item_name' => $pr['tier_name'] . ' - ' . ($sub->journal?->name ?? 'Jurnal CIB'),
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

    /**
     * --------------------------------------------------------------------------
     * 5. Status Inquiry & Cancel
     * --------------------------------------------------------------------------
     */

    public function checkStatus(Payment $payment): Payment
    {
        if ($payment->isPaid()) {
            $payment->ensureInvoiceNumber();
            return $payment;
        }

        try {
            $response = $this->getRequest('/payment/status/' . $payment->order_id);
            $data = $response['data'] ?? [];
            $status = strtolower($data['status'] ?? ($response['status'] ?? ''));

            $isPaid = in_array($status, ['paid', 'success', 'settlement', 'completed', 'berhasil']);
            $isExpired = in_array($status, ['expired', 'expire', 'kadaluwarsa']);
            $isFailed = in_array($status, ['cancelled', 'canceled', 'failed', 'rejected', 'gagal']);

            if ($isPaid) {
                $this->fulfillmentService->markAsPaid($payment, 'settlement', $response);
            } elseif ($isExpired) {
                $this->fulfillmentService->markAsExpired($payment, $response);
            } elseif ($isFailed) {
                $this->fulfillmentService->markAsFailed($payment, $status, $response);
            }

            // If transaction is still pending and QRIS was missing or dummy SVG, heal and populate
            if ($this->isDummyQr($payment->qris_url)) {
                $rawQr = $data['qr_code'] ?? ($data['qr_url'] ?? null);
                $qrContent = $data['qr_content'] ?? ($data['qr_string'] ?? null);
                $qrUrl = $this->resolveQrUrl($rawQr, $qrContent, $payment->order_id);

                if (!empty($qrUrl)) {
                    $payment->update([
                        'qris_url' => $qrUrl,
                        'qr_string' => $qrContent ?? $qrUrl,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Belibayar checkStatus failed for {$payment->order_id}: " . $e->getMessage());

            // If API failed or timed out, but local payment record has dummy/missing QR, at least self-heal the QR
            if ($this->isDummyQr($payment->qris_url)) {
                $qrUrl = $this->resolveQrUrl(null, $payment->qr_string, $payment->order_id);
                $payment->update([
                    'qris_url' => $qrUrl,
                    'qr_string' => $payment->qr_string ?? $qrUrl,
                ]);
            }
        }

        return $payment->fresh();
    }

    public function cancelPayment(Payment $payment): bool
    {
        try {
            $response = $this->postRequest('/payment/cancel', [
                'reference' => $payment->order_id,
                'reason' => 'Dibatalkan oleh pengguna / sistem',
            ]);

            $this->fulfillmentService->markAsFailed($payment, 'cancelled', $response);
            return true;
        } catch (\Exception $e) {
            Log::warning("Belibayar cancelPayment failed for {$payment->order_id}: " . $e->getMessage());
            return false;
        }
    }
}
