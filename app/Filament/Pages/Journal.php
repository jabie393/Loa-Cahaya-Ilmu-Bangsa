<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use App\Models\Journal as JournalModel;

class Journal extends Page
{
    protected string $view = 'filament.pages.journal';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = '1. Unduh Template';
    protected static ?string $title = 'Unduh Template';

    public function getJurnals(): array
    {
        return JournalModel::all()->toArray();
    }
}
