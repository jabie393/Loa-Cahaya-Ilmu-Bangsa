<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\Submission;
use App\Services\PaymentGateways\PaymentGatewayManager;
use App\Services\SubmissionPricingService;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PaymentReplacePdfSubmission extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submissions.pages.payment-replace-pdf';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $currentUser = Auth::user();
        if ($this->record->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'ryu_dev'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function getViewData(): array
    {
        $pricingService = app(SubmissionPricingService::class);
        $qrisService = app(PaymentGatewayManager::class);

        $user = $this->record->user ?? Auth::user();
        $pricing = $pricingService->calculateReplacePdf($user);
        $payment = null;
        $errorMessage = null;

        try {
            $payment = $qrisService->getOrCreateReplacePdfPayment($this->record);
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
        return 'Pembayaran Layanan Ganti PDF';
    }

    public function getBreadcrumbs(): array
    {
        return [
            SubmissionResource::getUrl('index') => 'Submissions',
            SubmissionResource::getUrl('view', ['record' => $this->record]) => (string) $this->record->id,
            '' => 'Ganti PDF Naskah',
        ];
    }
}
