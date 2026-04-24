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
                ->modalSubmitActionLabel('Mulai Review AI')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['status'] = 'processing';
                    return $data;
                })
                ->after(function (PreSubmissionReview $record) {
                    try {
                        // resolve AI service through manager
                        $aiService = app('ai-review')->driver();
                        
                        // Perform Review
                        $results = $aiService->review($record);
                        
                        // Update Record
                        $record->update([
                            'title' => $results['detected_title'] ?? null,
                            'structure_review' => $results['structure_review'] ?? null,
                            'abstract_review' => $results['abstract_review'] ?? null,
                            'introduction_review' => $results['introduction_review'] ?? null,
                            'method_review' => $results['method_review'] ?? null,
                            'results_review' => $results['results_review'] ?? null,
                            'conclusion_review' => $results['conclusion_review'] ?? null,
                            'bibliography_review' => $results['bibliography_review'] ?? null,
                            'general_suggestions' => $results['general_suggestions'] ?? null,
                            'status' => 'reviewed',
                        ]);

                        // Send Email
                        Mail::to($record->email)->send(new PreSubmissionReviewMail($record));
                        
                        $record->update(['email_sent_at' => now()]);

                        Notification::make()
                            ->title('Review Selesai & Email Terkirim')
                            ->success()
                            ->send();

                    } catch (Exception $e) {
                        $record->update([
                            'status' => 'failed',
                            'error_message' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Gagal Memproses Review')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}
