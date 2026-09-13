<?php

namespace App\Filament\Resources\SubmissionPricings\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubmissionPricingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tier & Layanan')
                    ->description('Detail nama tier dan kategori layanan penerbitan naskah.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('tier_name')
                                ->label('Nama Tier / Layanan')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('key')
                                ->label('Key Identifier (Sistem)')
                                ->disabled()
                                ->dehydrated(fn ($state) => filled($state))
                                ->helperText('Kode unik identifikasi backend sistem.'),
                        ]),

                        Grid::make(3)->schema([
                            Select::make('category')
                                ->label('Kategori')
                                ->options([
                                    'issn' => 'ISSN (Nasional)',
                                    'international' => 'International (IJEFI / PJLS)',
                                    'addon' => 'Add-on / Layanan Tambahan',
                                    'setting' => 'Pengaturan Parameter Global',
                                ])
                                ->required(),

                            TextInput::make('min_authors')
                                ->label('Min. Penulis')
                                ->numeric()
                                ->nullable()
                                ->helperText('Kosongkan untuk Add-on/Setting'),

                            TextInput::make('max_authors')
                                ->label('Max. Penulis')
                                ->numeric()
                                ->nullable()
                                ->helperText('Kosongkan jika tanpa batas atas'),
                        ]),

                        Grid::make(2)->schema([
                            Toggle::make('with_doi')
                                ->label('Termasuk Nomor DOI')
                                ->helperText('Centang jika tier ini sudah mencakup nomor DOI'),

                            Toggle::make('is_active')
                                ->label('Status Aktif')
                                ->default(true)
                                ->helperText('Hanya tarif aktif yang akan digunakan dalam kalkulasi.'),
                        ]),
                    ]),

                Section::make('Kalkulasi Biaya & Bagi Hasil')
                    ->description('Tentukan tarif kotor yang dibayar pemohon dan porsi pendapatan untuk developer.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('gross_amount')
                                ->label('Total Tarif / Biaya (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->live(debounce: 500)
                                ->helperText('Biaya awal sebelum diskon member.'),

                            TextInput::make('developer_gross_share')
                                ->label('Bagian Developer (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->live(debounce: 500)
                                ->helperText('Porsi developer sebelum dipotong biaya MDR QRIS.'),

                            Placeholder::make('estimated_journal_share')
                                ->label('Estimasi Bagian Jurnal (Rp)')
                                ->content(function ($get) {
                                    $gross = (float) ($get('gross_amount') ?? 0);
                                    $dev = (float) ($get('developer_gross_share') ?? 0);
                                    $journal = max(0, $gross - $dev);
                                    return 'Rp ' . number_format($journal, 0, ',', '.');
                                })
                                ->helperText('Otomatis: Total Biaya dikurangi Bagian Developer.'),
                        ]),

                        Textarea::make('notes')
                            ->label('Catatan / Keterangan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
