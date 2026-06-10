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
                    ->label('Detail Artikel')
                    ->html()
                    ->state(fn(Submission $record) => $record)
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) {
                        return $query->where('author_name', 'like', "%{$search}%")
                            ->orWhere('title', 'like', "%{$search}%")
                            ->orWhereHas('journal', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhere('volume', 'like', "%{$search}%");
                    })
                    ->formatStateUsing(function ($record) {
                        $authorNames = $record->author_name;
                        if (is_array($authorNames)) {
                            $authors = implode(', ', $authorNames);
                        } elseif ($authorNames instanceof \Illuminate\Support\Collection) {
                            $authors = $authorNames->implode(', ');
                        } else {
                            $authors = (string) $authorNames;
                        }

                        $title = $record->title ?? 'Untitled';
                        $journalName = $record->journal?->name ?? 'N/A';
                        $volume = $record->volume ? " — {$record->volume}" : '';

                        return new \Illuminate\Support\HtmlString("
                            <div class='flex flex-col gap-0.5 py-1' style='max-width: 450px;'>
                                <div class='font-bold text-sm text-gray-900 dark:text-white break-words'>
                                    {$authors}
                                </div>
                                <div class='text-xs text-gray-500 dark:text-gray-400 break-words' style='display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;'>
                                    {$title}
                                </div>
                                <div class='text-[10px] text-primary-600 dark:text-primary-400 font-semibold break-words'>
                                    {$journalName}{$volume}
                                </div>
                            </div>
                        ");
                    }),
                TextColumn::make('proof_of_payment')
                    ->label('Bukti Pembayaran')
                    ->badge()
                    ->state(fn(Submission $record): string => ($record->proof_of_payment || $record->status === 'Approved') ? 'Paid' : 'Unpaid')
                    ->color(fn(string $state): string => $state === 'Paid' ? 'success' : 'danger')
                    ->icon(fn(string $state): string => $state === 'Paid' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status & OJS')
                    ->html()
                    ->state(fn(Submission $record) => $record)
                    ->formatStateUsing(function ($record) {
                        // 1. LOA Badge Info
                        $loaStatus = $record->status;
                        $loaClass = match ($loaStatus) {
                            'Approved' => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800/50',
                            'Rejected' => 'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800/50',
                            default => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50' // Pending
                        };

                        // 2. OJS Badge Info
                        $ojsStatus = $record->ojs_status ?? 'Not Sent';
                        $ojsClass = match ($record->ojs_status) {
                            'pending' => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50',
                            'submitted' => 'text-sky-700 bg-sky-50 border-sky-200 dark:text-sky-400 dark:bg-sky-950/30 dark:border-sky-800/50',
                            'accepted' => 'text-indigo-700 bg-indigo-50 border-indigo-200 dark:text-indigo-400 dark:bg-indigo-950/30 dark:border-indigo-800/50',
                            'published' => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800/50',
                            'failed' => 'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800/50',
                            default => 'text-gray-700 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-800/30 dark:border-gray-700/50' // null / not sent
                        };
                        $ojsLabel = ucfirst($ojsStatus);

                        // 3. Date / Sync Time Info
                        $dateString = '';
                        if ($record->status === 'Approved' && $record->ojs_synced_at) {
                            $dateString = "<span class='text-[10px] text-gray-500 dark:text-gray-400 font-mono'>" . $record->ojs_synced_at->translatedFormat('d M Y H:i:s') . "</span>";
                        }

                        return new \Illuminate\Support\HtmlString("
                            <div class='flex flex-col gap-1.5 py-1 align-start justify-center'>
                                <div class='flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300'>
                                    <span class='font-semibold min-w-[32px]'>LOA:</span>
                                    <span class='px-2 py-0.5 text-[10px] font-semibold rounded-full border {$loaClass}'>
                                        {$loaStatus}
                                    </span>
                                </div>
                                <div class='flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300'>
                                    <span class='font-semibold min-w-[32px]'>OJS:</span>
                                    <span class='px-2 py-0.5 text-[10px] font-semibold rounded-full border {$ojsClass}'>
                                        {$ojsLabel}
                                    </span>
                                </div>
                                {$dateString}
                            </div>
                        ");
                    })
                    ->sortable(
                        query: fn(\Illuminate\Database\Eloquent\Builder $query, string $direction): \Illuminate\Database\Eloquent\Builder =>
                        $query->orderBy('sort_priority', $direction)->orderBy('created_at', 'desc')
                    ),
                IconColumn::make('manuscript_file')
                    ->label('File PDF')
                    ->icon(fn($state) => $state ? 'heroicon-o-arrow-down-tray' : null)
                    ->color('primary')
                    ->url(fn(Submission $record) => $record->manuscript_file ? Storage::disk('public')->url($record->manuscript_file) : null)
                    ->openUrlInNewTab()
                    ->placeholder('-'),
                TextColumn::make('submission_date')
                    ->label('Tanggal')
                    ->state(function (Submission $record) {
                        if ($record->status === 'Approved') {
                            return $record->approved_date;
                        }
                        if ($record->status === 'Rejected') {
                            return $record->rejected_date;
                        }
                        return $record->submission_date;
                    })
                    ->date('d M Y')
                    ->description(function (Submission $record) {
                        if ($record->status === 'Approved') {
                            return 'Disetujui';
                        }
                        if ($record->status === 'Rejected') {
                            return 'Ditolak';
                        }
                        return 'Diajukan';
                    })
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

                                    // Run OJS Submission in background
                                    try {
                                        \App\Services\OjsSubmissionService::submitInBackground($record);
                                    } catch (\Throwable $e) {
                                        \Illuminate\Support\Facades\Log::warning("OJS integration failed to dispatch background job for submission ID: {$record->id}. Error: {$e->getMessage()}");
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
