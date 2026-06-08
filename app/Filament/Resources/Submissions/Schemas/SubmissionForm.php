<?php

namespace App\Filament\Resources\Submissions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;

class SubmissionForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('status')
                    ->badge()
                    ->color(fn($record): string => match ($record->status) {
                        'Pending' => 'warning',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                    })
                    ->columnSpanFull(),
                Section::make('Form Submission')
                    ->columnSpan(fn($record) => $record?->status === 'Approved' ? 6 : 4)
                    ->description('Data form submission')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(Auth::user()->id),
                        TagsInput::make('author_name')
                            ->label('Nama Penulis (Ditulis Sesuai EYD)')
                            ->placeholder('Tambah penulis')
                            ->default(Auth::user()?->name ? [Auth::user()->name] : [])
                            ->formatStateUsing(function ($state) {
                                if (is_array($state)) {
                                    return $state;
                                }
                                if (is_string($state) && str_starts_with($state, '[') && str_ends_with($state, ']')) {
                                    $decoded = json_decode($state, true);
                                    if (is_array($decoded)) {
                                        return $decoded;
                                    }
                                }
                                return is_string($state) ? array_map('trim', explode(',', $state)) : [];
                            })
                            ->separator(',')
                            ->required()
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        TextInput::make('email')
                            ->label('Email (Maximal 1 Email, Email ini akan digunakan untuk pengiriman LOA)')
                            ->email()
                            ->required()
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        TextInput::make('institution')
                            ->columnSpanFull()
                            ->label('Instansi/Kampus (Jangan Disingkat)')
                            ->required()
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        Select::make('journal_id')
                            ->label('Jurnal (Pilih Salah satu)')
                            ->relationship('journal', 'name')
                            ->columnSpanFull()
                            ->required()
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        TextInput::make('title')
                            ->columnSpanFull()
                            ->label('Judul (Diisi Huruf Besar)')
                            ->required()
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        Textarea::make('abstract')
                            ->label('Abstract')
                            ->required()
                            ->columnSpanFull()
                            ->autosize()
                            ->maxLength(5000)
                            ->rules(['string'])
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        TagsInput::make('keywords')
                            ->label('Keywords')
                            ->separator(',')
                            ->required()
                            ->placeholder('Tambah kata kunci')
                            ->helperText('Tekan enter untuk memisahkan')
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        Textarea::make('references')
                            ->label('Referensi / Daftar Pustaka')
                            ->placeholder('Masukkan daftar pustaka / referensi artikel (satu per baris)')
                            ->required()
                            ->columnSpanFull()
                            ->rows(6)
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        DatePicker::make('submission_date')
                            ->default(now())
                            ->native(false)
                            ->disabled()
                            ->visible(fn() => Auth::user()?->hasRole('super_admin')),
                        Hidden::make('submission_date')
                            ->default(now())
                            ->visible(fn() => !Auth::user()?->hasRole('super_admin')),

                        Select::make('status')
                            ->options(['Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected'])
                            ->default(fn($record) => $record?->status ?? 'Pending')
                            ->required()
                            ->visible(fn() => Auth::user()?->hasRole('super_admin')),
                        Hidden::make('status')
                            ->afterStateHydrated(function (Set $set, $state, $record) {
                                // If status is Rejected and user is not admin, reset to Pending
                                if ($record?->status === 'Rejected' && !Auth::user()?->hasRole('super_admin')) {
                                    $set('status', 'Pending');
                                }
                            })
                            ->dehydrated(function ($state, $record) {
                                // For non-admin users with rejected submissions, always save as Pending
                                return !Auth::user()?->hasRole('super_admin') && $record?->status === 'Rejected' ? 'Pending' : $state;
                            })
                            ->visible(fn() => !Auth::user()?->hasRole('super_admin')),
                    ])->columns(2),
                Group::make([
                    Section::make('File PDF')
                        ->description('File PDF Fix yang sudah disesuaikan template')
                        ->schema([
                            FileUpload::make('manuscript_file')
                                ->label('Upload File PDF')
                                ->required()
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                ])
                                ->maxSize(20480)
                                ->disk('public')
                                ->directory('manuscripts')
                                ->downloadable()
                                ->preserveFilenames()
                                ->rules(['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'])
                                ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        ]),
                    Section::make('Pembayaran')
                        ->description('Bukti Pembayaran')
                        ->visible(fn($record) => $record?->status !== 'Approved')
                        ->schema([
                            FileUpload::make('proof_of_payment')
                                ->label('Upload Bukti Pembayaran')
                                ->directory('proof-of-payment')
                                ->disk('public')
                                ->image()
                                ->required(),
                        ]),
                ])->columnSpan(fn($record) => $record?->status === 'Approved' ? 6 : 2),
            ])->columns(6);
    }
}
