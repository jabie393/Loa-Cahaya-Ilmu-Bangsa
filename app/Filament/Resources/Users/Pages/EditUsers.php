<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UsersResource;
use App\Services\QuotaService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUsers extends EditRecord
{
    protected static string $resource = UsersResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pastikan user ini punya record quota agar bisa disimpan lewat form
        app(QuotaService::class)->getQuota($this->getRecord());

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
