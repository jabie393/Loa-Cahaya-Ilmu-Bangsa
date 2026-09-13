<?php

namespace App\Filament\Resources\SubmissionPricings\Pages;

use App\Filament\Resources\SubmissionPricings\SubmissionPricingResource;
use Database\Seeders\SubmissionPricingSeeder;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSubmissionPricings extends ListRecords
{
    protected static string $resource = SubmissionPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_default')
                ->label('Reset ke Default')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Reset Seluruh Pricelist ke Default?')
                ->modalDescription('Tindakan ini akan mengembalikan seluruh tarif naskah, layanan, dan pembagian developer ke nilai standar bawaan.')
                ->action(function () {
                    (new SubmissionPricingSeeder())->run();

                    Notification::make()
                        ->title('Pricelist Berhasil Direset')
                        ->body('Seluruh tarif dan bagi hasil developer telah dikembalikan ke standar awal.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
