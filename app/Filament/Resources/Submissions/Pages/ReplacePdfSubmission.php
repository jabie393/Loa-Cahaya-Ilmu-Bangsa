<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\Submission;
use App\Services\GeminiReviewService;
use App\Services\MidtransQrisService;
use App\Services\SubmissionPricingService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class ReplacePdfSubmission extends Page implements HasForms
{
    use InteractsWithRecord;
    use InteractsWithForms;

    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submissions.pages.replace-pdf-submission';

    public ?array $data = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $currentUser = Auth::user();
        if ($this->record->user_id !== $currentUser->id && !$currentUser->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        if ($this->record->ojs_status !== 'submitted') {
            Notification::make()
                ->title('Fitur Tidak Tersedia')
                ->body('Penggantian PDF hanya tersedia jika status naskah di OJS adalah Submitted.')
                ->warning()
                ->send();
            $this->redirect(SubmissionResource::getUrl('view', ['record' => $this->record]));
            return;
        }

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('new_manuscript_file')
                    ->label('Upload File PDF Naskah Baru')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('temp_replace_pdf')
                    ->preserveFilenames()
                    ->required()
                    ->helperText('Format PDF saja. Pastikan naskah sesuai dengan template jurnal yang ditentukan dan jumlah penulis sama dengan naskah awal.'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();
        $rawFile = $state['new_manuscript_file'] ?? null;

        if (is_array($rawFile)) {
            $rawFile = reset($rawFile);
        }

        if (!$rawFile) {
            Notification::make()
                ->title('File Tidak Ditemukan')
                ->body('Mohon unggah file PDF naskah baru terlebih dahulu.')
                ->danger()
                ->send();
            return;
        }

        $relativePublicPath = null;
        $fullPath = null;

        if (is_object($rawFile) && method_exists($rawFile, 'getRealPath')) {
            $originalName = method_exists($rawFile, 'getClientOriginalName') ? $rawFile->getClientOriginalName() : 'manuscript.pdf';
            $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);

            if (method_exists($rawFile, 'storeAs')) {
                $relativePublicPath = $rawFile->storeAs('temp_replace_pdf', $safeName, 'public');
            } else {
                $relativePublicPath = 'temp_replace_pdf/' . $safeName;
                Storage::disk('public')->put($relativePublicPath, file_get_contents($rawFile->getRealPath()));
            }
            $fullPath = Storage::disk('public')->path($relativePublicPath);
        } elseif (is_string($rawFile)) {
            if (Storage::disk('public')->exists($rawFile)) {
                $relativePublicPath = $rawFile;
                $fullPath = Storage::disk('public')->path($rawFile);
            } elseif (file_exists(storage_path('app/public/' . $rawFile))) {
                $relativePublicPath = $rawFile;
                $fullPath = storage_path('app/public/' . $rawFile);
            } elseif (file_exists($rawFile)) {
                $fullPath = $rawFile;
                $safeName = time() . '_' . basename($rawFile);
                $relativePublicPath = 'temp_replace_pdf/' . $safeName;
                Storage::disk('public')->put($relativePublicPath, file_get_contents($fullPath));
                $fullPath = Storage::disk('public')->path($relativePublicPath);
            } elseif (Storage::disk('local')->exists($rawFile)) {
                $safeName = time() . '_' . basename($rawFile);
                $relativePublicPath = 'temp_replace_pdf/' . $safeName;
                Storage::disk('public')->put($relativePublicPath, Storage::disk('local')->get($rawFile));
                $fullPath = Storage::disk('public')->path($relativePublicPath);
            }
        }

        if (!$relativePublicPath || !$fullPath || !file_exists($fullPath)) {
            Notification::make()
                ->title('File Tidak Ditemukan')
                ->body('Berkas PDF tidak dapat diakses pada server. Silakan coba unggah ulang.')
                ->danger()
                ->send();
            return;
        }

        // 1. Validasi Kesesuaian Template Jurnal
        $journal = $this->record->journal;
        if ($journal) {
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($fullPath);
                $pages = $pdf->getPages();
                if (empty($pages)) {
                    throw new \Exception('Berkas PDF kosong atau rusak.');
                }

                $firstPageText = $pages[0]->getText();
                if (empty(trim($firstPageText))) {
                    throw new \Exception('Teks naskah tidak dapat terbaca. Pastikan Anda mengunggah naskah digital (bukan hasil scan/foto) yang disalin ke template.');
                }

                $text = strtolower($firstPageText);
                $slug = strtolower($journal->slug ?? '');
                $name = strtolower($journal->name ?? '');
                $nameParts = explode(':', $journal->name ?? '');
                $firstWord = strtolower(trim($nameParts[0] ?? ''));

                if (!empty($journal->identifier)) {
                    $dbKeywords = array_map('trim', explode(',', $journal->identifier));
                    $missingKeywords = [];

                    foreach ($dbKeywords as $kw) {
                        if (!empty($kw) && !str_contains($text, strtolower($kw))) {
                            $missingKeywords[] = $kw;
                        }
                    }

                    if (!empty($missingKeywords)) {
                        throw new \Exception("Pastikan naskah artikel disesuaikan dengan template {$journal->name} yang sudah disediakan.");
                    }
                } else {
                    $found = false;
                    if (!empty($slug) && str_contains($text, $slug)) $found = true;
                    if (!empty($name) && str_contains($text, $name)) $found = true;
                    if (!empty($firstWord) && str_contains($text, $firstWord)) $found = true;

                    if (!$found) {
                        throw new \Exception("Pastikan naskah artikel disesuaikan dengan template {$journal->name} yang sudah disediakan.");
                    }
                }
            } catch (\Throwable $e) {
                if (Storage::disk('public')->exists($relativePublicPath)) {
                    Storage::disk('public')->delete($relativePublicPath);
                }
                Notification::make()
                    ->title('Format Template Tidak Sesuai')
                    ->body($e->getMessage())
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }
        }

        // 2. Validasi Jumlah Penulis
        $pricingService = app(SubmissionPricingService::class);
        $currentAuthorCount = $pricingService->getAuthorCount($this->record);

        try {
            $reviewService = app(GeminiReviewService::class);
            $extracted = $reviewService->extractMetadataFromFile($relativePublicPath, $this->record->isExternal());
            $newAuthors = $extracted['detected_authors'] ?? [];
            $newAuthorCount = count(array_filter($newAuthors, fn($a) => !empty(trim($a['name'] ?? ''))));
            if ($newAuthorCount === 0) {
                $newAuthorCount = 1;
            }
        } catch (\Throwable $e) {
            $newAuthorCount = $currentAuthorCount;
        }

        if ($newAuthorCount !== $currentAuthorCount) {
            if (Storage::disk('public')->exists($relativePublicPath)) {
                Storage::disk('public')->delete($relativePublicPath);
            }
            Notification::make()
                ->title('Perubahan Jumlah Penulis Ditolak')
                ->body("Jumlah penulis pada file PDF baru terdeteksi {$newAuthorCount} orang, sedangkan naskah awal memiliki {$currentAuthorCount} orang. Fitur Ganti PDF tidak mengizinkan penambahan atau pengurangan jumlah penulis.")
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        // 3. Buat Tagihan Pembayaran & Arahkan ke Payment
        $qrisService = app(MidtransQrisService::class);
        try {
            $qrisService->getOrCreateReplacePdfPayment($this->record, $relativePublicPath);

            Notification::make()
                ->title('Verifikasi Berhasil')
                ->body('File naskah dan jumlah penulis telah sesuai. Mengarahkan ke halaman pembayaran...')
                ->success()
                ->send();

            $this->redirect(route('submissions.payment.replace-pdf', $this->record->id));
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Membuat Tagihan Pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string
    {
        return 'Ganti File PDF Naskah #' . $this->record->id;
    }

    public function getBreadcrumbs(): array
    {
        return [
            SubmissionResource::getUrl('index') => 'Submissions',
            SubmissionResource::getUrl('view', ['record' => $this->record]) => (string) $this->record->id,
            '' => 'Ganti PDF Naskah',
        ];
    }
}
