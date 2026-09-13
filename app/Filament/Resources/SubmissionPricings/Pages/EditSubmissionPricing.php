<?php

namespace App\Filament\Resources\SubmissionPricings\Pages;

use App\Filament\Resources\SubmissionPricings\SubmissionPricingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubmissionPricing extends EditRecord
{
    protected static string $resource = SubmissionPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
