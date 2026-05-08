<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'panel_user' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                // CONSOLIDATED SISA HARI INI
                TextColumn::make('daily_stats')
                    ->label('Sisa (Hari Ini)')
                    ->getStateUsing(function ($record) {
                        $review = $record?->hasRole('super_admin')
                            ? 'Unlimited'
                            : (max(0, ($record?->userQuota?->daily_limit ?? config('quota.default_daily_limit')) - ($record?->userQuota?->daily_used ?? 0)) . ' / ' . ($record?->userQuota?->daily_limit ?? config('quota.default_daily_limit')));

                        $plagiarism = $record?->hasRole('super_admin')
                            ? 'Unlimited'
                            : (max(0, ($record?->userPlagiarismQuota?->daily_limit ?? config('quota.plagiarism_daily_limit')) - ($record?->userPlagiarismQuota?->daily_used ?? 0)) . ' / ' . ($record?->userPlagiarismQuota?->daily_limit ?? config('quota.plagiarism_daily_limit')));

                        return new HtmlString("
                            <div style='display: grid; grid-template-columns: 75px auto; font-size: 0.85rem; row-gap: 2px;'>
                                <span style='color: #6b7280;'>Review</span>
                                <span>: {$review}</span>
                                <span style='color: #6b7280;'>Plagiarism</span>
                                <span>: {$plagiarism}</span>
                            </div>
                        ");
                    })
                    ->html(),

                // CONSOLIDATED CREDITS
                TextColumn::make('credits_stats')
                    ->label('Credits')
                    ->getStateUsing(function ($record) {
                        $reviewCredits = $record->hasRole('super_admin') ? 'Unlimited' : ($record?->userQuota?->review_credits ?? 0);
                        $plagiarismCredits = $record->hasRole('super_admin') ? 'Unlimited' : ($record?->userPlagiarismQuota?->additional_credits ?? 0);

                        return new HtmlString("
                            <div style='display: grid; grid-template-columns: 75px auto; font-size: 0.85rem; row-gap: 2px;'>
                                <span style='color: #6b7280;'>Review</span>
                                <span>: {$reviewCredits}</span>
                                <span style='color: #6b7280;'>Plagiarism</span>
                                <span>: {$plagiarismCredits}</span>
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
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
