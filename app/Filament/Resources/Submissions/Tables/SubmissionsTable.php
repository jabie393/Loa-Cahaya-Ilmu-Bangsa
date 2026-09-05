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
                        // Payment Badge Info
                        $payStatus = $record->payment_status ?? 'pending';
                        $payClass = match ($payStatus) {
                            'paid' => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800/50',
                            'expired' => 'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800/50',
                            default => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50',
                        };
                        $payLabel = ucfirst($payStatus);


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
                        $query->orderBy('status', $direction)->orderBy('created_at', 'desc')
                    ),

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

                        $doiStatus = 'Tanpa DOI';
                        $doiClass = 'text-gray-500 dark:text-gray-400';
                        if ($record->has_doi === null) {
                            if ($record->want_doi) {
                                $doiStatus = 'Pending';
                                $doiClass = 'text-amber-600 dark:text-amber-400 font-semibold';
                            } else {
                                $doiStatus = 'Tanpa DOI';
                                $doiClass = 'text-gray-500 dark:text-gray-400';
                            }
                        } elseif ($record->has_doi) {
                            if (!empty($record->repository_identifier)) {
                                $doiStatus = $record->repository_identifier;
                                $doiClass = 'text-emerald-600 dark:text-emerald-400 font-mono font-bold';
                            } else {
                                $doiStatus = 'Pending';
                                $doiClass = 'text-amber-600 dark:text-amber-400 font-semibold';
                            }
                        } else {
                            $doiStatus = 'Tanpa DOI';
                            $doiClass = 'text-gray-500 dark:text-gray-400';
                        }

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
                                <div class='text-[10px] text-gray-500 dark:text-gray-400 mt-0.5'>
                                    <span>DOI: </span><span class='{$doiClass}'>{$doiStatus}</span>
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
            ->poll('5s')
            ->defaultSort('volume_sort_key', 'desc')
            ->filters([
                SelectFilter::make('ojs_base_url')
                    ->label('Filter Website OJS')
                    ->placeholder('Semua Website')
                    ->options(function () {
                        $dbUrls = \App\Models\Journal::query()
                            ->whereNotNull('ojs_base_url')
                            ->where('ojs_base_url', '<>', '')
                            ->distinct()
                            ->pluck('ojs_base_url')
                            ->toArray();

                        $urls = [];
                        $urls['default_env'] = 'a. Jurnal Nasional Non Sinta';

                        foreach ($dbUrls as $url) {
                            $host = parse_url($url, PHP_URL_HOST);
                            if (empty($host)) {
                                $host = str_replace(['https://', 'http://', '/'], '', $url);
                            }

                            if ($host === 'ijefijournal.com') {
                                $urls[$url] = 'b. IJEFI Non-Scopus Indexed Journal of Economics and Management';
                            } elseif ($host === 'pjlsedu.com') {
                                $urls[$url] = 'c. PJLSS Non-Scopus Indexed Multidisciplinary Journal';
                            } else {
                                $urls[$url] = $host ?: $url;
                            }
                        }

                        return $urls;
                    })
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'default_env') {
                            return $query->whereHas('journal', function ($q) {
                                $q->whereNull('ojs_base_url')
                                    ->orWhere('ojs_base_url', '');
                            });
                        }

                        return $query->whereHas('journal', function ($q) use ($data) {
                            $q->where('ojs_base_url', $data['value']);
                        });
                    }),
                SelectFilter::make('review_status')
                    ->label('Status Review')
                    ->options([
                        'processing' => 'Processing',
                        'reviewed' => 'Reviewed',
                        'failed' => 'Failed',
                        'N/A' => 'N/A',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        $value = $data['value'];
                        if (empty($value)) {
                            return $query;
                        }

                        if ($value === 'N/A') {
                            return $query->where(function ($q) {
                                $q->where('review_status', 'N/A')
                                    ->orWhere('status', 'Approved')
                                    ->orWhere(fn($sub) => $sub->whereNotNull('ojs_status')->where('ojs_status', '!=', ''));
                            });
                        }

                        return $query->where('review_status', $value);
                    }),
                SelectFilter::make('status')
                    ->label('Status LOA')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),
                SelectFilter::make('ojs_status')
                    ->label('Status OJS')
                    ->options([
                        'not_sent' => 'Not Sent',
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'failed' => 'Failed',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        $value = $data['value'];
                        if (empty($value)) {
                            return $query;
                        }

                        if ($value === 'not_sent') {
                            return $query->where(function ($q) {
                                $q->whereNull('ojs_status')
                                    ->orWhere('ojs_status', '');
                            });
                        }

                        return $query->where('ojs_status', $value);
                    }),
            ])
            ->recordUrl(fn(Submission $record): ?string => $record->review_status === 'processing' ? null : SubmissionResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ActionGroup::make([
                    Action::make('bayar')
                        ->label('Bayar QRIS')
                        ->icon('heroicon-o-credit-card')
                        ->color('primary')
                        ->url(fn(Submission $record): string => SubmissionResource::getUrl('payment', ['record' => $record]))
                        ->visible(fn(Submission $record) => $record->status !== 'Approved' && $record->payment_status !== 'paid' && $record->review_status !== 'processing'),
                    Action::make('review')
                        ->label('Review')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->url(fn(Submission $record): ?string => SubmissionResource::getUrl('review', ['record' => $record]))
                        ->visible(fn(Submission $record) => Auth::user()->hasRole('super_admin') && $record->status !== 'Approved' && $record->review_status !== 'processing'),
                    Action::make('request_review_again')
                        ->label('Minta Review Lagi')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn(Submission $record) => $record->review_status === 'failed' && $record->status !== 'Approved')
                        ->action(fn(Submission $record) => $record->processReviewInBackground()),
                    Action::make('approve')
                        ->label('Approve LOA')
                        ->icon('heroicon-o-check-circle')
                        ->color('primary')
                        ->modalHeading('Konfirmasi Approval')
                        ->modalDescription('Submission akan diproses. Silakan tentukan apakah artikel ini akan diberikan DOI.')
                        ->form([
                            \Filament\Forms\Components\Radio::make('has_doi')
                                ->label('Pilihan DOI')
                                ->options([
                                    1 => 'Berikan DOI',
                                    0 => 'Tanpa DOI',
                                ])
                                ->default(fn(Submission $record) => $record->want_doi ? 1 : 0)
                                ->required(),
                        ])
                        ->visible(fn(Submission $record) => Auth::user()->hasRole('super_admin') && $record->status !== 'Approved' && $record->review_status !== 'processing')
                        ->disabled(fn(Submission $record) => $record->review_status === 'processing' || empty($record->title))
                        ->action(function (Submission $record, array $data) {
                            $hasDoi = (bool) $data['has_doi'];

                            // Record financial transaction for this submission
                            \App\Models\FinanceTransaction::recordSubmissionPayment($record, $record->proof_of_payment);

                            if ($record->proof_of_payment) {
                                Storage::disk('public')->delete($record->proof_of_payment);
                            }

                            $hasDoi = (bool) $data['has_doi'];

                            $updateData = [
                                'status' => 'Approved',
                                'approved_date' => now(),
                                'proof_of_payment' => null,
                                'has_doi' => $hasDoi,
                            ];
                            if ($record->review_status === 'failed') {
                                $updateData['review_status'] = 'N/A';
                            }
                            $record->update($updateData);

                            // Run OJS Submission in background
                            try {
                                \App\Services\OjsSubmissionService::submitInBackground($record);
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning("OJS integration failed to dispatch background job for submission ID: {$record->id}. Error: {$e->getMessage()}");
                            }

                            Notification::make()
                                ->title('Submission approved successfully')
                                ->success()
                                ->send();
                        }),
                    Action::make('tambah_doi')
                        ->label('Tambah DOI')
                        ->icon('heroicon-o-plus-circle')
                        ->color('primary')
                        ->url(fn(Submission $record): string => SubmissionResource::getUrl('payment.doi', ['record' => $record]))
                        ->visible(fn(Submission $record) => $record->status === 'Approved' && !$record->has_doi),
                    Action::make('generate_doi')
                        ->label('Buat DOI')
                        ->icon('heroicon-o-qr-code')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Generate Repository Identifier (DOI Custom)')
                        ->modalDescription('Apakah Anda yakin ingin membuat DOI/Repository Identifier untuk artikel ini? Tindakan ini akan memperbarui data di OJS dan katalog Repository.')
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->modalIconColor('primary')
                        ->modalSubmitAction(fn($action) => $action->color('primary'))
                        ->modalSubmitAction(fn($action) => $action->color('primary'))
                        ->action(function (Submission $record) {
                            $record->update([
                                'has_doi' => true,
                            ]);

                            // Generate DOI immediately
                            $identifierService = new \App\Services\RepositoryIdentifierService();
                            $identifier = $identifierService->generate($record);


                            $repoUrl = rtrim(config('services.repo_url', 'http://127.0.0.1:8001'), '/');
                            $redirectUrl = $repoUrl . '/' . $identifier;
                            $landingPage = "/article/submission-{$record->id}";


                            $record->update([
                                'repository_identifier' => $identifier,
                                'repository_landing_page' => $landingPage,
                                'repository_redirect_url' => $redirectUrl,
                                'repository_identifier_status' => 'active',
                                'repository_identifier_generated_at' => now(),
                            ]);

                            // Run OJS Submission in background
                            try {
                                \App\Services\OjsSubmissionService::submitInBackground($record);
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning("OJS integration failed to dispatch background job for submission ID: {$record->id}. Error: {$e->getMessage()}");
                            }

                            Notification::make()
                                ->title('DOI berhasil dibuat dan disinkronkan')
                                ->success()
                                ->send();
                        })
                        ->visible(function (Submission $record) {
                            if (!Auth::user()?->hasRole('super_admin') || $record->status !== 'Approved' || $record->has_doi || $record->review_status === 'processing') {
                                return false;
                            }
                            if (!empty($record->publication_link)) {
                                $linkHost = parse_url($record->publication_link, PHP_URL_HOST);
                                $targetHost = parse_url($record->journal?->ojs_base_url ?: config('ojs.base_url'), PHP_URL_HOST);
                                if ($linkHost && $targetHost && strtolower($linkHost) !== strtolower($targetHost)) {
                                    return false;
                                }
                            }
                            return true;
                        }),

                    Action::make('resubmit_ojs')
                        ->label('Resubmit to OJS')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(function (Submission $record) {
                            if (!Auth::user()?->hasRole('super_admin') || $record->status !== 'Approved' || in_array($record->ojs_status, ['submitted', 'published']) || $record->review_status === 'processing') {
                                return false;
                            }
                            if (!empty($record->publication_link)) {
                                $linkHost = parse_url($record->publication_link, PHP_URL_HOST);
                                $targetHost = parse_url($record->journal?->ojs_base_url ?: config('ojs.base_url'), PHP_URL_HOST);
                                if ($linkHost && $targetHost && strtolower($linkHost) !== strtolower($targetHost)) {
                                    return false;
                                }
                            }
                            return true;
                        })
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

                    Action::make('sync_ojs')
                        ->label('Sinkronkan OJS')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Sinkronisasi Ulang ke OJS')
                        ->modalDescription('Apakah Anda yakin ingin melakukan sinkronisasi ulang data (termasuk DOI jika ada) ke OJS?')
                        ->visible(function (Submission $record) {
                            if (!Auth::user()?->hasRole('super_admin') || $record->status !== 'Approved' || $record->ojs_status !== 'submitted' || $record->review_status === 'processing') {
                                return false;
                            }
                            if (!empty($record->publication_link)) {
                                $linkHost = parse_url($record->publication_link, PHP_URL_HOST);
                                $targetHost = parse_url($record->journal?->ojs_base_url ?: config('ojs.base_url'), PHP_URL_HOST);
                                if ($linkHost && $targetHost && strtolower($linkHost) !== strtolower($targetHost)) {
                                    return false;
                                }
                            }
                            return true;
                        })
                        ->action(function (Submission $record) {
                            try {
                                \App\Services\OjsSubmissionService::submitInBackground($record);
                                Notification::make()
                                    ->title('Sinkronisasi ulang dikirim ke antrean latar belakang')
                                    ->info()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Gagal mengirim sinkronisasi')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('view')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->color('primary')
                        ->url(fn(Submission $record): ?string => SubmissionResource::getUrl('view', ['record' => $record]))
                        ->visible(fn(Submission $record) => $record->review_status !== 'processing'),
                    EditAction::make()
                        ->label(fn(Submission $record): string => $record->status === 'Rejected' ? 'Revise Submission' : 'Edit Submission')
                        ->visible(fn(Submission $record) => $record->review_status !== 'processing'),
                    Action::make('Konfirmasi LOA ke Admin')
                        ->label('Konfirmasi LOA ke Admin')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('primary')
                        ->url(fn(Submission $record) => 'https://wa.me/' . (\App\Models\User::find(1)?->phone ?? '') . '?text=Halo%20Admin%20LOA%2C%20Saya%20ingin%20bertanya%20tentang%20pengajuan%20LOA%20saya%20dengan%20nomor%20registrasi%20' . $record->id)
                        ->openUrlInNewTab()
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi LOA ke Admin')
                        ->modalDescription('PENTING: Harap pastikan data naskah Anda (Judul, Abstrak, dan Penulis) sudah sesuai dan benar sebelum menghubungi Admin. Jika Anda menggunakan sistem ekstraksi otomatis, pastikan hasil ekstraksi di tabel sudah benar. Jika ada kesalahan, Anda dapat memperbaikinya terlebih dahulu melalui tombol Edit.')
                        ->modalSubmitActionLabel('Lanjutkan ke WhatsApp')
                        ->modalCancelActionLabel('Periksa Kembali'),
                    Action::make('download_invoice')
                        ->label('Download Invoice')
                        ->icon('heroicon-o-document-currency-dollar')
                        ->color('primary')
                        ->url(fn(Submission $record) => route('public.invoice.preview', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->visible(fn(Submission $record) => $record->payment_status === 'paid'),
                    Action::make('download_invoice_bulk')
                        ->label('Download Invoice Kolektif')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->url(function (Submission $record) {
                            $bulkPayment = $record->getBulkPayment();
                            return $bulkPayment ? route('public.invoice.bulk.preview', ['payment' => $bulkPayment->id]) : null;
                        })
                        ->openUrlInNewTab()
                        ->visible(fn(Submission $record) => $record->payment_status === 'paid' && $record->getBulkPayment() !== null),
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
                        ->visible(function (Submission $record) {
                            if ($record->status !== 'Approved') {
                                return false;
                            }
                            $ojsUrl = $record->journal?->ojs_base_url;
                            if (!empty($ojsUrl)) {
                                $host = parse_url($ojsUrl, PHP_URL_HOST);
                                if (empty($host)) {
                                    $host = str_replace(['https://', 'http://', '/'], '', $ojsUrl);
                                }
                                if (in_array($host, ['pjlsedu.com', 'ijefijournal.com'])) {
                                    return false;
                                }
                            }
                            return true;
                        }),
                    Action::make('download_pfc')
                        ->label('Download PFC')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->url(fn(Submission $record) => route('public.pfc.preview', ['record' => $record, 'download' => 1]))
                        ->openUrlInNewTab()
                        ->visible(function (Submission $record) {
                            if ($record->status !== 'Approved') {
                                return false;
                            }
                            $ojsUrl = $record->journal?->ojs_base_url;
                            if (!empty($ojsUrl)) {
                                $host = parse_url($ojsUrl, PHP_URL_HOST);
                                if (empty($host)) {
                                    $host = str_replace(['https://', 'http://', '/'], '', $ojsUrl);
                                }
                                if (in_array($host, ['pjlsedu.com', 'ijefijournal.com'])) {
                                    return false;
                                }
                            }
                            return true;
                        }),
                ])
                    ->label('')
                    ->button()
                    ->color('primary')
                    ->icon('heroicon-o-eye'),
            ], position: RecordActionsPosition::BeforeColumns)

            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_pay_qris')
                        ->label('Bayar QRIS Terpilih')
                        ->icon('heroicon-o-credit-card')
                        ->color('primary')
                        ->action(function (Collection $records) {
                            // 1. Check if any selected submission is still processing review
                            $processingRecords = $records->filter(fn(Submission $r) => $r->review_status === 'processing' || empty($r->title));

                            if ($processingRecords->isNotEmpty()) {
                                $processingIds = $processingRecords->pluck('id')->implode(', ');
                                Notification::make()
                                    ->warning()
                                    ->title('Naskah Sedang Dalam Proses Review')
                                    ->body("Terdapat naskah ({$processingIds}) yang masih dalam proses ekstraksi & peninjauan (review). Mohon tunggu hingga proses review selesai sebelum melakukan pembayaran.")
                                    ->persistent()
                                    ->send();
                                return;
                            }

                            // 2. Filter only submissions that are pending LOA and unpaid
                            $unpaidRecords = $records->filter(function (Submission $r) {
                                return $r->status === 'Pending'
                                    && $r->payment_status !== 'paid'
                                    && empty($r->proof_of_payment);
                            });

                            if ($unpaidRecords->isEmpty()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Tidak Ada Naskah yang Memerlukan Pembayaran')
                                    ->body('Seluruh naskah yang dipilih sudah berstatus Approved atau sudah lunas.')
                                    ->send();
                                return;
                            }

                            $ids = $unpaidRecords->pluck('id')->implode(',');
                            return redirect()->to(SubmissionResource::getUrl('payment.bulk') . '?records=' . $ids);
                        }),

                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation(false)
                        ->action(function (Collection $records) {
                            $count = 0;
                            $skipped = 0;
                            $records->each(function (Submission $record) use (&$count, &$skipped) {
                                if ($record->status !== 'Approved') {
                                    if ($record->review_status === 'processing' || empty($record->title)) {
                                        $skipped++;
                                        return;
                                    }
                                    if ($record->proof_of_payment) {
                                        Storage::disk('public')->delete($record->proof_of_payment);
                                    }

                                    // Run OJS Submission in background
                                    try {
                                        \App\Services\OjsSubmissionService::submitInBackground($record);
                                    } catch (\Throwable $e) {
                                        \Illuminate\Support\Facades\Log::warning("OJS integration failed to dispatch background job for submission ID: {$record->id}. Error: {$e->getMessage()}");
                                    }

                                    // Record financial transaction for this submission
                                    \App\Models\FinanceTransaction::recordSubmissionPayment($record, $record->proof_of_payment);

                                    $updateData = [
                                        'status' => 'Approved',
                                        'approved_date' => now(),
                                        'proof_of_payment' => null,
                                    ];
                                    if ($record->review_status === 'failed') {
                                        $updateData['review_status'] = 'N/A';
                                    }
                                    $record->update($updateData);

                                    $count++;
                                }
                            });

                            if ($count > 0) {
                                Notification::make()
                                    ->title($count . ' submissions approved successfully')
                                    ->success()
                                    ->send();
                            }

                            if ($skipped > 0) {
                                Notification::make()
                                    ->title($skipped . ' submissions skipped')
                                    ->body('Beberapa naskah dilewati karena masih dalam proses ekstraksi atau judul naskah kosong.')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->visible(fn() => Auth::user()->hasRole('super_admin')),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->filtersFormWidth('2xl');
    }
}
