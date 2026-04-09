<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubmissions extends ListRecords
{
    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.pages.list-submissions';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Buat Pengajuan Baru')
            ->icon('heroicon-o-plus'),
        ];
    }
}
