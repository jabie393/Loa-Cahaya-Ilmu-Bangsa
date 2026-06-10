<?php

namespace App\Filament\Resources\Submissions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Repeater;
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
                        Repeater::make('authors')
                            ->label('Daftar Penulis & Instansi')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Penulis (Sesuai EYD)')
                                    ->required()
                                    ->placeholder('Nama Lengkap'),
                                TextInput::make('institution')
                                    ->label('Instansi (Jangan Disingkat)')
                                    ->required()
                                    ->placeholder('Nama Instansi / Kampus'),
                            ])
                            ->createItemButtonLabel('Tambah Penulis')
                            ->minItems(1)
                            ->columns(2)
                            ->columnSpanFull()
                            ->default([
                                ['name' => Auth::user()?->name ?? '', 'institution' => '']
                            ])
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if (empty($state) && $record && !empty($record->author_name)) {
                                    $names = is_array($record->author_name)
                                        ? $record->author_name
                                        : array_map('trim', explode(',', $record->author_name));

                                    $state = [];
                                    foreach ($names as $name) {
                                        $state[] = ['name' => $name, 'institution' => ''];
                                    }
                                    $component->state($state);
                                }
                            })
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        TextInput::make('email')
                            ->label('Email (Maximal 1 Email, Email ini akan digunakan untuk pengiriman LOA)')
                            ->email()
                            ->required()
                            ->placeholder('email@example.com')
                            ->columnSpanFull()
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
                            ->placeholder('Judul Artikel')
                            ->required()
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        TagsInput::make('keywords')
                            ->label('Keywords')
                            ->separator(',')
                            ->required()
                            ->placeholder('Tambah kata kunci')
                            ->helperText('Tekan enter untuk memisahkan')
                            ->columnSpanFull()
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        Textarea::make('abstract')
                            ->label('Abstract')
                            ->placeholder('Masukkan Abstrak')
                            ->required()
                            ->columnSpanFull()
                            ->autosize()
                            ->maxLength(5000)
                            ->rules(['string'])
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        Textarea::make('references')
                            ->label('Referensi / Daftar Pustaka')
                            ->placeholder('Masukkan daftar pustaka / referensi artikel (satu per baris)')
                            ->required()
                            ->columnSpanFull()
                            ->rows(6)
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        TextInput::make('publication_link')
                            ->label('Link Publikasi')
                            ->url()
                            ->placeholder('Link Publikasi Artikel')
                            ->columnSpanFull()
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && $record?->status !== 'Approved'),

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
