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
use Filament\Schemas\Components\Grid;
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
                Section::make('Informasi Pengajuan')
                    ->description('Lengkapi data penulis dan pilih jurnal target untuk memulai penelaahan awal.')
                    ->schema([
                        TextInput::make('author_name')
                            ->label('Nama Penulis (Di Isi Semua, Pisahkan dengan Tanda Koma)')
                            ->helperText('Masukkan nama penulis pertama dan rekan penulis jika ada.')
                            ->default(fn() => auth()->user()?->name)
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email Korespondensi')
                            ->helperText('Hasil review akan dikirimkan secara otomatis ke alamat email ini.')
                            ->default(fn() => auth()->user()?->email)
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Select::make('journal_id')
                            ->label('Target Jurnal / Nama Jurnal')
                            ->relationship('journal', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                    ])
                    ->columnSpan(4),

                Section::make('Dokumen Jurnal')
                    ->description('Unggah naskah Anda dalam format Word.')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('File Naskah (.docx)')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'])
                            ->directory('pre-reviews')
                            ->preserveFilenames()
                            ->maxSize(10240)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2),

                Section::make('Hasil Review')
                    ->description('Bagian ini akan terisi secara otomatis oleh sistem setelah Anda menyimpan data.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Jurnal yang Terdeteksi')
                            ->disabled()
                            ->placeholder('Akan terisi otomatis...'),
                        Textarea::make('general_suggestions')
                            ->label('Ringkasan Saran Revisi')
                            ->rows(5)
                            ->disabled()
                            ->placeholder('Akan terisi otomatis...'),
                    ])
                    ->columnSpanFull()
                    ->visible(fn($record) => $record !== null),
            ])->columns(6);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengajuan')
                    ->schema([
                        TextEntry::make('author_name')->label('Nama Author'),
                        TextEntry::make('email')->label('Email Author'),
                        TextEntry::make('journal.name')->label('Jurnal Tujuan')->placeholder('-'),
                        TextEntry::make('title')->label('Judul Naskah')->placeholder('-'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
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
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

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
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make('Informasi Sistem')
                    ->schema([
                        TextEntry::make('error_message')
                            ->label('Pesan Error Terakhir')
                            ->color('danger')
                            ->visible(fn($record) => $record->status === 'failed'),
                        TextEntry::make('created_at')->label('Dibuat')->dateTime(),
                        TextEntry::make('updated_at')->label('Pembaruan Terakhir')->dateTime(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn($record) => $record->status === 'failed' || $record->status === 'processing'),
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
                    ->color(fn(string $state): string => match ($state) {
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
                ViewAction::make()
                    ->modalWidth('7xl'),
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
