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
                    $this->record->update([
                        'status' => 'Approved',
                        'approved_date' => now(),
                    ]);

                    Notification::make()
                        ->title('Submission Approved')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () => $this->record->status !== 'Approved' && Auth::user()?->hasRole('super_admin')),
            EditAction::make(),
            
        ];
    }
}
