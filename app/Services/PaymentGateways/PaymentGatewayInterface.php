<?php

namespace App\Services\PaymentGateways;

use App\Models\Payment;
use App\Models\Submission;

interface PaymentGatewayInterface
{
    /**
     * Get the gateway identifier (e.g. 'midtrans' or 'belibayar').
     */
    public function getGatewayName(): string;

    /**
     * Single submission payment.
     */
    public function getOrCreatePayment(Submission $submission): Payment;
    public function forceNewPayment(Submission $submission): Payment;
    public function chargeQris(Submission $submission): Payment;

    /**
     * DOI Add-on payment.
     */
    public function getOrCreateDoiPayment(Submission $submission): Payment;
    public function forceNewDoiPayment(Submission $submission): Payment;
    public function chargeDoiQris(Submission $submission): Payment;
    public function chargeDoiAddonQris(Submission $submission): Payment;

    /**
     * Replace PDF service payment.
     */
    public function getOrCreateReplacePdfPayment(Submission $submission, ?string $tempFilePath = null): Payment;
    public function forceNewReplacePdfPayment(Submission $submission, string $tempFilePath = ''): Payment;
    public function chargeReplacePdfQris(Submission $submission, string $tempFilePath = ''): Payment;

    /**
     * Bulk submission payment.
     */
    public function getOrCreateBulkPayment($submissions): Payment;
    public function forceNewBulkPayment($submissions): Payment;
    public function chargeBulkQris($submissions): Payment;

    /**
     * Check transaction status with gateway API.
     */
    public function checkStatus(Payment $payment): Payment;

    /**
     * Cancel pending transaction.
     */
    public function cancelPayment(Payment $payment): bool;
}
