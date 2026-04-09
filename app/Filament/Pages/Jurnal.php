<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use App\Models\Journal;

class Jurnal extends Page
{
    use HasPageShield;
    protected string $view = 'filament.pages.jurnal';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    public function getJurnals(): array
    {
        return Journal::all()->toArray();
    }
}
