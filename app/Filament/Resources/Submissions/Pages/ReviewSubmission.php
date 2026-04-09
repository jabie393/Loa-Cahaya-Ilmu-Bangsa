<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Callout;
use Illuminate\Support\Facades\Storage;

class ReviewSubmission extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submissions.pages.review-submission';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve Submission')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function () {
                    if ($this->record->proof_of_payment) {
                        Storage::disk('public')->delete($this->record->proof_of_payment);
                    }

                    $this->record->update([
                        'status' => 'Approved',
                        'approved_date' => now(),
                        'proof_of_payment' => null,
                    ]);

                    Notification::make()
                        ->title('Submission Approved')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () => $this->record->status !== 'Approved' && Auth::user()?->hasRole('super_admin')),
            EditAction::make(),
            Action::make('Tanya admin')
                ->label('Tanya Admin')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary')
                ->url(fn () => 'https://wa.me/' . (\App\Models\User::find(1)?->phone ?? '') . '?text=Halo%20Admin%20LOA%2C%20Saya%20ingin%20bertanya%20tentang%20pengajuan%20LOA%20saya%20dengan%20nomor%20registrasi%20' . $this->record->id)
                ->openUrlInNewTab()
        ];
    }
}
