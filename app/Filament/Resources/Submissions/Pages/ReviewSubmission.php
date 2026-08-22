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
                ->color(fn() => $this->record->status === 'Rejected' ? 'warning' : 'primary')
                ->outlined(fn() => $this->record->status === 'Rejected')
                ->icon(fn() => $this->record->status === 'Rejected' ? 'heroicon-m-arrow-uturn-left' : 'heroicon-m-check-circle')
                ->size('sm')
                ->disabled(fn() => $this->record->review_status === 'processing' || empty($this->record->title))
                ->modalSubmitAction(
                    fn(Action $action) => $action
                        ->color($this->record->status === 'Rejected' ? 'warning' : 'primary')
                        ->label($this->record->status === 'Rejected' ? 'Yes, Cancel Rejection' : 'Yes, Approve')
                )
                ->modalCancelAction(
                    fn(Action $action) => $action
                        ->color('gray')
                        ->label('Cancel')
                )
                ->form(function () {
                    if ($this->record->status === 'Rejected') {
                        return [];
                    }
                    return [
                        \Filament\Forms\Components\Radio::make('has_doi')
                            ->label('Pilihan DOI')
                            ->options([
                                1 => 'Berikan DOI',
                                0 => 'Tanpa DOI',
                            ])
                            ->default(fn() => $this->record->want_doi ? 1 : 0)
                            ->required(),
                    ];
                })
                ->requiresConfirmation()
                ->modalHeading(fn() => $this->record->status === 'Rejected' ? 'Cancel Rejection' : 'Approve Submission')
                ->modalDescription(
                    fn() => $this->record->status === 'Rejected'
                    ? 'Are you sure you want to cancel this rejection?'
                    : 'Are you sure you want to approve this submission?'
                )
                ->action(function (array $data) {
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

                        $hasDoi = isset($data['has_doi']) ? (bool)$data['has_doi'] : false;

                        $updateData = [
                            'status' => 'Approved',
                            'approved_date' => now(),
                            'proof_of_payment' => null,
                            'has_doi' => $hasDoi,
                        ];
                        if ($this->record->review_status === 'failed') {
                            $updateData['review_status'] = 'N/A';
                        }
                        $this->record->update($updateData);

                        // Run OJS Submission in background
                        try {
                            \App\Services\OjsSubmissionService::submitInBackground($this->record);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning("OJS integration failed to dispatch background job for submission ID: {$this->record->id}. Error: {$e->getMessage()}");
                        }

                        Notification::make()
                            ->title('Submission Approved')
                            ->success()
                            ->send();
                    }

                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn() => $this->record->status !== 'Approved' && Auth::user()?->hasRole('super_admin')),
            Action::make('generate_doi')
                ->label('Buat DOI')
                ->icon('heroicon-m-plus-circle')
                ->color('primary')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading('Generate Repository Identifier (DOI Custom)')
                ->modalDescription('Apakah Anda yakin ingin membuat DOI/Repository Identifier untuk artikel ini? Tindakan ini akan memperbarui data di OJS dan katalog Repository.')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalIconColor('primary')
                ->modalSubmitAction(fn ($action) => $action->color('primary'))
                ->action(function () {
                    $this->record->update([
                        'has_doi' => true,
                    ]);

                    // Generate DOI immediately
                    $identifierService = new \App\Services\RepositoryIdentifierService();
                    $identifier = $identifierService->generate($this->record);
                    
                    $repoUrl = rtrim(env('REPO_URL', 'http://127.0.0.1:8001'), '/');
                    $redirectUrl = $repoUrl . '/' . $identifier;
                    $landingPage = "/article/submission-{$this->record->id}";
                    
                    $this->record->update([
                        'repository_identifier' => $identifier,
                        'repository_landing_page' => $landingPage,
                        'repository_redirect_url' => $redirectUrl,
                        'repository_identifier_status' => 'active',
                        'repository_identifier_generated_at' => now(),
                    ]);

                    // Run OJS Submission in background
                    try {
                        \App\Services\OjsSubmissionService::submitInBackground($this->record);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("OJS integration failed to dispatch background job for submission ID: {$this->record->id}. Error: {$e->getMessage()}");
                    }

                    Notification::make()
                        ->title('DOI berhasil dibuat dan disinkronkan')
                        ->success()
                        ->send();
                })
                ->visible(fn() => Auth::user()?->hasRole('super_admin') && $this->record->status === 'Approved' && !$this->record->has_doi),

            Action::make('resubmit_ojs')
                ->label('Resubmit to OJS')
                ->color('info')
                ->icon('heroicon-m-arrow-path')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading('Resubmit to OJS')
                ->modalDescription('Are you sure you want to resubmit this submission to OJS?')
                ->action(function () {
                    try {
                        \App\Services\OjsSubmissionService::submitInBackground($this->record);
                        Notification::make()
                            ->title('Resubmission dispatched in background')
                            ->info()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('OJS Resubmission Failed to Dispatch')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->disabled(fn() => $this->record->ojs_status === 'pending')
                ->visible(fn() => $this->record->status === 'Approved' && !in_array($this->record->ojs_status, ['submitted', 'published']) && Auth::user()?->hasRole('super_admin')),

            Action::make('sync_ojs')
                ->label('Sinkronkan OJS')
                ->color('info')
                ->icon('heroicon-m-arrow-path')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi Ulang ke OJS')
                ->modalDescription('Apakah Anda yakin ingin melakukan sinkronisasi ulang data (termasuk DOI jika ada) ke OJS?')
                ->action(function () {
                    try {
                        \App\Services\OjsSubmissionService::submitInBackground($this->record);
                        Notification::make()
                            ->title('Sinkronisasi ulang dikirim ke antrean latar belakang')
                            ->info()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal mengirim sinkronisasi')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn() => $this->record->status === 'Approved' && $this->record->ojs_status === 'submitted' && Auth::user()?->hasRole('super_admin')),
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
                    ->icon('heroicon-m-pencil-square')
                    ->disabled(fn() => $this->record->review_status === 'processing'),
                Action::make('request_review_again')
                    ->label('Minta Review Lagi')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->action(function () {
                        $this->record->processReviewInBackground();

                        Notification::make()
                            ->title('Proses Review Dimulai Kembali')
                            ->body('Review naskah Anda sedang diproses di latar belakang.')
                            ->info()
                            ->send();

                        $this->redirect(static::$resource::getUrl('view', ['record' => $this->record]));
                    })
                    ->visible(fn() => $this->record->review_status === 'failed' && $this->record->status !== 'Approved'),
                Action::make('Konfirmasi LOA ke Admin')
                    ->label('Konfirmasi LOA ke Admin')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn() => 'https://wa.me/' . (\App\Models\User::find(1)?->phone ?? '') . '?text=Halo%20Admin%20LOA%2C%20Saya%20ingin%20bertanya%20tentang%20pengajuan%20LOA%20saya%20dengan%20nomor%20registrasi%20' . $this->record->id)
                    ->openUrlInNewTab()
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi LOA ke Admin')
                    ->modalDescription('PENTING: Harap pastikan data naskah Anda (Judul, Abstrak, dan Penulis) sudah sesuai dan benar sebelum menghubungi Admin. Jika Anda menggunakan sistem ekstraksi otomatis, pastikan hasil ekstraksi di tabel sudah benar. Jika ada kesalahan, Anda dapat memperbaikinya terlebih dahulu melalui tombol Edit.')
                    ->modalSubmitActionLabel('Lanjutkan ke WhatsApp')
                    ->modalCancelActionLabel('Periksa Kembali'),
            ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
        ];
    }
}
