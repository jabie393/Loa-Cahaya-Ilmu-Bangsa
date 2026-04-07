<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Jurnal extends Page
{
    use HasPageShield;
    protected string $view = 'filament.pages.jurnal';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    public function getJurnals(): array
    {
        return [
            [
                'title' => 'Jurnal Ilmu Pendidikan',
                'cover' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg',
                'url' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg',
            ],
            [
                'title' => 'Jurnal Teknologi Informasi',
                'cover' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg',
                'url' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg',
            ],
            [
                'title' => 'Jurnal Ekonomi & Bisnis',
                'cover' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg',
                'url' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg',
            ],
            [
                'title' => 'Jurnal Psikologi Terapan',
                'cover' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg',
                'url' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg',
            ],
        ];
    }
}
