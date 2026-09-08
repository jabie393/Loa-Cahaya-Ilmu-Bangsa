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
                ->visible(function () {
                    if (!Auth::user()?->hasRole('super_admin') || $this->record->status !== 'Approved' || in_array($this->record->ojs_status, ['submitted', 'published'])) {
                        return false;
                    }
                    if (!empty($this->record->publication_link)) {
                        $linkHost = parse_url($this->record->publication_link, PHP_URL_HOST);
                        $targetHost = parse_url($this->record->journal?->ojs_base_url ?: config('ojs.base_url'), PHP_URL_HOST);
                        if ($linkHost && $targetHost && strtolower($linkHost) !== strtolower($targetHost)) {
                            return false;
                        }
                    }
                    return true;
                }),

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
                ->visible(function () {
                    if (!Auth::user()?->hasRole('super_admin') || $this->record->status !== 'Approved' || $this->record->ojs_status !== 'submitted') {
                        return false;
                    }
                    if (!empty($this->record->publication_link)) {
                        $linkHost = parse_url($this->record->publication_link, PHP_URL_HOST);
                        $targetHost = parse_url($this->record->journal?->ojs_base_url ?: config('ojs.base_url'), PHP_URL_HOST);
                        if ($linkHost && $targetHost && strtolower($linkHost) !== strtolower($targetHost)) {
                            return false;
                        }
                    }
                    return true;
                }),

            Action::make('replace_pdf')
                ->label('Ganti PDF')
                ->color('warning')
                ->icon('heroicon-m-arrow-up-tray')
                ->size('sm')
                ->modalHeading('Ganti File PDF Naskah')
                ->modalDescription('Unggah file PDF artikel yang telah diperbarui. Sistem akan memverifikasi bahwa jumlah penulis pada file baru sama dengan naskah awal. Biaya layanan penggantian file PDF adalah Rp 25.000.')
                ->modalSubmitActionLabel('Verifikasi & Lanjut Bayar')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('new_manuscript_file')
                        ->label('File PDF Naskah Baru')
                        ->acceptedFileTypes(['application/pdf'])
                        ->disk('public')
                        ->directory('temp_replace_pdf')
                        ->required()
                        ->helperText('Format PDF saja. Pastikan jumlah dan daftar penulis sama dengan naskah awal.'),
                ])
                ->action(function (array $data) {
                    $uploadedFilePath = $data['new_manuscript_file'] ?? null;
                    if (!$uploadedFilePath || !Storage::disk('public')->exists($uploadedFilePath)) {
                        Notification::make()
                            ->title('Gagal Mengunggah File')
                            ->body('File PDF tidak ditemukan.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $pricingService = app(\App\Services\SubmissionPricingService::class);
                    $currentAuthorCount = $pricingService->getAuthorCount($this->record);

                    // Extract & verify author count from new PDF
                    try {
                        $reviewService = app(\App\Services\GeminiReviewService::class);
                        $extracted = $reviewService->extractMetadataFromFile($uploadedFilePath, $this->record->isExternal());
                        $newAuthors = $extracted['detected_authors'] ?? [];
                        $newAuthorCount = count(array_filter($newAuthors, fn($a) => !empty(trim($a['name'] ?? ''))));
                        if ($newAuthorCount === 0) {
                            $newAuthorCount = 1;
                        }
                    } catch (\Throwable $e) {
                        $newAuthorCount = $currentAuthorCount;
                    }

                    if ($newAuthorCount !== $currentAuthorCount) {
                        Storage::disk('public')->delete($uploadedFilePath);
                        Notification::make()
                            ->title('Perubahan Jumlah Penulis Ditolak')
                            ->body("Jumlah penulis pada file PDF baru terdeteksi {$newAuthorCount} orang, sedangkan naskah awal memiliki {$currentAuthorCount} orang. Fitur Ganti PDF tidak mengizinkan penambahan atau pengurangan jumlah penulis.")
                            ->danger()
                            ->persistent()
                            ->send();
                        return;
                    }

                    // Charge QRIS for Replace PDF
                    $qrisService = app(\App\Services\MidtransQrisService::class);
                    try {
                        $qrisService->getOrCreateReplacePdfPayment($this->record, $uploadedFilePath);
                        $this->redirect(route('submissions.payment.replace-pdf', $this->record->id));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Membuat Tagihan Pembayaran')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn() => $this->record->ojs_status === 'submitted'),
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
                Action::make('bayar')
                    ->label('Bayar QRIS')
                    ->icon('heroicon-m-credit-card')
                    ->color('success')
                    ->url(fn(): string => static::$resource::getUrl('payment', ['record' => $this->record]))
                    ->visible(fn(): bool => $this->record->payment_status !== 'paid' && !in_array($this->record->review_status, ['processing', 'failed'])),
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
