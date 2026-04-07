<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class JurnalList extends Page
{
    use HasPageShield;
    protected string $view = 'filament.pages.jurnal-list';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    public function getJurnals(): array
    {
        return [
            [
                'title' => 'Jurnal Ilmu Pendidikan',
                'cover' => 'https://via.placeholder.com/300x400/2563eb/ffffff?text=Jurnal+Pendidikan',
                'description' => 'Fokus pada riset pendidikan kontemporer dan inovasi pembelajaran.',
            ],
            [
                'title' => 'Jurnal Teknologi Informasi',
                'cover' => 'https://via.placeholder.com/300x400/059669/ffffff?text=Jurnal+TI',
                'description' => 'Mengeksplorasi perkembangan terbaru dalam rekayasa perangkat lunak dan AI.',
            ],
            [
                'title' => 'Jurnal Ekonomi & Bisnis',
                'cover' => 'https://via.placeholder.com/300x400/d97706/ffffff?text=Jurnal+Ekonomi',
                'description' => 'Analisis mendalam mengenai tren pasar global dan manajemen strategis.',
            ],
            [
                'title' => 'Jurnal Psikologi Terapan',
                'cover' => 'https://via.placeholder.com/300x400/7c3aed/ffffff?text=Jurnal+Psikologi',
                'description' => 'Studi tentang perilaku manusia dalam konteks organisasi dan klinis.',
            ],
            [
                'title' => 'Jurnal Ilmu Pendidikan',
                'cover' => 'https://via.placeholder.com/300x400/2563eb/ffffff?text=Jurnal+Pendidikan',
                'description' => 'Fokus pada riset pendidikan kontemporer dan inovasi pembelajaran.',
            ],
            [
                'title' => 'Jurnal Teknologi Informasi',
                'cover' => 'https://via.placeholder.com/300x400/059669/ffffff?text=Jurnal+TI',
                'description' => 'Mengeksplorasi perkembangan terbaru dalam rekayasa perangkat lunak dan AI.',
            ],
            [
                'title' => 'Jurnal Ekonomi & Bisnis',
                'cover' => 'https://via.placeholder.com/300x400/d97706/ffffff?text=Jurnal+Ekonomi',
                'description' => 'Analisis mendalam mengenai tren pasar global dan manajemen strategis.',
            ],
            [
                'title' => 'Jurnal Psikologi Terapan',
                'cover' => 'https://via.placeholder.com/300x400/7c3aed/ffffff?text=Jurnal+Psikologi',
                'description' => 'Studi tentang perilaku manusia dalam konteks organisasi dan klinis.',
            ],
            [
                'title' => 'Jurnal Ilmu Pendidikan',
                'cover' => 'https://via.placeholder.com/300x400/2563eb/ffffff?text=Jurnal+Pendidikan',
                'description' => 'Fokus pada riset pendidikan kontemporer dan inovasi pembelajaran.',
            ],
            [
                'title' => 'Jurnal Teknologi Informasi',
                'cover' => 'https://via.placeholder.com/300x400/059669/ffffff?text=Jurnal+TI',
                'description' => 'Mengeksplorasi perkembangan terbaru dalam rekayasa perangkat lunak dan AI.',
            ],
            [
                'title' => 'Jurnal Ekonomi & Bisnis',
                'cover' => 'https://via.placeholder.com/300x400/d97706/ffffff?text=Jurnal+Ekonomi',
                'description' => 'Analisis mendalam mengenai tren pasar global dan manajemen strategis.',
            ],
            [
                'title' => 'Jurnal Psikologi Terapan',
                'cover' => 'https://via.placeholder.com/300x400/7c3aed/ffffff?text=Jurnal+Psikologi',
                'description' => 'Studi tentang perilaku manusia dalam konteks organisasi dan klinis.',
            ],
            [
                'title' => 'Jurnal Ilmu Pendidikan',
                'cover' => 'https://via.placeholder.com/300x400/2563eb/ffffff?text=Jurnal+Pendidikan',
                'description' => 'Fokus pada riset pendidikan kontemporer dan inovasi pembelajaran.',
            ],
            [
                'title' => 'Jurnal Teknologi Informasi',
                'cover' => 'https://via.placeholder.com/300x400/059669/ffffff?text=Jurnal+TI',
                'description' => 'Mengeksplorasi perkembangan terbaru dalam rekayasa perangkat lunak dan AI.',
            ],
            [
                'title' => 'Jurnal Ekonomi & Bisnis',
                'cover' => 'https://via.placeholder.com/300x400/d97706/ffffff?text=Jurnal+Ekonomi',
                'description' => 'Analisis mendalam mengenai tren pasar global dan manajemen strategis.',
            ],
            [
                'title' => 'Jurnal Psikologi Terapan',
                'cover' => 'https://via.placeholder.com/300x400/7c3aed/ffffff?text=Jurnal+Psikologi',
                'description' => 'Studi tentang perilaku manusia dalam konteks organisasi dan klinis.',
            ],
        ];
    }
}
