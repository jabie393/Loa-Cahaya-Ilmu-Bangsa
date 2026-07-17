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
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use Filament\Notifications\Notification;

use Livewire\Attributes\Url;

class CreateSubmission extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    #[Url]
    public ?string $ojs_base_url = null;

    protected static string $resource = SubmissionResource::class;

    public function mount(): void
    {
        parent::mount();
        if (empty($this->ojs_base_url)) {
            $this->ojs_base_url = request()->query('ojs_base_url');
        }
    }

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

                                    Select::make('journal_id')
                                        ->label('Jurnal Target')
                                        ->relationship(
                                            name: 'journal',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: function ($query, $livewire) {
                                                $filterUrl = $livewire->ojs_base_url;
                                                if (!empty($filterUrl)) {
                                                    $defaultUrl = config('ojs.base_url');
                                                    if ($filterUrl === 'default_env' || rtrim($filterUrl, '/') === rtrim($defaultUrl, '/')) {
                                                        return $query->where(function ($q) use ($defaultUrl) {
                                                            $q->whereNull('ojs_base_url')
                                                              ->orWhere('ojs_base_url', '')
                                                              ->orWhere('ojs_base_url', $defaultUrl)
                                                              ->orWhere('ojs_base_url', rtrim($defaultUrl, '/'));
                                                        });
                                                    }
                                                    return $query->where('ojs_base_url', $filterUrl);
                                                }
                                                return $query;
                                            }
                                        )
                                        ->columnSpanFull()
                                        ->required(),

                                    Toggle::make('manual_metadata')
                                        ->label('Isi Metadata Secara Manual')
                                        ->helperText('Aktifkan jika Anda ingin mengisi Judul, Abstrak, Penulis, Kata Kunci, Referensi, dan Email secara manual. Jika dinonaktifkan, sistem akan mendeteksi dan mengisinya secara otomatis dari berkas PDF.')
                                        ->default(false)
                                        ->live()
                                        ->columnSpanFull(),

                                    Repeater::make('authors')
                                        ->label('Daftar Penulis & Instansi')
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Nama Penulis (Sesuai EYD)')
                                                ->required(fn(Get $get) => (bool) $get('../../manual_metadata'))
                                                ->placeholder('Nama Lengkap'),
                                            TextInput::make('institution')
                                                ->label('Instansi (Jangan Disingkat)')
                                                ->required(fn(Get $get) => (bool) $get('../../manual_metadata'))
                                                ->placeholder('Nama Instansi / Kampus'),
                                        ])
                                        ->createItemButtonLabel('Tambah Penulis')
                                        ->minItems(1)
                                        ->columns(2)
                                        ->columnSpanFull()
                                        ->default([
                                            ['name' => Auth::user()?->name ?? '', 'institution' => '']
                                        ])
                                        ->visible(fn(Get $get) => (bool) $get('manual_metadata')),

                                    TextInput::make('email')
                                        ->label('Email (Digunakan untuk pengiriman LOA & laporan review)')
                                        ->email()
                                        ->required()
                                        ->default(fn() => Auth::user()?->email)
                                        ->placeholder('email@example.com')
                                        ->columnSpanFull(),

                                    TextInput::make('title')
                                        ->columnSpanFull()
                                        ->label('Judul (Diisi Huruf Besar)')
                                        ->placeholder('Judul Artikel Lengkap')
                                        ->required(fn(Get $get) => (bool) $get('manual_metadata'))
                                        ->visible(fn(Get $get) => (bool) $get('manual_metadata')),

                                    TagsInput::make('keywords')
                                        ->label('Keywords')
                                        ->separator(',')
                                        ->placeholder('Tambah kata kunci')
                                        ->helperText('Tekan enter untuk memisahkan')
                                        ->columnSpanFull()
                                        ->visible(fn(Get $get) => (bool) $get('manual_metadata')),

                                    Textarea::make('abstract')
                                        ->label('Abstract')
                                        ->placeholder('Masukkan Abstrak')
                                        ->columnSpanFull()
                                        ->autosize()
                                        ->maxLength(5000)
                                        ->rules(['string'])
                                        ->required(fn(Get $get) => (bool) $get('manual_metadata'))
                                        ->visible(fn(Get $get) => (bool) $get('manual_metadata')),

                                    Textarea::make('references')
                                        ->label('Referensi / Daftar Pustaka')
                                        ->placeholder('Masukkan daftar pustaka')
                                        ->columnSpanFull()
                                        ->rows(6)
                                        ->visible(fn(Get $get) => (bool) $get('manual_metadata')),
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

    protected function getCreatedNotification(): ?Notification
    {
        $isManual = (bool) ($this->data['manual_metadata'] ?? false);

        if (! $isManual) {
            return Notification::make()
                ->warning()
                ->title('Pengajuan Berhasil Dikirim!')
                ->body('Naskah Anda sedang diproses oleh sistem untuk ekstraksi otomatis. Harap tinjau kembali data Anda (Judul, Abstrak, Penulis) di tabel sebelum melakukan Konfirmasi LOA ke Admin!')
                ->persistent();
        }

        return Notification::make()
            ->success()
            ->title('Pengajuan Berhasil Dikirim!')
            ->body('Data pengajuan Anda telah berhasil disimpan.');
    }
}
