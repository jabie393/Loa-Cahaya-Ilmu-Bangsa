<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use App\Mail\SubmissionApproved;
use App\Mail\SubmissionRejected;

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
                ->label(fn() => $this->record->status === 'Rejected' ? 'Cancel Rejection' : 'Approve')
                ->color(fn() => $this->record->status === 'Rejected' ? 'warning' : 'success')
                ->outlined(fn() => $this->record->status === 'Rejected')
                ->icon(fn() => $this->record->status === 'Rejected' ? 'heroicon-m-arrow-uturn-left' : 'heroicon-m-check-circle')
                ->size('sm')
                ->modalSubmitAction(
                    fn(Action $action) => $action
                        ->color($this->record->status === 'Rejected' ? 'warning' : 'success')
                        ->label($this->record->status === 'Rejected' ? 'Yes, Cancel Rejection' : 'Yes, Approve')
                )
                ->modalCancelAction(
                    fn(Action $action) => $action
                        ->color('gray')
                        ->label('Cancel')
                )
                ->requiresConfirmation()
                ->modalHeading(fn() => $this->record->status === 'Rejected' ? 'Cancel Rejection' : 'Approve Submission')
                ->modalDescription(
                    fn() => $this->record->status === 'Rejected'
                    ? 'Are you sure you want to cancel this rejection?'
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

                        // Run OJS Submission
                        try {
                            app(\App\Services\OjsSubmissionService::class)->submit($this->record);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning("OJS integration failed during single approval of submission ID: {$this->record->id}. Error: {$e->getMessage()}");
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
                ->visible(fn() => $this->record->status !== 'Approved' && Auth::user()?->hasRole('super_admin')),
            Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->outlined(false)
                ->icon('heroicon-m-x-mark')
                ->size('sm')
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

                    Mail::to($this->record->email)->send(new SubmissionRejected($this->record));

                    Notification::make()
                        ->title('Submission Rejected')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn() => $this->record->status === 'Pending' && Auth::user()?->hasRole('super_admin')),
            ActionGroup::make([
                EditAction::make()
                    ->label(fn() => $this->record->status === 'Rejected' ? 'Revise Submission' : 'Edit Submission')
                    ->icon('heroicon-m-pencil-square'),
                Action::make('Konfirmasi LOA ke Admin')
                    ->label('Konfirmasi LOA ke Admin')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn() => 'https://wa.me/' . (\App\Models\User::find(1)?->phone ?? '') . '?text=Halo%20Admin%20LOA%2C%20Saya%20ingin%20bertanya%20tentang%20pengajuan%20LOA%20saya%20dengan%20nomor%20registrasi%20' . $this->record->id)
                    ->openUrlInNewTab(),
            ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
        ];
    }
}
