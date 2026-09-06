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

    protected bool $manuscriptFileChanged = false;

    protected function getLoadingProtectionAttributes(): array
    {
        return [
            'wire:loading.attr' => 'disabled',
            'wire:loading.class' => 'opacity-50 cursor-wait pointer-events-none animate-pulse',
        ];
    }

    protected function getHeaderActions(): array
    {
        if (Auth::user()?->hasRole('super_admin')) {
            return [
                DeleteAction::make()
                    ->extraAttributes($this->getLoadingProtectionAttributes()),
            ];
        }

        return [];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->extraAttributes($this->getLoadingProtectionAttributes());
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->extraAttributes($this->getLoadingProtectionAttributes());
    }

    protected function getFormActions(): array
    {
        $actions = [
            $this->getSaveFormAction(),
        ];

        // Tombol cepat ke Pembayaran QRIS sejajar dengan tombol Save Changes (Simpan & Langsung Buka Pembayaran)
        if ($this->record->status !== 'Approved' && $this->record->payment_status !== 'paid') {
            $actions[] = Action::make('bayar_qris')
                ->label('Proceed to Payment')
                ->icon('heroicon-o-credit-card')
                ->color('primary')
                ->extraAttributes($this->getLoadingProtectionAttributes())
                ->action(function () {
                    $this->save(shouldRedirect: false);
                    return redirect()->to(SubmissionResource::getUrl('payment', ['record' => $this->record]));
                });
        }

        $actions[] = $this->getCancelFormAction();

        return $actions;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $originalFile = $this->record->manuscript_file;
        $newFile = $data['manuscript_file'] ?? null;

        // Tandai jika ada berkas naskah baru yang diunggah atau diubah
        if (!empty($newFile) && $newFile !== $originalFile) {
            $this->manuscriptFileChanged = true;

            $disk = 'public';
            $extension = pathinfo($newFile, PATHINFO_EXTENSION);
            $targetPath = "manuscripts/file-{$this->record->id}" . ($extension ? ".{$extension}" : "");

            if ($newFile !== $targetPath && \Illuminate\Support\Facades\Storage::disk($disk)->exists($newFile)) {
                if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($targetPath)) {
                    \Illuminate\Support\Facades\Storage::disk($disk)->delete($targetPath);
                }
                \Illuminate\Support\Facades\Storage::disk($disk)->move($newFile, $targetPath);
                $data['manuscript_file'] = $targetPath;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $fileChanged = $this->manuscriptFileChanged;
        $doiChanged = $this->record->wasChanged('want_doi');

        // Pastikan model di-refresh agar data terbaru dari database sinkron
        $this->record->refresh();

        // Jika file PDF diunggah ulang, jalankan proses ekstraksi di latar belakang
        if ($fileChanged) {
            $this->record->update(['review_status' => 'processing', 'review_error_message' => null]);
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
        return Notification::make()
            ->success()
            ->title('Perubahan Disimpan')
            ->body($this->manuscriptFileChanged ? 'File naskah baru sedang diekstraksi di latar belakang.' : 'Data pengajuan berhasil diperbarui.');
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}

