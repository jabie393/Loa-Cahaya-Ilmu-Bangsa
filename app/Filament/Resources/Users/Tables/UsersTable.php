<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->icon(fn (\App\Models\User $record): ?string => $record->isMember() ? 'heroicon-s-check-badge' : null)
                    ->iconColor('primary')
                    ->iconPosition(IconPosition::After)
                    ->tooltip(fn (\App\Models\User $record): ?string => $record->isMember() ? 'Member CIB Aktif (Potongan Rp 10.000 / Transaksi)' : null),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_member')
                    ->label('Member CIB')
                    ->getStateUsing(fn (\App\Models\User $record): bool => $record->isMember())
                    ->disabled(fn (\App\Models\User $record): bool => $record->hasRole('ryu_dev'))
                    ->extraAttributes(fn (\App\Models\User $record): array => $record->hasRole('ryu_dev') ? [
                        'style' => 'display: none !important; pointer-events: none !important;',
                    ] : [])
                    ->extraCellAttributes(fn (\App\Models\User $record): array => $record->hasRole('ryu_dev') ? [
                        'class' => "pointer-events-none cursor-default select-none after:content-['-'] after:text-slate-400 after:text-sm after:font-medium",
                        'style' => 'pointer-events: none !important; cursor: default !important;',
                    ] : [])
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'panel_user' => 'User',
                        'ryu_dev' => 'Developer',
                        default => Str::headline($state),
                    })
                    ->color(fn(string $state): string => match (strtolower(str_replace(' ', '_', $state))) {
                        'super_admin' => 'danger',
                        'panel_user', 'user' => 'success',
                        'ryu_dev', 'developer' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                // CONSOLIDATED SISA HARI INI
                TextColumn::make('daily_stats')
                    ->label('Sisa (Hari Ini)')
                    ->getStateUsing(function ($record) {
                        if ($record?->hasRole('ryu_dev')) {
                            return '-';
                        }

                        $plagiarism = $record?->hasRole('super_admin')
                            ? 'Unlimited'
                            : (max(0, ($record?->userPlagiarismQuota?->daily_limit ?? config('quota.plagiarism_daily_limit')) - ($record?->userPlagiarismQuota?->daily_used ?? 0)) . ' / ' . ($record?->userPlagiarismQuota?->daily_limit ?? config('quota.plagiarism_daily_limit')));

                        return new HtmlString("
                            <div style='font-size: 0.85rem;'>
                                <span>Plagiarism: {$plagiarism}</span>
                            </div>
                        ");
                    })
                    ->html(),

                // CONSOLIDATED CREDITS
                TextColumn::make('credits_stats')
                    ->label('Credits')
                    ->getStateUsing(function ($record) {
                        if ($record?->hasRole('ryu_dev')) {
                            return '-';
                        }

                        $plagiarismCredits = $record->hasRole('super_admin') ? 'Unlimited' : ($record?->userPlagiarismQuota?->additional_credits ?? 0);

                        return new HtmlString("
                            <div style='font-size: 0.85rem;'>
                                <span>Plagiarism: {$plagiarismCredits}</span>
                            </div>
                        ");
                    })
                    ->html(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_member')
                    ->label('Status Member CIB')
                    ->placeholder('Semua User')
                    ->trueLabel('Hanya Member')
                    ->falseLabel('Non-Member'),
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn (\App\Models\User $record): bool => $record->hasRole('ryu_dev')),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->checkIfRecordIsSelectableUsing(fn (\App\Models\User $record): bool => ! $record->hasRole('ryu_dev'))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
