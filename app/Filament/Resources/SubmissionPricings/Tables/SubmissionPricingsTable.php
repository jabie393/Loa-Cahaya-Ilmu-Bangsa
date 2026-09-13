<?php

namespace App\Filament\Resources\SubmissionPricings\Tables;

use App\Models\SubmissionPricing;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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

                TextColumn::make('author_range_label')
                    ->label('Rentang Penulis')
                    ->alignCenter(),

                IconColumn::make('with_doi')
                    ->label('DOI')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('gross_amount')
                    ->label('Biaya / Tarif')
                    ->formatStateUsing(function ($state, SubmissionPricing $record): string {
                        if ($record->key === 'setting_mdr_rate') {
                            return ($state * 100) . '%';
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

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
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
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(fn (SubmissionPricing $record): string => "Ubah Tarif: {$record->tier_name}")
                    ->modalWidth('lg')
                    ->modalDescription('Perbarui tarif biaya pemohon dan bagian pendapatan developer.')
                    ->successNotificationTitle('Tarif berhasil diperbarui!'),
            ]);
    }
}
