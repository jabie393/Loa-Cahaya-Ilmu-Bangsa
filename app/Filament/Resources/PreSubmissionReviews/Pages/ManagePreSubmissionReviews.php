<?php

namespace App\Filament\Resources\PreSubmissionReviews\Pages;

use App\Filament\Resources\PreSubmissionReviews\PreSubmissionReviewResource;
use App\Mail\PreSubmissionReviewMail;
use App\Models\PreSubmissionReview;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Mail;
use App\Services\QuotaService;
use Exception;

class ManagePreSubmissionReviews extends ManageRecords
{
    protected static string $resource = PreSubmissionReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Review Jurnal Baru')
                ->modalHeading('Kirim Jurnal untuk Review Pra-OJS')
                ->modalSubmitActionLabel('Request Review')
                ->successNotification(null)
                ->mutateFormDataUsing(function (array $data): array {
                    $quotaService = app(QuotaService::class);
                    $user = auth()->user();

                    if (!$quotaService->canRequestReview($user)) {
                        Notification::make()
                            ->title('Batas Review Tercapai')
                            ->body('Maaf, kuota review harian Anda telah habis. Silakan coba lagi besok atau hubungi admin untuk credits tambahan.')
                            ->danger()
                            ->send();

                        $this->halt();
                    }

                    $data['user_id'] = $user->id;
                    $data['status'] = 'processing';
                    return $data;
                })
                ->after(function (PreSubmissionReview $record) {
                    $record->processReview();
                }),
        ];
    }
}
