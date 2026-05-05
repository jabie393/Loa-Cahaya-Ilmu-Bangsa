<?php

namespace App\Filament\Resources\PlagiarismChecks\Pages;

use App\Filament\Resources\PlagiarismChecks\PlagiarismCheckResource;
use App\Services\PlagiarismQuotaService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PlagiarismCheckResultMail;

class ManagePlagiarismChecks extends ManageRecords
{
    protected static string $resource = PlagiarismCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Cek Plagiasi Baru')
                ->icon('heroicon-o-plus')
                ->modalHeading('Mulai Cek Plagiasi')
                ->modalSubmitActionLabel('Jalankan Analisis')
                ->successNotification(null)
                ->before(function (Actions\CreateAction $action) {
                    $user = Auth::user();
                    $quotaService = app(PlagiarismQuotaService::class);

                    if (!$quotaService->canCheck($user)) {
                        Notification::make()
                            ->title('Kuota Habis')
                            ->body('Batas harian pengecekan plagiasi Anda telah tercapai. Silakan coba lagi besok atau hubungi admin untuk kuota tambahan.')
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    return $data;
                })
                ->after(function ($record) {
                    // Start processing
                    $record->processCheck();
                }),
        ];
    }
}
