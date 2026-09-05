<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSubmission extends EditRecord
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        if (Auth::user()?->hasRole('super_admin')) {
            return [
                DeleteAction::make(),
            ];
        }

        return [];
    }

    protected function getFormActions(): array
    {
        $actions = [
            $this->getSaveFormAction(),
        ];

        // Tombol cepat ke Pembayaran QRIS sejajar dengan tombol Save Changes (Simpan & Langsung Buka Pembayaran)
        if ($this->record->status !== 'Approved' && $this->record->payment_status !== 'paid') {
            $actions[] = Action::make('bayar_qris')
                ->label('Lanjut ke Pembayaran QRIS')
                ->icon('heroicon-o-credit-card')
                ->color('primary')
                ->action(function () {
                    $this->save(shouldRedirect: false);
                    return redirect()->to(SubmissionResource::getUrl('payment', ['record' => $this->record]));
                });
        }

        $actions[] = $this->getCancelFormAction();

        return $actions;
    }

    protected function afterSave(): void
    {
        $fileChanged = $this->record->wasChanged('manuscript_file');
        $doiChanged = $this->record->wasChanged('want_doi');

        // Jika file PDF diunggah ulang:
        if ($fileChanged) {
            $this->record->update(['review_status' => 'processing']);
            $this->record->processReviewInBackground();
        }

        // Jika opsi DOI atau file PDF diubah, expire transaksi pending agar QRIS baru menyesuaikan tarif terbaru
        if ($fileChanged || $doiChanged) {
            $this->record->payments()
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'expired',
                    'transaction_status' => 'expire',
                ]);
        }
    }

    protected function getSavedNotification(): ?Notification
    {
        $fileChanged = $this->record->wasChanged('manuscript_file');

        return Notification::make()
            ->success()
            ->title('Perubahan Disimpan')
            ->body($fileChanged ? 'File naskah baru sedang diekstraksi di latar belakang.' : 'Data pengajuan berhasil diperbarui.');
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
