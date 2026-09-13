<?php

namespace App\Filament\Resources\SubmissionPricings\Pages;

use App\Filament\Resources\SubmissionPricings\SubmissionPricingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateSubmissionPricing extends CreateRecord
{
    protected static string $resource = SubmissionPricingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['key'])) {
            $data['key'] = Str::slug($data['tier_name'] . '_' . ($data['category'] ?? 'tier'), '_');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
