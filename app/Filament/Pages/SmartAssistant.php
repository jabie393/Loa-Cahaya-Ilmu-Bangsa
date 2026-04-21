<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;

class SmartAssistant extends Page
{
    protected string $view = 'filament.pages.smart-assistant';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $title = 'Smart Journal Assistant';
    protected static ?string $slug = 'smart-assistant';

    public function getSubheading(): ?string
    {
        return 'Unggah jurnal Anda untuk mendapatkan reviu singkat, profesional, dan saran revisi secara otomatis dengan bantuan Artificial Intelligence.';
    }
}
