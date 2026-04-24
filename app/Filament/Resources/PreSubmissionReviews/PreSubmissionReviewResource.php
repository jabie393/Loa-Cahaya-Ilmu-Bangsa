<?php

namespace App\Filament\Resources\PreSubmissionReviews;

use App\Filament\Resources\PreSubmissionReviews\Pages\ManagePreSubmissionReviews;
use App\Models\PreSubmissionReview;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class PreSubmissionReviewResource extends Resource
{
    protected static ?string $model = PreSubmissionReview::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-magnifying-glass';
    
    protected static ?string $navigationLabel = 'Review Pra OJS';
    
    protected static ?string $pluralLabel = 'Review Pra OJS';
    
    protected static ?string $modelLabel = 'Review Pra OJS';

    protected static ?string $recordTitleAttribute = 'author_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Author & Jurnal')
                    ->description('Lengkapi data berikut untuk memulai review otomatis.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('author_name')
                            ->label('Nama Lengkap Author')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email Author')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Select::make('journal_id')
                            ->label('Target Jurnal')
                            ->relationship('journal', 'name')
                            ->searchable()
                            ->preload(),
                        FileUpload::make('file_path')
                            ->label('File Jurnal (.docx)')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'])
                            ->directory('pre-reviews')
                            ->preserveFilenames()
                            ->maxSize(10240),
                    ]),
                
                Section::make('Hasil Review AI')
                    ->description('Hasil review akan terisi otomatis setelah proses selesai.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Jurnal (Dideteksi AI)')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('general_suggestions')
                            ->label('Saran Revisi Umum')
                            ->rows(5)
                            ->disabled(),
                    ])
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pengajuan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('author_name')->label('Nama Author'),
                        TextEntry::make('email')->label('Email Author'),
                        TextEntry::make('journal.name')->label('Jurnal Tujuan')->placeholder('-'),
                        TextEntry::make('title')->label('Judul Naskah')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'processing' => 'warning',
                                'reviewed' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('email_sent_at')
                            ->label('Email Terkirim Pada')
                            ->dateTime()
                            ->placeholder('Belum terkirim'),
                    ]),

                Section::make('Hasil Analisis Reviewer')
                    ->schema([
                        TextEntry::make('structure_review')->label('Kelengkapan Struktur')->markdown(),
                        TextEntry::make('abstract_review')->label('Analisis Abstrak')->markdown(),
                        TextEntry::make('introduction_review')->label('Analisis Pendahuluan')->markdown(),
                        TextEntry::make('method_review')->label('Analisis Metode')->markdown(),
                        TextEntry::make('results_review')->label('Hasil & Pembahasan')->markdown(),
                        TextEntry::make('conclusion_review')->label('Kesimpulan')->markdown(),
                        TextEntry::make('bibliography_review')->label('Daftar Pustaka')->markdown(),
                        TextEntry::make('general_suggestions')
                            ->label('Saran Revisi Umum')
                            ->markdown()
                            ->weight('bold')
                            ->color(Color::Amber),
                    ]),
                
                Section::make('Informasi Sistem')
                    ->schema([
                        TextEntry::make('error_message')
                            ->label('Pesan Error (Jika Ada)')
                            ->color('danger')
                            ->visible(fn ($record) => $record->status === 'failed'),
                    ])->visible(fn ($record) => $record->status === 'failed'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('author_name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('journal.name')
                    ->label('Jurnal')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'reviewed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('email_sent_at')
                    ->label('Terkirim')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Pending'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePreSubmissionReviews::route('/'),
        ];
    }
}
