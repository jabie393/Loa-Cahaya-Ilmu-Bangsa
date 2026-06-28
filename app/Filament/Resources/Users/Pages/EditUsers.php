<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UsersResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUsers extends EditRecord
{
    protected static string $resource = UsersResource::class;

    public function getMaxContentWidth(): \Filament\Support\Enums\Width | string | null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pastikan user ini punya record plagiarism quota agar bisa disimpan lewat form
        app(\App\Services\PlagiarismQuotaService::class)->getQuota($this->getRecord());

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
