<?php

namespace App\Filament\Resources\Submissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Submission;
use App\Filament\Resources\Submissions\SubmissionResource;
use Illuminate\Support\Facades\Auth;

class SubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('author_name')
                    ->searchable(),
                TextColumn::make('institution')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('journal.name')
                    ->searchable(),
                TextColumn::make('volume')
                    ->searchable(),
                TextColumn::make('proof_of_payment')
                    ->label('Pembayaran')
                    ->badge()
                    ->state(fn (Submission $record): string => $record->proof_of_payment ? 'Paid' : 'Unpaid')
                    ->color(fn (string $state): string => $state === 'Paid' ? 'success' : 'danger')
                    ->icon(fn (string $state): string => $state === 'Paid' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->searchable(),
                TextColumn::make('status')
                    ->color(fn (string $state): string => $state === 'Pending' ? 'primary' : 'success')
                    ->icon(fn (string $state): string => $state === 'Pending' ? 'heroicon-o-clock' : 'heroicon-o-check-circle')
                    ->badge(),
                TextColumn::make('submission_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('approved_date')
                    ->date()
                    ->sortable()
                    ->visible(fn () => Auth::user()?->hasRole('super_admin')),
            ])
            ->filters([
                //
            ])
            ->recordUrl(fn (Submission $record): string => SubmissionResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->url(fn (Submission $record): string => SubmissionResource::getUrl('review', ['record' => $record]))
                    ->visible(fn (Submission $record) => Auth::user()->hasRole('super_admin') && $record->status !== 'Approved'),
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Submission $record): string => SubmissionResource::getUrl('view', ['record' => $record])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
