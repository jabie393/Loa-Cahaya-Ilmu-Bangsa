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
                    Section::make('Form Submission')
                        ->description('Lengkapi data pengajuan di bawah ini untuk memproses Letter of Acceptance (LOA)')
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
                                ->required(),

                            FileUpload::make('manuscript_file')
                                ->label('File Naskah PDF yang telah disesuaikan Template')
                                ->required()
                                ->acceptedFileTypes([
                                    'application/pdf',
                                ])
                                ->maxSize(20480)
                                ->disk('public')
                                ->directory('manuscripts')
                                ->downloadable()
                                ->preserveFilenames(),

                            FileUpload::make('proof_of_payment')
                                ->label('Upload Bukti Pembayaran')
                                ->directory('proof-of-payment')
                                ->disk('public')
                                ->image()
                                ->required(),

                            Placeholder::make('qris_image')
                                ->label('QRIS Pembayaran')
                                ->content(new HtmlString('<div class="flex flex-col items-center justify-center p-3 bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-800"><img src="' . asset('assets/qris.jpg') . '" alt="QRIS" class="w-full max-w-xs rounded-lg shadow-sm" style="max-height: 250px; object-fit: contain;" /><span class="text-xs text-gray-500 dark:text-gray-400 mt-2">Scan QRIS di atas untuk melakukan pembayaran</span></div>')),

                            TextInput::make('email')
                                ->label('Email Korespondensi (Penerima LOA)')
                                ->email()
                                ->required()
                                ->default(fn() => Auth::user()?->email)
                                ->placeholder('email@example.com'),
                        ]),
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
        return Notification::make()
            ->warning()
            ->title('Pengajuan Berhasil Dikirim!')
            ->body('Naskah Anda sedang diproses oleh sistem untuk ekstraksi otomatis. Harap tinjau kembali data Anda (Judul, Abstrak, Penulis) di tabel sebelum melakukan Konfirmasi LOA ke Admin!')
            ->persistent();
    }
}
