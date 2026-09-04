<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class DevPayoutsPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Dev Payouts';
    protected static ?string $title = 'Developer Payouts';
    protected static ?string $slug = 'dev-payouts';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.dev-payouts-page';

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('ryu_dev') ?? false;
    }
}
