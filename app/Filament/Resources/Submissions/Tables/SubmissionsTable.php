<?php

namespace App\Filament\Resources\Submissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('author_name')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('institution')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('journal.name')
                    ->searchable(),
                TextColumn::make('volume')
                    ->searchable(),
                TextColumn::make('date_of_loa')
                    ->date()
                    ->sortable(),
                TextColumn::make('proof_of_payment')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('submission_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('approved_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
