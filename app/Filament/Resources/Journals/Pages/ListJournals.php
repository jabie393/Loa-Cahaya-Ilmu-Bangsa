<?php

namespace App\Filament\Resources\Journals\Pages;

use App\Filament\Resources\Journals\JournalsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJournals extends ListRecords
{
    protected static string $resource = JournalsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
