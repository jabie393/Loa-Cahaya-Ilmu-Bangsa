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
use Filament\Actions\ActionGroup;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;



class SubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('author_name')
                    ->label('Penulis & Judul')
                    ->words(5)
                    ->searchable()
                    ->description(fn (Submission $record): string => \Illuminate\Support\Str::words($record->title, 10)),
                TextColumn::make('journal.name')
                    ->label('Jurnal & Volume')
                    ->searchable()
                    ->description(fn (Submission $record): string => \Illuminate\Support\Str::words($record->volume, 10)),
                TextColumn::make('proof_of_payment')
                    ->label('Bukti Pembayaran')
                    ->badge()
                    ->state(fn (Submission $record): string => ($record->proof_of_payment || $record->status === 'Approved') ? 'Paid' : 'Unpaid')
                    ->color(fn (string $state): string => $state === 'Paid' ? 'success' : 'danger')
                    ->icon(fn (string $state): string => $state === 'Paid' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->searchable(),
                TextColumn::make('status')
                    ->color(fn (string $state): string => match($state) {
                        'Pending' => 'primary',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default => 'gray'
                    })
                    ->icon(fn (string $state): string => match($state) {
                        'Pending' => 'heroicon-o-clock',
                        'Approved' => 'heroicon-o-check-circle',
                        'Rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle'
                    })
                    ->badge(),
                TextColumn::make('submission_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('approved_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('rejected_date')
                    ->label('Rejection Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordUrl(fn (Submission $record): string => SubmissionResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ActionGroup::make([
                    Action::make('review')
                        ->label('Review')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->url(fn (Submission $record): string => SubmissionResource::getUrl('review', ['record' => $record]))
                        ->visible(fn (Submission $record) => Auth::user()->hasRole('super_admin') && $record->status !== 'Approved'),
                    Action::make('view')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->color('primary')
                        ->url(fn (Submission $record): string => SubmissionResource::getUrl('view', ['record' => $record])),
                    EditAction::make(),
                    Action::make('Tanya admin')
                    ->label('Tanya Admin')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary')
                    ->url(fn (Submission $record) => 'https://wa.me/' . (\App\Models\User::find(1)?->phone ?? '') . '?text=Halo%20Admin%20LOA%2C%20Saya%20ingin%20bertanya%20tentang%20pengajuan%20LOA%20saya%20dengan%20nomor%20registrasi%20' . $record->id)
                    ->openUrlInNewTab(),
                    Action::make('download')
                    ->label('Download LOA')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (Submission $record) => route('public.loa.preview', ['record' => $record, 'download' => 1]))
                    ->openUrlInNewTab()
                    ->visible(fn (Submission $record) => $record->status === 'Approved'),
                    Action::make('download')
                    ->label('Download AC')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (Submission $record) => route('public.ac.preview', ['record' => $record, 'download' => 1]))
                    ->openUrlInNewTab()
                    ->visible(fn (Submission $record) => $record->status === 'Approved'),
                    Action::make('download_pfc')
                    ->label('Download PFC')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (Submission $record) => route('public.pfc.preview', ['record' => $record, 'download' => 1]))
                    ->openUrlInNewTab()
                    ->visible(fn (Submission $record) => $record->status === 'Approved'),
                ])
                ->label('')
                ->button()
                ->icon('heroicon-o-eye'),
            ], position: RecordActionsPosition::BeforeColumns)
            
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

            
    }
}
