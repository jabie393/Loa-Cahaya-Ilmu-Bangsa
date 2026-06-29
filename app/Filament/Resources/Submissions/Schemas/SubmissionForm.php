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
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Grid;

class SubmissionForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Tampilan Hasil Review (Hanya muncul jika record ada hasil review-nya)
                Section::make('Hasil Review Naskah')
                    ->description('Rekomendasi perbaikan dari Reviewer berdasarkan draf naskah Anda.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('review_status')
                                    ->label('Status Review')
                                    ->formatStateUsing(fn ($state) => strtoupper($state))
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('review_email_sent_at')
                                    ->label('Laporan Review Terkirim')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                        Textarea::make('general_suggestions')
                            ->label('Saran Perbaikan Umum')
                            ->rows(4)
                            ->disabled()
                            ->dehydrated(false),
                        Section::make('Detail Penilaian Struktur')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Textarea::make('structure_review')->label('Struktur Naskah')->rows(3)->disabled()->dehydrated(false),
                                Textarea::make('abstract_review')->label('Analisis Abstrak')->rows(3)->disabled()->dehydrated(false),
                                Textarea::make('introduction_review')->label('Analisis Pendahuluan')->rows(3)->disabled()->dehydrated(false),
                                Textarea::make('method_review')->label('Analisis Metode')->rows(3)->disabled()->dehydrated(false),
                                Textarea::make('results_review')->label('Analisis Hasil & Pembahasan')->rows(3)->disabled()->dehydrated(false),
                                Textarea::make('conclusion_review')->label('Analisis Kesimpulan')->rows(3)->disabled()->dehydrated(false),
                                Textarea::make('bibliography_review')->label('Analisis Daftar Pustaka')->rows(3)->disabled()->dehydrated(false),
                            ])
                    ])
                    ->visible(fn ($record) => $record !== null && $record->review_status === 'reviewed')
                    ->columnSpanFull(),

                // 2. Formulir Utama Pengisian (Bentuk Kolom Kiri untuk Detail Pengajuan)
                Section::make('Form Submission')
                    ->columnSpan(fn($record) => $record?->status === 'Approved' ? 6 : 4)
                    ->description('Lengkapi data pengajuan dan unggah bukti pembayaran di bawah ini')
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
                            ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        TextInput::make('email')
                            ->label('Email (Digunakan untuk pengiriman LOA & laporan review)')
                            ->email()
                            ->required()
                            ->default(fn() => Auth::user()?->email)
                            ->placeholder('email@example.com')
                            ->columnSpanFull()
                            ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        Select::make('journal_id')
                            ->label('Jurnal Target')
                            ->relationship('journal', 'name')
                            ->columnSpanFull()
                            ->required()
                            ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        TextInput::make('title')
                            ->columnSpanFull()
                            ->label('Judul (Diisi Huruf Besar)')
                            ->placeholder('Judul Artikel Lengkap')
                            ->required()
                            ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        TagsInput::make('keywords')
                            ->label('Keywords')
                            ->separator(',')
                            ->required()
                            ->placeholder('Tambah kata kunci')
                            ->helperText('Tekan enter untuk memisahkan')
                            ->columnSpanFull()
                            ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        Textarea::make('abstract')
                            ->label('Abstract')
                            ->placeholder('Masukkan Abstrak')
                            ->required()
                            ->columnSpanFull()
                            ->autosize()
                            ->maxLength(5000)
                            ->rules(['string'])
                            ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        Textarea::make('references')
                            ->label('Referensi / Daftar Pustaka')
                            ->placeholder('Masukkan daftar pustaka')
                            ->required()
                            ->columnSpanFull()
                            ->rows(6)
                            ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        TextInput::make('publication_link')
                            ->label('Link Publikasi')
                            ->url()
                            ->placeholder('Link Publikasi Artikel')
                            ->columnSpanFull()
                            ->disabled(fn($record) => !Auth::user()?->hasRole('super_admin') && $record?->status !== 'Approved')
                            ->visible(fn($record) => $record !== null),

                        DatePicker::make('submission_date')
                            ->default(now())
                            ->native(false)
                            ->disabled()
                            ->visible(fn() => Auth::user()?->hasRole('super_admin') && !request()->routeIs('*.create')),

                        Select::make('status')
                            ->options(['Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected'])
                            ->default(fn($record) => $record?->status ?? 'Pending')
                            ->required()
                            ->visible(fn() => Auth::user()?->hasRole('super_admin') && !request()->routeIs('*.create')),
                    ])->columns(2),

                // 3. Kolom Kanan untuk File & Bukti Pembayaran
                Group::make([
                    Section::make('File Naskah')
                        ->description('Unggah draf naskah Anda (.pdf)')
                        ->schema([
                            FileUpload::make('manuscript_file')
                                ->label('Upload File')
                                ->required()
                                ->acceptedFileTypes([
                                    'application/pdf',
                                ])
                                ->maxSize(20480)
                                ->disk('public')
                                ->directory('manuscripts')
                                ->downloadable()
                                ->preserveFilenames()
                                ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        ]),
                    Section::make('Pembayaran')
                        ->description('Bukti Pembayaran LOA')
                        ->visible(fn($record) => $record === null || $record->status !== 'Approved')
                        ->schema([
                             FileUpload::make('proof_of_payment')
                                 ->label('Upload Bukti Pembayaran')
                                 ->directory('proof-of-payment')
                                 ->disk('public')
                                 ->image()
                                 ->required(fn($record) => $record === null || $record->status === 'Pending'),
                             Placeholder::make('qris_image')
                                 ->label('QRIS Pembayaran')
                                 ->content(new HtmlString('<div class="flex flex-col items-center justify-center p-3 bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-800"><img src="' . asset('assets/qris.jpg') . '" alt="QRIS" class="w-full max-w-xs rounded-lg shadow-sm" style="max-height: 250px; object-fit: contain;" /><span class="text-xs text-gray-500 dark:text-gray-400 mt-2">Scan QRIS di atas untuk melakukan pembayaran</span></div>')),
                         ]),
                    Section::make('Status Pengajuan')
                        ->description('Status pemrosesan artikel Anda')
                        ->visible(fn($record) => $record !== null)
                        ->schema([
                            TextInput::make('status')
                                ->label('Status LOA')
                                ->formatStateUsing(fn ($state) => strtoupper($state ?? 'DRAFT'))
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('review_status')
                                ->label('Status Review')
                                ->formatStateUsing(function ($state, $record) {
                                    $reviewStatus = $state;
                                    if ($reviewStatus !== 'reviewed' && $record) {
                                        if ($record->status === 'Approved' || !empty($record->ojs_status)) {
                                            $reviewStatus = 'N/A';
                                        } elseif (empty($reviewStatus)) {
                                            $reviewStatus = 'pending';
                                        }
                                    }
                                    return strtoupper($reviewStatus ?? 'PENDING');
                                })
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('ojs_status')
                                ->label('Status OJS')
                                ->formatStateUsing(fn ($state) => strtoupper($state ?? 'NOT SENT'))
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                ])->columnSpan(fn($record) => $record?->status === 'Approved' ? 6 : 2),
            ])->columns(6);
    }
}
