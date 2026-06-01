<?php

namespace App\Filament\Resources\PlagiarismChecks;

use App\Filament\Resources\PlagiarismChecks\Pages\ManagePlagiarismChecks;
use App\Models\PlagiarismCheck;
use App\Models\PlagiarismParaphrase;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Enums\RecordActionsPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PlagiarismCheckResource extends Resource
{
    protected static ?string $model = PlagiarismCheck::class;

    protected static string|BackedEnum|null $navigationIcon = 'turnitin-logo';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Cek Plagiasi & Parafrase';

    protected static ?string $pluralLabel = 'Cek Plagiasi & Parafrase';

    protected static ?string $modelLabel = 'Cek Plagiasi & Parafrase';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (!$user)
            return null;

        $count = static::getModel()::where('status', 'failed')
            ->where('user_id', $user->id)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('plagiarismParaphrase');
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            $query->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('status', 'completed');
            });
        } else {
            $query->where('user_id', $user->id);
        }

        return $query->select('*')
            ->selectRaw("(CASE 
                WHEN status = 'failed' THEN 1 
                WHEN status = 'processing' THEN 2 
                WHEN status = 'pending' THEN 3 
                WHEN status = 'completed' THEN 4 
                ELSE 5 END) as sort_priority");
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload Dokumen')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Penerima Hasil')
                            ->helperText('Hasil analisis plagiasi akan dikirimkan ke email ini.')
                            ->email()
                            ->required()
                            ->default(fn() => auth()->user()?->email),

                        FileUpload::make('file_path')
                            ->label('File Naskah (.docx / .pdf)')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'])
                            ->directory('plagiarism-checks')
                            ->preserveFilenames()
                            ->maxSize(10240)
                            ->columnSpanFull(),

                        Placeholder::make('quota_info')
                            ->label('Sisa Kuota Anda')
                            ->content(function () {
                                $user = auth()->user();
                                if ($user?->hasRole('super_admin')) {
                                    return 'Unlimited (Administrator)';
                                }
                                $summary = app(\App\Services\PlagiarismQuotaService::class)->getQuotaSummary($user);
                                return "Hari Ini: {$summary['daily_remaining']} / {$summary['daily_limit']} | Plagiarism Credits: {$summary['additional_credits']}";
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Laporan Editorial')
                    ->tabs([
                        // Tab 1: Hasil Cek Plagiasi
                        Tabs\Tab::make('Hasil Cek Plagiasi')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Informasi Hasil Analisis')
                                    ->schema([
                                        TextEntry::make('title')->label('Judul Naskah')->placeholder('-'),
                                        TextEntry::make('similarity_score')
                                            ->label('Skor Kemiripan')
                                            ->numeric(2)
                                            ->suffix('%')
                                            ->weight('bold')
                                            ->color(fn($state) => match (true) {
                                                $state < 20 => 'success',
                                                $state < 50 => 'warning',
                                                default => 'danger',
                                            }),
                                        TextEntry::make('similarity_category')
                                            ->label('Kategori')
                                            ->badge()
                                            ->color(fn(?string $state): string => match ($state) {
                                                'rendah' => 'success',
                                                'sedang' => 'warning',
                                                'tinggi' => 'danger',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn($state) => ucfirst($state ?? '-')),
                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'pending' => 'gray',
                                                'processing' => 'warning',
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                                default => 'gray',
                                            }),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                                Section::make('Bagian yang Terindikasi Mirip')
                                    ->schema([
                                        RepeatableEntry::make('report_data.highlighted_parts')
                                            ->label('')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextEntry::make('text')->label('Potongan Teks')->markdown()->columnSpanFull(),
                                                        TextEntry::make('source')->label('Kemungkinan Sumber')->placeholder('External Source'),
                                                        TextEntry::make('reason')->label('Keterangan')->placeholder('-'),
                                                    ]),
                                            ])
                                            ->columns(1),
                                    ])
                                    ->columnSpanFull()
                                    ->visible(fn($record) => $record->status === 'completed' && !empty($record->report_data['highlighted_parts'])),

                                Section::make('Informasi Sistem')
                                    ->schema([
                                        TextEntry::make('error_message')
                                            ->label('Pesan Error')
                                            ->formatStateUsing(fn($state) => (config('app.env') === 'local' || env('APP_ENV') === 'local') ? $state : 'Server turnitin sedang high traffic silakan cek ulang dalam beberapa menit dengan menekan tombol "Re-Check"')
                                            ->color('danger')
                                            ->visible(fn($record) => $record->status === 'failed'),
                                        TextEntry::make('created_at')->label('Waktu Pengecekan')->dateTime(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ]),

                        // Tab 2: Hasil Parafrase (Hanya muncul jika sudah selesai diparafrase)
                        Tabs\Tab::make('Hasil Parafrase')
                            ->icon('heroicon-o-sparkles')
                            ->visible(fn($record) => $record->plagiarismParaphrase && $record->plagiarismParaphrase->status === 'completed')
                            ->schema([
                                Section::make('Hasil Parafrase & Optimasi Akademik')
                                    ->description('Rekomendasi penyusunan ulang kalimat dari tim editorial untuk menurunkan persentase kemiripan.')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('similarity_score')
                                                    ->label('Similarity Awal')
                                                    ->numeric(2)
                                                    ->suffix('%')
                                                    ->weight('bold')
                                                    ->color('danger'),
                                                TextEntry::make('plagiarismParaphrase.estimated_new_score')
                                                    ->label('Estimasi Setelah Parafrase')
                                                    ->numeric(2)
                                                    ->suffix('%')
                                                    ->weight('bold')
                                                    ->color('success')
                                                    ->placeholder('Belum diproses'),
                                            ]),

                                        RepeatableEntry::make('plagiarismParaphrase.improvements')
                                            ->label('Rincian Kalimat yang Diparafrase')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextEntry::make('original')
                                                            ->label('Kalimat Asli (Similarity Tinggi)')
                                                            ->color('danger')
                                                            ->markdown(),
                                                        TextEntry::make('improved')
                                                            ->label('Rekomendasi Parafrase Akademik')
                                                            ->color('success')
                                                            ->markdown(),
                                                        TextEntry::make('explanation')
                                                            ->label('Catatan Perubahan')
                                                            ->columnSpanFull()
                                                            ->placeholder('-'),
                                                    ]),
                                            ])
                                            ->columnSpanFull()
                                            ->visible(fn($record) => !empty($record->plagiarismParaphrase?->improvements)),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Informasi Sistem')
                                    ->schema([
                                        TextEntry::make('created_at')->label('Waktu Pengecekan')->dateTime(),
                                        TextEntry::make('plagiarismParaphrase.updated_at')->label('Waktu Parafrase')->dateTime(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Pengaju / Email Penerima Column
                TextColumn::make('email')
                    ->label(fn() => auth()->user()?->hasRole('super_admin') ? 'Pengaju' : 'Email Penerima')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('email', 'like', "%{$search}%")
                            ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable()
                    ->weight(fn() => auth()->user()?->hasRole('super_admin') ? 'bold' : 'normal')
                    ->state(fn(PlagiarismCheck $record) => auth()->user()?->hasRole('super_admin') ? ($record->user?->name ?? 'Guest') : $record->email)
                    ->description(fn(PlagiarismCheck $record) => auth()->user()?->hasRole('super_admin') ? $record->email : null)
                    ->wrap(),

                // 2. Judul Column
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->extraHeaderAttributes(['style' => 'width: 35%; min-width: 250px; max-width: 380px;'])
                    ->extraCellAttributes(['style' => 'max-width: 380px; white-space: normal;'])
                    ->state(fn(PlagiarismCheck $record) => $record)
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $title = $state->title;
                        $code = 'PFC-' . str_pad($state->id, 3, '0', STR_PAD_LEFT);

                        $titleClass = 'text-gray-900 dark:text-white';
                        $isLowOpacity = false;

                        if ($state->status === 'failed') {
                            $filename = !empty($state->file_path) ? basename($state->file_path) : 'Berkas';
                            $title = "Analisis Plagiasi Gagal — ({$filename})";
                            $titleClass = 'text-red-600 dark:text-red-400 italic';
                            $errorMessage = 'Tips: Coba Re-Check setelah beberapa saat...';
                            $code .= " | <span class='text-amber-600 dark:text-amber-400 font-semibold'>{$errorMessage}</span>";
                        } elseif (empty($title)) {
                            if (!empty($state->file_path)) {
                                $title = basename($state->file_path);
                            } else {
                                $title = 'Menunggu berkas dianalisis...';
                            }
                            $isLowOpacity = true;
                        } elseif (in_array($state->status, ['pending', 'processing'])) {
                            $isLowOpacity = true;
                        }

                        $opacityStyle = $isLowOpacity ? 'opacity: 0.55 !important;' : '';

                        return new \Illuminate\Support\HtmlString("
                            <div class='flex flex-col gap-0.5 py-1' style='{$opacityStyle}'>
                                <div class='font-bold text-sm {$titleClass}' 
                                     style='display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; word-break: break-word; line-height: 1.25rem; max-height: 2.5rem;'>
                                    {$title}
                                </div>
                                <span class='text-xs text-gray-500 dark:text-gray-400 font-mono'>{$code}</span>
                            </div>
                        ");
                    }),

                // 3. Similarity Group Column
                ColumnGroup::make('Similarity', [
                    // Awal Sub-column
                    TextColumn::make('similarity_score')
                        ->label('Awal')
                        ->alignment(\Filament\Support\Enums\Alignment::Center)
                        ->state(fn(PlagiarismCheck $record) => $record)
                        ->html()
                        ->formatStateUsing(function ($state) {
                            if ($state->status !== 'completed' || $state->similarity_score === null) {
                                return new \Illuminate\Support\HtmlString('<span class="text-gray-400">—</span>');
                            }
                            $score = number_format($state->similarity_score, 1) . '%';
                            $category = $state->similarity_category ?? PlagiarismCheck::getCategoryForScore($state->similarity_score);

                            $badgeClasses = match ($category) {
                                'rendah' => [
                                    'textClass' => 'text-emerald-600 dark:text-emerald-400',
                                    'badgeClass' => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800/50'
                                ],
                                'sedang' => [
                                    'textClass' => 'text-amber-600 dark:text-amber-400',
                                    'badgeClass' => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50'
                                ],
                                'tinggi' => [
                                    'textClass' => 'text-red-600 dark:text-red-400',
                                    'badgeClass' => 'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800/50'
                                ],
                                default => [
                                    'textClass' => 'text-gray-600 dark:text-gray-400',
                                    'badgeClass' => 'text-gray-700 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-800/30 dark:border-gray-700/50'
                                ]
                            };

                            $label = ucfirst($category);

                            return new \Illuminate\Support\HtmlString("
                                <div class='flex flex-col items-center justify-center gap-1.5 py-1'>
                                    <span class='font-bold text-sm {$badgeClasses['textClass']}'>{$score}</span>
                                    <span class='px-2.5 py-0.5 text-[10px] font-semibold rounded-full border {$badgeClasses['badgeClass']}'>
                                        {$label}
                                    </span>
                                </div>
                            ");
                        }),

                    // Delta Sub-column
                    TextColumn::make('delta')
                        ->label('Δ')
                        ->alignment(\Filament\Support\Enums\Alignment::Center)
                        ->state(fn(PlagiarismCheck $record) => $record)
                        ->html()
                        ->formatStateUsing(function ($state) {
                            $paraphrase = $state->plagiarismParaphrase;
                            if (!$paraphrase || $paraphrase->status !== 'completed' || $paraphrase->estimated_new_score === null) {
                                return new \Illuminate\Support\HtmlString('<span class="text-gray-400 font-medium text-xs">—</span>');
                            }

                            $awal = (float) $state->similarity_score;
                            $estimasi = (float) $paraphrase->estimated_new_score;
                            $diff = $awal - $estimasi;

                            if ($diff > 0) {
                                $formattedDiff = number_format($diff, 1);
                                return new \Illuminate\Support\HtmlString("
                                    <span class='inline-flex items-center gap-0.5 font-semibold text-xs text-emerald-600 dark:text-emerald-400'>
                                        <svg class='w-3.5 h-3.5 mr-0.5' fill='none' stroke='currentColor' viewBox='0 0 24 24' style='display: inline-block; vertical-align: middle;'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 14l-7 7m0 0l-7-7m7 7V3'></path>
                                        </svg>
                                        <span style='vertical-align: middle;'>{$formattedDiff}</span>
                                    </span>
                                ");
                            }

                            return new \Illuminate\Support\HtmlString('<span class="text-gray-400 font-medium text-xs">—</span>');
                        }),

                    // Estimasi Sub-column
                    TextColumn::make('estimated_new_score')
                        ->label('Estimasi')
                        ->alignment(\Filament\Support\Enums\Alignment::Center)
                        ->state(fn(PlagiarismCheck $record) => $record)
                        ->html()
                        ->formatStateUsing(function ($state) {
                            $paraphrase = $state->plagiarismParaphrase;
                            if (!$paraphrase || $paraphrase->status !== 'completed' || $paraphrase->estimated_new_score === null) {
                                return new \Illuminate\Support\HtmlString("
                                    <div class='flex items-center justify-center py-2'>
                                        <span class='text-xs text-gray-400/80 font-medium italic'>Belum parafrase</span>
                                    </div>
                                ");
                            }

                            $score = number_format((float) $paraphrase->estimated_new_score, 1) . '%';
                            $category = PlagiarismCheck::getCategoryForScore((float) $paraphrase->estimated_new_score);

                            $badgeClasses = match ($category) {
                                'rendah' => [
                                    'textClass' => 'text-emerald-600 dark:text-emerald-400',
                                    'badgeClass' => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800/50'
                                ],
                                'sedang' => [
                                    'textClass' => 'text-amber-600 dark:text-amber-400',
                                    'badgeClass' => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50'
                                ],
                                'tinggi' => [
                                    'textClass' => 'text-red-600 dark:text-red-400',
                                    'badgeClass' => 'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800/50'
                                ],
                                default => [
                                    'textClass' => 'text-gray-600 dark:text-gray-400',
                                    'badgeClass' => 'text-gray-700 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-800/30 dark:border-gray-700/50'
                                ]
                            };

                            $label = ucfirst($category);

                            return new \Illuminate\Support\HtmlString("
                                <div class='flex flex-col items-center justify-center gap-1.5 py-1'>
                                    <span class='font-bold text-sm {$badgeClasses['textClass']}'>{$score}</span>
                                    <span class='px-2.5 py-0.5 text-[10px] font-semibold rounded-full border {$badgeClasses['badgeClass']}'>
                                        {$label}
                                    </span>
                                </div>
                            ");
                        }),
                ]),

                // 4. Tanggal Column
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->alignment(\Filament\Support\Enums\Alignment::Center)
                    ->formatStateUsing(fn($record) => $record->created_at->translatedFormat('d M Y'))
                    ->sortable(),
            ])
            ->defaultSort(function (Builder $query) {
                return $query->orderByRaw("(CASE 
                    WHEN status = 'failed' THEN 1 
                    WHEN status = 'processing' THEN 2 
                    WHEN status = 'pending' THEN 3 
                    WHEN status = 'completed' THEN 4 
                    ELSE 5 END) ASC")
                    ->orderBy('created_at', 'desc');
            })
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail')
                        ->icon('heroicon-o-eye')
                        ->color('primary')
                        ->modalWidth('5xl')
                        ->modalAutofocus(false)
                        ->extraModalFooterActions([
                            Action::make('paraphrase_modal')
                                ->label('Parafrase')
                                ->icon('heroicon-o-sparkles')
                                ->color('success')
                                ->requiresConfirmation()
                                ->modalHeading('Mulai Parafrase Akademik')
                                ->modalDescription('Apakah Anda yakin ingin memproses parafrase akademik pada bagian naskah ini? Proses ini hanya bisa dijalankan satu kali per hasil cek plagiasi dan akan memparafrase kalimat yang memiliki similarity tinggi.')
                                ->modalSubmitAction(fn(\Filament\Actions\Action $action) => $action->color('warning'))
                                ->modalIconColor('warning')
                                ->visible(
                                    fn(PlagiarismCheck $record): bool =>
                                    $record->status === 'completed' &&
                                    !empty($record->report_data['highlighted_parts']) &&
                                    ($record->plagiarismParaphrase === null || $record->plagiarismParaphrase->status === 'failed') &&
                                    (!auth()->user()?->hasRole('super_admin') || $record->user_id === auth()->id())
                                )
                                ->action(function (PlagiarismCheck $record) {
                                    try {
                                        $paraphrase = $record->plagiarismParaphrase()->updateOrCreate(
                                            ['plagiarism_check_id' => $record->id],
                                            [
                                                'status' => 'pending',
                                                'original_score' => $record->similarity_score,
                                                'estimated_new_score' => null,
                                                'improvements' => null,
                                                'error_message' => null,
                                            ]
                                        );

                                        $paraphrase->processParaphrase();

                                        \Filament\Notifications\Notification::make()
                                            ->title('Parafrase Berhasil')
                                            ->body('Naskah telah berhasil diparafrase secara akademik. Laporan perbandingan dan estimasi skor baru telah dikirimkan ke email Anda.')
                                            ->success()
                                            ->send();

                                    } catch (\Exception $e) {
                                        $errorMessage = (config('app.env') === 'local' || env('APP_ENV') === 'local')
                                            ? $e->getMessage()
                                            : 'Server parafrase sedang sibuk. Silakan coba kembali beberapa saat lagi.';

                                        \Filament\Notifications\Notification::make()
                                            ->title('Parafrase Gagal')
                                            ->body('Terjadi kesalahan saat memproses parafrase: ' . $errorMessage)
                                            ->danger()
                                            ->send();
                                    }
                                }),
                            Action::make('request_again')
                                ->label('Re-Check')
                                ->color('warning')
                                ->icon('heroicon-o-arrow-path')
                                ->requiresConfirmation()
                                ->visible(
                                    fn(PlagiarismCheck $record): bool =>
                                    $record->status === 'failed' &&
                                    (!auth()->user()?->hasRole('super_admin') || $record->user_id === auth()->id())
                                )
                                ->action(fn(PlagiarismCheck $record, Action $action) => [
                                    $record->processCheck(),
                                    $action->cancel(),
                                ]),
                            DeleteAction::make()
                                ->visible(fn(PlagiarismCheck $record): bool => $record->status === 'failed'),
                        ]),
                    Action::make('paraphrase')
                        ->label('Parafrase')
                        ->icon('heroicon-o-sparkles')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Mulai Parafrase Akademik')
                        ->modalDescription('Apakah Anda yakin ingin memproses parafrase akademik pada bagian naskah ini? Proses ini hanya bisa dijalankan satu kali per hasil cek plagiasi dan akan memparafrase kalimat yang memiliki similarity tinggi.')
                        ->modalSubmitAction(fn(\Filament\Actions\Action $action) => $action->color('warning'))
                        ->modalIconColor('warning')
                        ->visible(
                            fn(PlagiarismCheck $record): bool =>
                            $record->status === 'completed' &&
                            !empty($record->report_data['highlighted_parts']) &&
                            ($record->plagiarismParaphrase === null || $record->plagiarismParaphrase->status === 'failed') &&
                            (!auth()->user()?->hasRole('super_admin') || $record->user_id === auth()->id())
                        )
                        ->action(function (PlagiarismCheck $record) {
                            try {
                                $paraphrase = $record->plagiarismParaphrase()->updateOrCreate(
                                    ['plagiarism_check_id' => $record->id],
                                    [
                                        'status' => 'pending',
                                        'original_score' => $record->similarity_score,
                                        'estimated_new_score' => null,
                                        'improvements' => null,
                                        'error_message' => null,
                                    ]
                                );

                                $paraphrase->processParaphrase();

                                \Filament\Notifications\Notification::make()
                                    ->title('Parafrase Berhasil')
                                    ->body('Naskah telah berhasil diparafrase secara akademik. Laporan perbandingan dan estimasi skor baru telah dikirimkan ke email Anda.')
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                $errorMessage = (config('app.env') === 'local' || env('APP_ENV') === 'local')
                                    ? $e->getMessage()
                                    : 'Server parafrase sedang sibuk. Silakan coba kembali beberapa saat lagi.';

                                \Filament\Notifications\Notification::make()
                                    ->title('Parafrase Gagal')
                                    ->body('Terjadi kesalahan saat memproses parafrase: ' . $errorMessage)
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('request_again_table')
                        ->label('Re-Check')
                        ->color('warning')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->visible(
                            fn(PlagiarismCheck $record): bool =>
                            $record->status === 'failed' &&
                            (!auth()->user()?->hasRole('super_admin') || $record->user_id === auth()->id())
                        )
                        ->action(fn(PlagiarismCheck $record, Action $action) => [
                            $record->processCheck(),
                            $action->cancel(),
                        ]),
                    DeleteAction::make(),
                ])
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->button(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => ManagePlagiarismChecks::route('/'),
        ];
    }
}
