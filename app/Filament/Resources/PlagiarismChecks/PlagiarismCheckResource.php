<?php

namespace App\Filament\Resources\PlagiarismChecks;

use App\Filament\Resources\PlagiarismChecks\Pages\ManagePlagiarismChecks;
use App\Models\PlagiarismCheck;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
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

    protected static ?string $navigationLabel = 'Cek Plagiasi';

    protected static ?string $pluralLabel = 'Cek Plagiasi';

    protected static ?string $modelLabel = 'Cek Plagiasi';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload Dokumen')
                    ->description('Unggah file jurnal (DOCX atau PDF) untuk dianalisis tingkat plagiasinya.')
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
                            ->label('Status Kuota Cek Plagiasi')
                            ->content(function () {
                                $user = auth()->user();
                                if ($user?->hasRole('super_admin')) {
                                    return 'Unlimited (Administrator)';
                                }
                                $summary = app(\App\Services\PlagiarismQuotaService::class)->getQuotaSummary($user);
                                return "Sisa Hari Ini: {$summary['daily_remaining']} / {$summary['daily_limit']} | Kredit Tambahan: {$summary['additional_credits']}";
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                            ->color(fn(string $state): string => match ($state) {
                                'rendah' => 'success',
                                'sedang' => 'warning',
                                'tinggi' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn($state) => ucfirst($state)),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Username')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => auth()->user()?->hasRole('super_admin')),
                TextColumn::make('email')
                    ->label('Email Penerima')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul Naskah')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('Processing...'),
                TextColumn::make('similarity_score')
                    ->label('Skor')
                    ->numeric(1)
                    ->suffix('%')
                    ->sortable()
                    ->color(fn($state) => match (true) {
                        $state < 20 => 'success',
                        $state < 50 => 'warning',
                        $state === null => 'gray',
                        default => 'danger',
                    }),
                TextColumn::make('similarity_category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'rendah' => 'success',
                        'sedang' => 'warning',
                        'tinggi' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state ?? '-')),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail')
                        ->modalWidth('5xl')
                        ->modalAutofocus(false)
                        ->extraModalFooterActions([
                            Action::make('request_again')
                                ->label('Re-Check')
                                ->color('warning')
                                ->icon('heroicon-o-arrow-path')
                                ->requiresConfirmation()
                                ->visible(fn(PlagiarismCheck $record): bool => $record->status === 'failed')
                                ->action(fn(PlagiarismCheck $record, Action $action) => [
                                    $record->processCheck(),
                                    $action->cancel(),
                                ]),
                            DeleteAction::make()
                                ->visible(fn(PlagiarismCheck $record): bool => $record->status === 'failed'),
                        ]),
                    Action::make('request_again_table')
                        ->label('Re-Check')
                        ->color('warning')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->visible(fn(PlagiarismCheck $record): bool => $record->status === 'failed')
                        ->action(fn(PlagiarismCheck $record) => $record->processCheck()),
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
