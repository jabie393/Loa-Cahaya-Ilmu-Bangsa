<?php

namespace App\Services\PaymentGateways;

use App\Models\Payment;
use App\Models\Submission;
use App\Services\MidtransQrisService;
use Illuminate\Support\Facades\Cache;

class PaymentGatewayManager
{
    public const GATEWAY_MIDTRANS = 'midtrans';
    public const GATEWAY_BELIBAYAR = 'belibayar';

    protected MidtransQrisService $midtrans;
    protected BelibayarQrisService $belibayar;
    protected PaymentFulfillmentService $fulfillmentService;

    public function __construct(
        MidtransQrisService $midtrans,
        BelibayarQrisService $belibayar,
        PaymentFulfillmentService $fulfillmentService
    ) {
        $this->midtrans = $midtrans;
        $this->belibayar = $belibayar;
        $this->fulfillmentService = $fulfillmentService;
    }

    /**
     * Get the currently active payment gateway name.
     */
    public function getActiveGatewayName(): string
    {
        $cached = Cache::get('active_payment_gateway');
        if (!empty($cached) && in_array($cached, [self::GATEWAY_MIDTRANS, self::GATEWAY_BELIBAYAR], true)) {
            return $cached;
        }

        $config = config('services.payment_gateway');
        if (!empty($config) && in_array($config, [self::GATEWAY_MIDTRANS, self::GATEWAY_BELIBAYAR], true)) {
            return $config;
        }

        return self::GATEWAY_MIDTRANS;
    }

    /**
     * Switch the active payment gateway dynamically.
     */
    public function setActiveGateway(string $gateway): void
    {
        if (!in_array($gateway, [self::GATEWAY_MIDTRANS, self::GATEWAY_BELIBAYAR], true)) {
            throw new \InvalidArgumentException("Unsupported payment gateway: {$gateway}");
        }

        Cache::forever('active_payment_gateway', $gateway);
    }

    /**
     * Resolve a gateway driver instance.
     */
    public function driver(?string $driver = null): PaymentGatewayInterface
    {
        $name = $driver ?: $this->getActiveGatewayName();

        return match ($name) {
            self::GATEWAY_BELIBAYAR => $this->belibayar,
            default => $this->midtrans,
        };
    }

    /**
     * Resolve the gateway that originally processed this payment.
     */
    public function forPayment(Payment $payment): PaymentGatewayInterface
    {
        $gatewayName = $payment->gateway ?: self::GATEWAY_MIDTRANS;
        return $this->driver($gatewayName);
    }

    /**
     * Get the shared fulfillment service.
     */
    public function fulfillment(): PaymentFulfillmentService
    {
        return $this->fulfillmentService;
    }

    /**
     * Cancel and mark a payment as expired in database to avoid double payment.
     */
    public function cancelAndExpirePayment(Payment $payment): void
    {
        try {
            $this->cancelPayment($payment);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Cancel payment API error during expiration: " . $e->getMessage());
        }

        $payment->update([
            'payment_status' => 'expired',
            'transaction_status' => 'expire',
        ]);
    }

    // -------------------------------------------------------------------------
    // Proxy Methods for Seamless Replacement of MidtransQrisService in UI Pages
    // -------------------------------------------------------------------------

    public function getOrCreatePayment(Submission $submission): Payment
    {
        $activeGateway = $this->getActiveGatewayName();

        // Check if already paid
        $paidPayment = $submission->payments()
            ->where('payment_status', 'paid')
            ->where('type', 'submission')
            ->first();

        if ($paidPayment) {
            $paidPayment->ensureInvoiceNumber();
            return $paidPayment;
        }

        // Check latest pending payment for gateway mismatch
        $latestPayment = $submission->payments()
            ->where('type', 'submission')
            ->latest()
            ->first();

        if ($latestPayment && $latestPayment->payment_status === 'pending') {
            $paymentGateway = $latestPayment->gateway ?: self::GATEWAY_MIDTRANS;
            if ($paymentGateway !== $activeGateway) {
                // Gateway changed! Cancel & expire old payment to prevent double payment
                $this->cancelAndExpirePayment($latestPayment);
                return $this->driver()->chargeQris($submission);
            }
        }

        return $this->driver()->getOrCreatePayment($submission);
    }

    public function forceNewPayment(Submission $submission): Payment
    {
        return $this->driver()->forceNewPayment($submission);
    }

    public function chargeQris(Submission $submission): Payment
    {
        return $this->driver()->chargeQris($submission);
    }

