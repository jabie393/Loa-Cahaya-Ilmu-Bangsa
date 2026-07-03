<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;

class CreateSubmission extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = SubmissionResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Form LOA')
                ->schema([
                    Grid::make(6)
                        ->schema([
                            Section::make('Form Submission')
                                ->columnSpan(4)
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
                                        ]),

                                    TextInput::make('email')
                                        ->label('Email (Digunakan untuk pengiriman LOA & laporan review)')
                                        ->email()
                                        ->required()
                                        ->default(fn() => Auth::user()?->email)
                                        ->placeholder('email@example.com')
                                        ->columnSpanFull(),

                                    Select::make('journal_id')
                                        ->label('Jurnal Target')
                                        ->relationship(
                                            name: 'journal',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: function ($query) {
                                                $filterUrl = request()->query('ojs_base_url');
                                                if (!empty($filterUrl)) {
                                                    return $query->where('ojs_base_url', $filterUrl);
                                                }
                                                return $query;
                                            }
                                        )
                                        ->columnSpanFull()
                                        ->required(),

                                    TextInput::make('title')
                                        ->columnSpanFull()
                                        ->label('Judul (Diisi Huruf Besar)')
                                        ->placeholder('Judul Artikel Lengkap')
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
                                        ->placeholder('Masukkan Abstrak')
                                        ->required()
                                        ->columnSpanFull()
                                        ->autosize()
                                        ->maxLength(5000)
                                        ->rules(['string']),

                                    Textarea::make('references')
                                        ->label('Referensi / Daftar Pustaka')
                                        ->placeholder('Masukkan daftar pustaka')
                                        ->required()
                                        ->columnSpanFull()
                                        ->rows(6),
                                ])->columns(2),

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
                                            ->preserveFilenames(),
                                    ]),
                                Section::make('Pembayaran')
                                    ->description('Bukti Pembayaran LOA')
                                    ->schema([
                                        FileUpload::make('proof_of_payment')
                                            ->label('Upload Bukti Pembayaran')
                                            ->directory('proof-of-payment')
                                            ->disk('public')
                                            ->image()
                                            ->required(),
                                        Placeholder::make('qris_image')
                                            ->label('QRIS Pembayaran')
                                            ->content(new HtmlString('<div class="flex flex-col items-center justify-center p-3 bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-800"><img src="' . asset('assets/qris.jpg') . '" alt="QRIS" class="w-full max-w-xs rounded-lg shadow-sm" style="max-height: 250px; object-fit: contain;" /><span class="text-xs text-gray-500 dark:text-gray-400 mt-2">Scan QRIS di atas untuk melakukan pembayaran</span></div>')),
                                    ]),
                            ])->columnSpan(2),
                        ])
                ]),

            Step::make('Konfirmasi')
                ->schema([
                    View::make('filament.pages.Confirmation'),
                    Checkbox::make('agreement')
                        ->label('LoA Berlaku Jika Dilengkapi Bukti Pembayaran dan Link Terbitan, Dengan ini saya bersedia naskah saya ditarik apabila dikemudian hari terdapat kecurangan dalam pengerjaannya')
                        ->accepted()
                        ->dehydrated(false),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $data['user_id'] = $user->id;
        $data['status'] = 'Draft';
        $data['review_status'] = 'processing';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Jalankan proses review di latar belakang setelah data disimpan
        $this->record->processReviewInBackground();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
