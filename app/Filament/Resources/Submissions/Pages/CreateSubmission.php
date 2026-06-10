<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\Action;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;


class CreateSubmission extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = SubmissionResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Form LOA')
                ->schema([
                    Section::make('Informasi Penulis')
                        ->columnSpan(4)
                        ->description('Informasi Penulis')
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
                                }),
                            TextInput::make('email')
                                ->label('Email (Maximal 1 Email, Email ini akan digunakan untuk pengiriman LOA)')
                                ->default(Auth::user()->email)
                                ->email()
                                ->required()
                                ->placeholder('email@example.com')
                                ->columnSpanFull(),

                            Select::make('journal_id')
                                ->label('Jurnal (Pilih Salah satu)')
                                ->relationship('journal', 'name')
                                ->default(request()->query('journal_id'))
                                ->columnSpanFull()
                                ->required(),
                            TextInput::make('title')
                                ->columnSpanFull()
                                ->label('Judul (Diisi Huruf Besar)')
                                ->placeholder('Judul Artikel')
                                ->required(),
                            TagsInput::make('keywords')
                                ->label('Keywords')
                                ->separator(',')
                                ->required()
                                ->placeholder('Tambah kata kunci')
                                ->helperText('Tekan enter untuk memisahkan')
                                ->columnSpanFull(),
                            Textarea::make('abstract')
                                ->label('Abstract')
                                ->required()
                                ->placeholder('Masukkan Abstrak')
                                ->columnSpanFull()
                                ->autosize()
                                ->maxLength(5000)
                                ->rules(['string']),
                            Textarea::make('references')
                                ->label('Referensi / Daftar Pustaka')
                                ->placeholder('Masukkan daftar pustaka / referensi artikel (satu per baris)')
                                ->required()
                                ->columnSpanFull()
                                ->rows(6),

                            Hidden::make('submission_date')
                                ->default(now()),

                            Hidden::make('status')
                                ->default('Pending'),
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
                                    ->rules(['required', 'file', 'mimes:pdf,doc,docx', 'max:20480']),
                            ]),
                        Section::make('Pembayaran')
                            ->description('Bukti Pembayaran')
                            ->schema([
                                FileUpload::make('proof_of_payment')
                                    ->label('Upload Bukti Pembayaran')
                                    ->directory('proof-of-payment')
                                    ->required()
                                    ->disk('public')
                                    ->image()
                            ]),
                    ])->columnSpan(2),
                ])->columns(6),
            Step::make('Konfirmasi')
                ->schema([
                    View::make('filament.pages.Confirmation'),
                    Checkbox::make('agreement')
                        ->label('LoA Berlaku Jika Dilengkapi Bukti Pembayaran dan Link Terbitan, Dengan ini saya bersedia naskah saya ditarik apabila dikemudian hari terdapat kecurangan dalam pengerjaannya')
                        ->accepted(),
                ]),
        ];
    }
}
