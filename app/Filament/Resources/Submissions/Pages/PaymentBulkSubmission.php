<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\Payment;
use App\Models\Submission;
use App\Services\MidtransQrisService;
use App\Services\SubmissionPricingService;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class PaymentBulkSubmission extends Page
{
    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submissions.pages.payment-bulk-submission';

    public Collection $submissions;
    public ?Payment $payment = null;
    public array $pricing = [];
    public array $itemsPricing = [];
    public array $selectedIds = [];

    public function mount(MidtransQrisService $qrisService, SubmissionPricingService $pricingService): void
    {
        $idsParam = request()->query('records');

        if (is_array($idsParam)) {
            $this->selectedIds = array_filter(array_map('intval', $idsParam));
        } elseif (is_string($idsParam)) {
            $this->selectedIds = array_filter(array_map('intval', explode(',', $idsParam)));
        }

        if (empty($this->selectedIds)) {
            $this->redirect(SubmissionResource::getUrl('index'));
            return;
        }

        // Get submissions that are unpaid
        $this->submissions = Submission::with(['journal', 'user'])
            ->whereIn('id', $this->selectedIds)
            ->where('payment_status', '<>', 'paid')
            ->get();

        // Jika hanya ada 1 naskah yang belum dibayar, redirect ke pembayaran single
        if ($this->submissions->count() === 1) {
            $this->redirect(SubmissionResource::getUrl('payment', ['record' => $this->submissions->first()]));
            return;
        }

        if ($this->submissions->isEmpty()) {
            // Check if they are all already paid
            $allSubmissions = Submission::with(['journal', 'user'])
                ->whereIn('id', $this->selectedIds)
                ->get();

            if ($allSubmissions->count() === 1) {
                $this->redirect(SubmissionResource::getUrl('payment', ['record' => $allSubmissions->first()]));
                return;
            }

            $this->submissions = $allSubmissions;
        }

        $bulkPricing = $pricingService->calculateBulk($this->submissions);
        $this->pricing = $bulkPricing;
        $this->itemsPricing = $bulkPricing['items'];

        // Get or charge QRIS
        $this->payment = $qrisService->getOrCreateBulkPayment($this->submissions);

        // If payment is pending, query Midtrans once synchronously to catch up if paid in simulator/tab
        if ($this->payment && !$this->payment->isPaid()) {
            $this->payment = $qrisService->checkStatusFromMidtrans($this->payment);
        }
    }

    public function getTitle(): string
    {
        return 'Pembayaran Kolektif Naskah (Bulk QRIS)';
    }

    public function getBreadcrumbs(): array
    {
        return [
            SubmissionResource::getUrl('index') => 'Submissions',
            '#' => 'Pembayaran Kolektif (' . count($this->selectedIds) . ' Naskah)',
        ];
    }
}
