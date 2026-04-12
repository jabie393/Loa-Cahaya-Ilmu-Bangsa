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

    public function getJurnals(): array
    {
        return JournalModel::all()->toArray();
    }
}
