<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use App\Mail\SubmissionApproved;

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
                ->label(fn () => $this->record->status === 'Rejected' ? 'Cancel Rejection' : 'Approve Submission')
                ->color(fn () => $this->record->status === 'Rejected' ? 'warning' : 'success')
                ->outlined(fn () => $this->record->status === 'Rejected')
                ->icon(fn () => $this->record->status === 'Rejected' ? 'heroicon-o-arrow-uturn-left' : 'heroicon-o-check-circle')
                ->modalSubmitAction(
                    fn (Action $action) => $action
                        ->color($this->record->status === 'Rejected' ? 'warning' : 'success')
                        ->label($this->record->status === 'Rejected' ? 'Yes, Cancel Rejection' : 'Yes, Approve Submission')
                )
                ->modalCancelAction(
                    fn (Action $action) => $action
                        ->color('danger')
                        ->label('No, Cancel')
                )
                ->requiresConfirmation()
                ->modalHeading(fn () => $this->record->status === 'Rejected' ? 'Cancel Rejection' : 'Approve Submission')
                ->modalDescription(fn () => $this->record->status === 'Rejected' 
                    ? 'Are you sure you want to cancel this rejection and set the status back to Pending?' 
                    : 'Are you sure you want to approve this submission?'
                )
                ->action(function () {
                    if ($this->record->status === 'Rejected') {
                        $this->record->update([
                            'status' => 'Pending',
                            'rejection_reason' => null,
                            'rejected_date' => null,
                        ]);

                        Notification::make()
                            ->title('Rejection Canceled')
                            ->success()
                            ->send();
                    } else {
                        if ($this->record->proof_of_payment) {
                            Storage::disk('public')->delete($this->record->proof_of_payment);
                        }

                        $this->record->update([
                            'status' => 'Approved',
                            'approved_date' => now(),
                            'proof_of_payment' => null,
                        ]);

                        Mail::to($this->record->email)->send(new SubmissionApproved($this->record));

                        Notification::make()
                            ->title('Submission Approved')
                            ->success()
                            ->send();
                    }

                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () => $this->record->status !== 'Approved' && Auth::user()?->hasRole('super_admin')),
            Action::make('reject')
                ->label('Reject Submission')
                ->color('danger')
                ->outlined(false)
                ->icon('heroicon-o-x-mark')
                ->form([
                    Section::make('')
                        ->schema([
                            Textarea::make('rejection_reason')
                                ->label('Alasan Penolakan')
                                ->required()
                                ->rows(5),
                        ]),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'Rejected',
                        'rejection_reason' => $data['rejection_reason'],
                        'rejected_date' => now(),
                    ]);

                    Notification::make()
                        ->title('Submission Rejected')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () => $this->record->status === 'Pending' && Auth::user()?->hasRole('super_admin')),
            EditAction::make(),
            Action::make('Tanya admin')
                ->label('Tanya Admin')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary')
                ->url(fn () => 'https://wa.me/' . (\App\Models\User::find(1)?->phone ?? '') . '?text=Halo%20Admin%20LOA%2C%20Saya%20ingin%20bertanya%20tentang%20pengajuan%20LOA%20saya%20dengan%20nomor%20registrasi%20' . $this->record->id)
                ->openUrlInNewTab(),
        ];
    }
}
