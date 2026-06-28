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
use Filament\Tables\Filters\SelectFilter;



class SubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
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
                            'Draft' => 'text-gray-700 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-800/30 dark:border-gray-700/50',
                            default => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50' // Pending
                        };

                        // 2. AI Review Badge Info
                        $reviewStatus = $record->review_status;
                        if ($reviewStatus !== 'reviewed') {
                            if ($record->status === 'Approved' || !empty($record->ojs_status)) {
                                $reviewStatus = 'N/A';
                            } elseif (empty($reviewStatus)) {
                                $reviewStatus = 'pending';
                            }
                        }

                        $reviewClass = match ($reviewStatus) {
                            'pending' => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50',
                            'processing' => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50',
                            'reviewed' => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800/50',
                            'failed' => 'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800/50',
                            'N/A' => 'text-gray-500 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-800/30 dark:border-gray-700/50',
                            default => 'text-gray-700 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-800/30 dark:border-gray-700/50'
                        };
                        $reviewLabel = $reviewStatus === 'N/A' ? 'N/A' : ucfirst($reviewStatus);

                        // 3. OJS Badge Info
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

                        // 4. Date / Sync Time Info
                        $dateString = '';
                        if ($record->status === 'Approved' && $record->ojs_synced_at) {
                            $dateString = "<span class='text-[10px] text-gray-500 dark:text-gray-400 font-mono'>" . $record->ojs_synced_at->translatedFormat('d M Y H:i:s') . "</span>";
                        }

                        return new \Illuminate\Support\HtmlString("
                            <div class='flex flex-col gap-1 py-1 align-start justify-center'>
                                <div class='flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300'>
                                    <span class='font-semibold min-w-[50px]'>Review:</span>
                                    <span class='inline-flex items-center justify-center w-[80px] py-0.5 text-[10px] font-semibold rounded-full border {$reviewClass}'>
                                        {$reviewLabel}
                                    </span>
                                </div>
                                <div class='flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300'>
                                    <span class='font-semibold min-w-[50px]'>LOA:</span>
                                    <span class='inline-flex items-center justify-center w-[80px] py-0.5 text-[10px] font-semibold rounded-full border {$loaClass}'>
                                        {$loaStatus}
                                    </span>
                                </div>
                                <div class='flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300'>
                                    <span class='font-semibold min-w-[50px]'>OJS:</span>
                                    <span class='inline-flex items-center justify-center w-[80px] py-0.5 text-[10px] font-semibold rounded-full border {$ojsClass}'>
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
            ->defaultSort('sort_priority', 'asc')
            ->filters([
                SelectFilter::make('review_status')
                    ->label('Status Review')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'reviewed' => 'Reviewed',
                        'failed' => 'Failed',
                        'N/A' => 'N/A',
                    ]),
                SelectFilter::make('status')
                    ->label('Status LOA')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Draft' => 'Draft',
                    ]),
                SelectFilter::make('ojs_status')
                    ->label('Status OJS')
                    ->options([
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'accepted' => 'Accepted',
                        'published' => 'Published',
                        'failed' => 'Failed',
                    ]),
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
                    Action::make('request_review_again')
                        ->label('Minta Review Lagi')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn(Submission $record) => in_array($record->review_status, ['failed', 'reviewed']) && $record->status !== 'Approved')
                        ->action(fn(Submission $record) => $record->processReviewInBackground()),
                    Action::make('approve')
                        ->label('Approve LOA')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(Submission $record) => Auth::user()->hasRole('super_admin') && $record->status !== 'Approved')
                        ->action(function (Submission $record) {
                            if ($record->proof_of_payment) {
                                Storage::disk('public')->delete($record->proof_of_payment);
                            }

                            // Run OJS Submission in background
                            try {
                                \App\Services\OjsSubmissionService::submitInBackground($record);
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning("OJS integration failed to dispatch background job for submission ID: {$record->id}. Error: {$e->getMessage()}");
                            }

                            $updateData = [
                                'status' => 'Approved',
                                'approved_date' => now(),
                                'proof_of_payment' => null,
                            ];
                            if ($record->review_status === 'failed') {
                                $updateData['review_status'] = 'N/A';
                            }
                            $record->update($updateData);

                            Mail::to($record->email)->send(new SubmissionApproved($record));

                            Notification::make()
                                ->title('Submission approved successfully')
                                ->success()
                                ->send();
                        }),
                    Action::make('resubmit_ojs')
                        ->label('Resubmit to OJS')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn(Submission $record) => $record->status === 'Approved' && $record->ojs_status === 'failed' && Auth::user()->hasRole('super_admin'))
                        ->action(function (Submission $record) {
                            try {
                                \App\Services\OjsSubmissionService::submitInBackground($record);
                                Notification::make()
                                    ->title('Resubmission dispatched in background')
                                    ->info()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('OJS Resubmission Failed to Dispatch')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
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

                                    $updateData = [
                                        'status' => 'Approved',
                                        'approved_date' => now(),
                                        'proof_of_payment' => null,
                                    ];
                                    if ($record->review_status === 'failed') {
                                        $updateData['review_status'] = 'N/A';
                                    }
                                    $record->update($updateData);

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
