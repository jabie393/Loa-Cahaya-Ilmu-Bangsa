<?php

namespace App\Filament\Resources\SubmissionPricings\Schemas;

use App\Models\SubmissionPricing;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SubmissionPricingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('tier_info')
                    ->label('Layanan / Tier')
                    ->content(function (?SubmissionPricing $record) {
                        if (!$record) return '-';
                        $note = $record->notes ? " — {$record->notes}" : '';
                        return "{$record->tier_name}{$note}";
                    }),

                Grid::make(2)->schema([
                    TextInput::make('gross_amount')
                        ->label(fn (?SubmissionPricing $record) => $record?->key === 'setting_mdr_rate' ? 'Rate MDR (Desimal, contoh 0.007 = 0.7%)' : 'Total Biaya / Tarif (Rp)')
                        ->numeric()
                        ->prefix(fn (?SubmissionPricing $record) => $record?->key === 'setting_mdr_rate' ? '' : 'Rp')
                        ->required()
                        ->live(debounce: 400)
                        ->helperText(fn (?SubmissionPricing $record) => $record?->key === 'setting_mdr_rate' ? 'Persentase MDR QRIS' : 'Biaya yang dibayar pemohon/penulis.'),

                    TextInput::make('developer_gross_share')
                        ->label('Bagian Developer (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->live(debounce: 400)
                        ->hidden(fn (?SubmissionPricing $record) => $record?->category === 'setting')
                        ->helperText('Hak bagian kotor untuk developer.'),
                ]),

                Placeholder::make('journal_share_preview')
                    ->label('Estimasi Bagian Jurnal (Rp)')
                    ->hidden(fn (?SubmissionPricing $record) => $record?->category === 'setting')
                    ->content(function ($get) {
                        $gross = (float) ($get('gross_amount') ?? 0);
                        $dev = (float) ($get('developer_gross_share') ?? 0);
                        $journal = max(0, $gross - $dev);
                        return 'Rp ' . number_format($journal, 0, ',', '.');
                    })
                    ->helperText('Otomatis: Total Biaya dikurangi Bagian Developer.'),
            ]);
    }
}
