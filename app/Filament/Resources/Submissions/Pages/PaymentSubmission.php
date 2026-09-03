<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\Payment;
use App\Models\Submission;
use App\Services\MidtransQrisService;
use App\Services\SubmissionPricingService;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PaymentSubmission extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submissions.pages.payment-submission';

    protected static ?string $title = 'Pembayaran Naskah';

    public ?array $pricing = null;
    public ?Payment $payment = null;
    public bool $isExtracting = false;
    public ?string $errorMessage = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // Authorization check
        $user = Auth::user();
        if ($this->record->user_id !== $user->id && !$user->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman pembayaran naskah ini.');
        }

        // If submission is already approved, publication fee is already settled
        if ($this->record->status === 'Approved') {
            if (!$this->record->has_doi) {
                $this->redirect(SubmissionResource::getUrl('payment.doi', ['record' => $this->record]));
                return;
            }
            $this->redirect(SubmissionResource::getUrl('view', ['record' => $this->record]));
            return;
        }

        $this->loadPaymentData();
    }

    public function loadPaymentData(): void
    {
        $this->record->refresh();
        $this->isExtracting = ($this->record->review_status === 'processing');

        if (!$this->isExtracting) {
            $pricingService = app(SubmissionPricingService::class);
            $qrisService = app(MidtransQrisService::class);

            $this->pricing = $pricingService->calculate($this->record);

            try {
                $this->payment = $qrisService->getOrCreatePayment($this->record);
            } catch (\Exception $e) {
                $this->errorMessage = $e->getMessage();
            }
        }
    }

    public function getBreadcrumbs(): array
    {
        return [
            SubmissionResource::getUrl('index') => 'Submissions',
            SubmissionResource::getUrl('view', ['record' => $this->record]) => (string) $this->record->id,
            '' => 'Payment',
        ];
    }
}
