<?php

namespace App\Filament\Resources\SubmissionPricings\Pages;

use App\Filament\Resources\SubmissionPricings\SubmissionPricingResource;
use Filament\Resources\Pages\ListRecords;

class ListSubmissionPricings extends ListRecords
{
    protected static string $resource = SubmissionPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
