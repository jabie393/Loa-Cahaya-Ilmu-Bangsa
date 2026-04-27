<?php

namespace App\Filament\Resources\PreSubmissionReviews\Pages;

use App\Filament\Resources\PreSubmissionReviews\PreSubmissionReviewResource;
use App\Mail\PreSubmissionReviewMail;
use App\Models\PreSubmissionReview;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Mail;
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
                    $data['user_id'] = auth()->id();
                    $data['status'] = 'processing';
                    return $data;
                })
                ->after(function (PreSubmissionReview $record) {
                    $record->processReview();
                }),
        ];
    }
}
