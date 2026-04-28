<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

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
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Nomor Telepon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->color(fn($state) => match ($state) {
                        'admin' => 'danger',
                        'user' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('pending_submissions_count')
                    ->label('Pending Submission')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('rejected_submissions_count')
                    ->label('Rejected Submission')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('userQuota.daily_used')
                    ->label('Sisa Kuota Hari Ini')
                    ->getStateUsing(fn ($record) => $record?->hasRole('super_admin') 
                        ? 'Unlimited' 
                        : (max(0, ($record?->userQuota?->daily_limit ?? config('quota.default_daily_limit')) - ($record?->userQuota?->daily_used ?? 0)) . ' / ' . ($record?->userQuota?->daily_limit ?? config('quota.default_daily_limit'))))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('userQuota.review_credits')
                    ->label('Review Credits')
                    ->getStateUsing(fn ($record) => $record->hasRole('super_admin') ? 'Unlimited' : ($record?->userQuota?->review_credits ?? 0))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('userQuota.total_used')
                    ->label('Total Review')
                    ->getStateUsing(fn ($record) => $record?->userQuota?->total_used ?? 0)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
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
