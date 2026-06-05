<?php

namespace App\Filament\Resources\Submissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use App\Models\Submission;
use App\Filament\Resources\Submissions\SubmissionResource;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\ActionGroup;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubmissionApproved;
use Illuminate\Database\Eloquent\Collection;
use Filament\Actions\BulkAction;



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
                    ->description(fn(Submission $record) => \Illuminate\Support\Str::words($record->title ?? '', 10)),
                TextColumn::make('journal.name')
                    ->label('Jurnal & Volume')
                    ->searchable()
                    ->description(fn(Submission $record) => \Illuminate\Support\Str::words($record->volume ?? '', 10)),
                TextColumn::make('proof_of_payment')
                    ->label('Bukti Pembayaran')
                    ->badge()
                    ->state(fn(Submission $record): string => ($record->proof_of_payment || $record->status === 'Approved') ? 'Paid' : 'Unpaid')
                    ->color(fn(string $state): string => $state === 'Paid' ? 'success' : 'danger')
                    ->icon(fn(string $state): string => $state === 'Paid' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->searchable(),
                TextColumn::make('status')
                    ->color(fn(string $state): string => match ($state) {
                        'Pending' => 'primary',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default => 'gray'
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'Pending' => 'heroicon-o-clock',
                        'Approved' => 'heroicon-o-check-circle',
                        'Rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle'
                    })
                    ->badge()
                    ->sortable(
                        query: fn(\Illuminate\Database\Eloquent\Builder $query, string $direction): \Illuminate\Database\Eloquent\Builder =>
                        $query->orderBy('sort_priority', $direction)->orderBy('created_at', 'desc')
                    ),
                IconColumn::make('manuscript_file')
                    ->label('Manuscript')
                    ->icon(fn($state) => $state ? 'heroicon-o-arrow-down-tray' : null)
                    ->color('primary')
                    ->url(fn(Submission $record) => $record->manuscript_file ? Storage::disk('public')->url($record->manuscript_file) : null)
                    ->openUrlInNewTab()
                    ->placeholder('-'),
                TextColumn::make('ojs_status')
                    ->label('OJS Status')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'submitted' => 'info',
                        'accepted' => 'primary',
                        'published' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ojs_synced_at')
                    ->label('Last Sync')
                    ->dateTime('d M Y H:i:s')
                    ->placeholder('-')
                    ->sortable(),
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
            ->defaultSort('status', 'asc')
            ->filters([
                //
            ])
            ->recordUrl(fn(Submission $record): ?string => SubmissionResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ActionGroup::make([
                    Action::make('review')
                        ->label('Review')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->url(fn(Submission $record): ?string => SubmissionResource::getUrl('review', ['record' => $record]))
                        ->visible(fn(Submission $record) => Auth::user()->hasRole('super_admin') && $record->status !== 'Approved'),
                    Action::make('view')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->color('primary')
                        ->url(fn(Submission $record): ?string => SubmissionResource::getUrl('view', ['record' => $record])),
                    EditAction::make()
                        ->label(fn(Submission $record): string => $record->status === 'Rejected' ? 'Revise Submission' : 'Edit Submission'),
                    Action::make('Konfirmasi LOA ke Admin')
                        ->label('Konfirmasi LOA ke Admin')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('primary')
                        ->url(fn(Submission $record) => 'https://wa.me/' . (\App\Models\User::find(1)?->phone ?? '') . '?text=Halo%20Admin%20LOA%2C%20Saya%20ingin%20bertanya%20tentang%20pengajuan%20LOA%20saya%20dengan%20nomor%20registrasi%20' . $record->id)
                        ->openUrlInNewTab(),
                    Action::make('download')
                        ->label('Download LOA')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->url(fn(Submission $record) => route('public.loa.preview', ['record' => $record, 'download' => 1]))
                        ->openUrlInNewTab()
                        ->visible(fn(Submission $record) => $record->status === 'Approved'),
                    Action::make('download')
                        ->label('Download AC')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->url(fn(Submission $record) => route('public.ac.preview', ['record' => $record, 'download' => 1]))
                        ->openUrlInNewTab()
                        ->visible(fn(Submission $record) => $record->status === 'Approved'),
                    Action::make('download_pfc')
                        ->label('Download PFC')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->url(fn(Submission $record) => route('public.pfc.preview', ['record' => $record, 'download' => 1]))
                        ->openUrlInNewTab()
                        ->visible(fn(Submission $record) => $record->status === 'Approved'),
                ])
                    ->label('')
                    ->button()
                    ->icon('heroicon-o-eye'),
            ], position: RecordActionsPosition::BeforeColumns)

            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation(false)
                        ->action(function (Collection $records) {
                            $count = 0;
                            $records->each(function (Submission $record) use (&$count) {
                                if ($record->status !== 'Approved') {
                                    if ($record->proof_of_payment) {
                                        Storage::disk('public')->delete($record->proof_of_payment);
                                    }

                                    // Run OJS Submission
                                    try {
                                        app(\App\Services\OjsSubmissionService::class)->submit($record);
                                    } catch (\Throwable $e) {
                                        \Illuminate\Support\Facades\Log::warning("OJS integration failed during bulk approval of submission ID: {$record->id}. Error: {$e->getMessage()}");
                                    }

                                    $record->update([
                                        'status' => 'Approved',
                                        'approved_date' => now(),
                                        'proof_of_payment' => null,
                                    ]);

                                    Mail::to($record->email)->send(new SubmissionApproved($record));
                                    $count++;
                                }
                            });

                            if ($count > 0) {
                                Notification::make()
                                    ->title($count . ' submissions approved successfully')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn() => Auth::user()->hasRole('super_admin')),
                    DeleteBulkAction::make(),
                ]),
            ]);


    }
}