    public function getOrCreateDoiPayment(Submission $submission): Payment
    {
        $activeGateway = $this->getActiveGatewayName();

        $paidDoi = $submission->payments()
            ->where('type', 'doi_addon')
            ->where('payment_status', 'paid')
            ->first();

        if ($paidDoi) {
            $paidDoi->ensureInvoiceNumber();
            return $paidDoi;
        }

        $latestDoi = $submission->payments()
            ->where('type', 'doi_addon')
            ->latest()
            ->first();

        if ($latestDoi && $latestDoi->payment_status === 'pending') {
            $paymentGateway = $latestDoi->gateway ?: self::GATEWAY_MIDTRANS;
            if ($paymentGateway !== $activeGateway) {
                $this->cancelAndExpirePayment($latestDoi);
                return $this->driver()->chargeDoiAddonQris($submission);
            }
        }

        return $this->driver()->getOrCreateDoiPayment($submission);
    }

    public function forceNewDoiPayment(Submission $submission): Payment
    {
        return $this->driver()->forceNewDoiPayment($submission);
    }

    public function chargeDoiQris(Submission $submission): Payment
    {
        return $this->driver()->chargeDoiQris($submission);
    }

    public function chargeDoiAddonQris(Submission $submission): Payment
    {
        return $this->driver()->chargeDoiAddonQris($submission);
    }

    public function getOrCreateReplacePdfPayment(Submission $submission, ?string $tempFilePath = null): Payment
    {
        $activeGateway = $this->getActiveGatewayName();

        $latest = $submission->payments()
            ->where('type', 'replace_pdf')
            ->latest()
            ->first();

        if ($latest && $latest->payment_status === 'pending') {
            $paymentGateway = $latest->gateway ?: self::GATEWAY_MIDTRANS;
            if ($paymentGateway !== $activeGateway) {
                $this->cancelAndExpirePayment($latest);
                return $this->driver()->chargeReplacePdfQris($submission, $tempFilePath ?: '');
            }
        }

        return $this->driver()->getOrCreateReplacePdfPayment($submission, $tempFilePath);
    }

    public function forceNewReplacePdfPayment(Submission $submission, string $tempFilePath = ''): Payment
    {
        return $this->driver()->forceNewReplacePdfPayment($submission, $tempFilePath);
    }

    public function chargeReplacePdfQris(Submission $submission, string $tempFilePath = ''): Payment
    {
        return $this->driver()->chargeReplacePdfQris($submission, $tempFilePath);
    }

    public function getOrCreateBulkPayment($submissions): Payment
    {
        $activeGateway = $this->getActiveGatewayName();
        $submissionIds = $submissions->pluck('id')->sort()->values()->toArray();

        $pendingPayment = Payment::where('type', 'bulk_submission')
            ->where('payment_status', 'pending')
            ->get()
            ->first(function ($p) use ($submissionIds) {
                $ids = is_array($p->submission_ids) ? $p->submission_ids : [];
                sort($ids);
                return $ids === $submissionIds;
            });

        if ($pendingPayment) {
            $paymentGateway = $pendingPayment->gateway ?: self::GATEWAY_MIDTRANS;
            if ($paymentGateway !== $activeGateway) {
                $this->cancelAndExpirePayment($pendingPayment);
                return $this->driver()->chargeBulkQris($submissions);
            }
        }

        return $this->driver()->getOrCreateBulkPayment($submissions);
    }

    public function forceNewBulkPayment($submissions): Payment
    {
        return $this->driver()->forceNewBulkPayment($submissions);
    }

    public function chargeBulkQris($submissions): Payment
    {
        return $this->driver()->chargeBulkQris($submissions);
    }

    /**
     * Check status using the specific gateway recorded on the payment record.
     */
    public function checkStatus(Payment $payment): Payment
    {
        return $this->forPayment($payment)->checkStatus($payment);
    }

    /**
     * Backward-compatible alias for checkStatus.
     */
    public function checkStatusFromMidtrans(Payment $payment): Payment
    {
        return $this->checkStatus($payment);
    }

    public function cancelPayment(Payment $payment): bool
    {
        return $this->forPayment($payment)->cancelPayment($payment);
    }

    public function activateDoiForSubmission(Submission $submission): void
    {
        $this->fulfillmentService->activateDoiForSubmission($submission);
    }

    public function applyReplacePdfForSubmission(Submission $submission, Payment $payment): void
    {
        $this->fulfillmentService->applyReplacePdfForSubmission($submission, $payment);
    }

    /**
     * Dynamically call methods on the active driver.
     */
    public function __call(string $method, array $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
