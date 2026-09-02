<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\Submission;
use App\Services\MidtransQrisService;
use App\Services\SubmissionPricingService;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PaymentDoiSubmission extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submissions.pages.payment-doi';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $currentUser = Auth::user();
        if ($this->record->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function getViewData(): array
    {
        $pricingService = app(SubmissionPricingService::class);
        $qrisService = app(MidtransQrisService::class);

        $pricing = $pricingService->calculateDoiAddon();
        $payment = null;
        $errorMessage = null;

        try {
            $payment = $qrisService->getOrCreateDoiPayment($this->record);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        return [
            'record' => $this->record,
            'pricing' => $pricing,
            'payment' => $payment,
            'errorMessage' => $errorMessage,
        ];
    }

    public function getTitle(): string
    {
        return 'Pembayaran Tambah DOI';
    }

    public function getBreadcrumbs(): array
    {
        return [
            SubmissionResource::getUrl('index') => 'Submissions',
            SubmissionResource::getUrl('view', ['record' => $this->record]) => (string) $this->record->id,
            '' => 'Tambah DOI',
        ];
    }
}
