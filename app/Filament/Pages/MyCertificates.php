<?php

namespace App\Filament\Pages;

use App\Models\Submission;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class MyCertificates extends Page
{
    use HasPageShield, WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.my-certificates';
    protected static ?string $title = 'My Publication';
    protected static ?string $navigationLabel = '4. My Publication';

    public Collection $submissions;

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Submission::query()
            ->where('user_id', Auth::id())
            ->where('status', 'Approved')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public function mount(): void
    {
        $this->loadSubmissions();
    }

    public function loadSubmissions(): void
    {
        $this->submissions = Submission::query()
            ->where('user_id', Auth::id())
            ->where('status', 'Approved')
            ->with(['journal', 'payments'])
            ->latest('approved_date')
            ->get();
    }

    public function openServiceModal(int $submissionId, string $type): void
    {
        $this->selectedSubmissionId = $submissionId;
        $this->selectedServiceType = $type;
        $this->newPdfFile = null;
        $this->paymentProof = null;
        $this->serviceNotes = '';
        $this->showServiceModal = true;
    }

    public function submitServiceRequest(): void
    {
        $this->validate([
            'selectedSubmissionId' => 'required|exists:submissions,id',
            'selectedServiceType' => 'required|in:add_doi,update_pdf',
            'paymentProof' => 'required|image|max:5120',
            'newPdfFile' => $this->selectedServiceType === 'update_pdf' ? 'required|mimes:pdf|max:20480' : 'nullable',
        ], [
            'paymentProof.required' => 'Wajib mengunggah bukti transfer pembayaran.',
            'paymentProof.image' => 'Bukti pembayaran harus berupa gambar (JPG/PNG).',
            'newPdfFile.required' => 'Wajib mengunggah file PDF naskah baru.',
            'newPdfFile.mimes' => 'File naskah baru harus berformat PDF.',
        ]);

        $submission = Submission::where('id', $this->selectedSubmissionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check package
        $packageCode = $this->selectedServiceType === 'add_doi' ? 'doi_saja' : 'ganti_pdf';
        $package = Package::where('code', $packageCode)->first();

        // Store payment proof
        $proofPath = $this->paymentProof->store('proof-of-payment', 'public');

        // Store new PDF if applicable
        $pdfPath = null;
        if ($this->selectedServiceType === 'update_pdf' && $this->newPdfFile) {
            $pdfPath = $this->newPdfFile->store('manuscripts', 'public');
        }

        // Create service request
        $serviceRequest = ServiceRequest::create([
            'user_id' => Auth::id(),
            'submission_id' => $submission->id,
            'package_id' => $package?->id,
            'service_type' => $this->selectedServiceType,
            'new_pdf_file' => $pdfPath,
            'requested_doi' => $this->selectedServiceType === 'add_doi' ? $this->serviceNotes : null,
            'notes' => $this->serviceNotes,
            'proof_of_payment' => $proofPath,
            'status' => 'pending',
        ]);

        // Create pending transaction in finance ledger
        FinanceTransaction::create([
            'user_id' => Auth::id(),
            'package_id' => $package?->id,
            'submission_id' => $submission->id,
            'service_request_id' => $serviceRequest->id,
            'item_name' => $package?->name ?? ($this->selectedServiceType === 'add_doi' ? 'Doi saja' : 'Ganti pdf'),
            'category' => 'other_service',
            'gross_price' => $package?->price ?? ($this->selectedServiceType === 'add_doi' ? 20000 : 25000),
            'qris_fee' => $package?->qris_amount ?? ($this->selectedServiceType === 'add_doi' ? 140 : 175),
            'dev_amount' => $package?->dev_amount ?? ($this->selectedServiceType === 'add_doi' ? 4860 : 9825),
            'admin_amount' => $package?->admin_amount ?? 15000,
            'payment_method' => 'qris',
            'proof_of_payment' => $proofPath,
            'status' => 'pending',
        ]);

        $this->showServiceModal = false;
        $this->loadSubmissions();

        Notification::make()
            ->title('Permintaan Berhasil Diajukan!')
            ->body('Admin akan memverifikasi bukti pembayaran dan memproses permintaan Anda secepatnya.')
            ->success()
            ->send();
    }
}
