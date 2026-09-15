<?php

namespace App\Filament\Resources\SubmissionPricings\Tables;

use App\Models\SubmissionPricing;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubmissionPricingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'issn' => 'ISSN (Nasional)',
                        'international' => 'International',
                        'addon' => 'Add-on',
                        'setting' => 'Parameter',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'info' => 'issn',
                        'primary' => 'international',
                        'success' => 'addon',
                        'warning' => 'setting',
                    ]),

                TextColumn::make('tier_name')
                    ->label('Nama Tier / Layanan')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (SubmissionPricing $record): ?string => $record->notes),

                TextColumn::make('gross_amount')
                    ->label('Biaya / Tarif')
                    ->formatStateUsing(function ($state, SubmissionPricing $record): string {
                        if ($record->key === 'setting_mdr_rate') {
                            return (round((float) $state * 100, 2)) . '%';
                        }
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    })
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('developer_gross_share')
                    ->label('Hak Dev (Kotor)')
                    ->formatStateUsing(function ($state, SubmissionPricing $record): string {
                        if ($record->category === 'setting') {
                            return '-';
                        }
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    })
                    ->color('primary')
                    ->weight('semibold')
                    ->sortable(),

                TextColumn::make('journal_share')
                    ->label('Hak Jurnal')
                    ->formatStateUsing(function ($state, SubmissionPricing $record): string {
                        if ($record->category === 'setting') {
                            return '-';
                        }
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    })
                    ->color('success')
                    ->weight('semibold'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'issn' => 'ISSN (Nasional)',
                        'international' => 'International',
                        'addon' => 'Add-on',
                        'setting' => 'Parameter Global',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(fn (SubmissionPricing $record): string => "Ubah Tarif: {$record->tier_name}")
                    ->modalWidth('2xl')
                    ->modalDescription('Perbarui tarif biaya pemohon dan bagian pendapatan developer.')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batal')
                    ->successNotificationTitle('Tarif berhasil diperbarui!'),
            ]);
    }
}
