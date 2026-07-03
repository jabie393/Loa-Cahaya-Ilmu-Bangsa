<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;

class SubmissionApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $submission;

    /**
     * Create a new message instance.
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Submission Approved - LOA Cahaya Ilmu Bangsa',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'filament.emails.submission-approved',
            with: [
                'submission' => $this->submission,
                'user' => $this->submission->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // 1. Lampiran LOA PDF
        try {
            $loaView = $this->submission->getTemplateView();
            $loaPdf = Pdf::loadView($loaView, ['record' => $this->submission])
                ->setPaper('a4', 'portrait')
                ->output();
            
            $attachments[] = Attachment::fromData(
                fn () => $loaPdf,
                'Letter_of_Acceptance.pdf'
            )->withMime('application/pdf');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Gagal melampirkan LOA PDF ke email persetujuan: " . $e->getMessage());
        }

        // 2. Lampiran Author Certificate (AC) PDF
        try {
            $acView = $this->submission->getAcTemplateView();
            $acPdf = Pdf::loadView($acView, ['record' => $this->submission])
                ->setPaper('a4', 'landscape')
                ->output();
            
            $attachments[] = Attachment::fromData(
                fn () => $acPdf,
                'Author_Certificate.pdf'
            )->withMime('application/pdf');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Gagal melampirkan AC PDF ke email persetujuan: " . $e->getMessage());
        }

        // 3. Lampiran Plagiarism-Free Certificate (PFC) PDF
        try {
            $pfcView = $this->submission->getPfcTemplateView();
            $pfcPdf = Pdf::loadView($pfcView, ['record' => $this->submission])
                ->setPaper('a4', 'landscape')
                ->output();
            
            $attachments[] = Attachment::fromData(
                fn () => $pfcPdf,
                'Plagiarism_Free_Certificate.pdf'
            )->withMime('application/pdf');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Gagal melampirkan PFC PDF ke email persetujuan: " . $e->getMessage());
        }

        return $attachments;
    }
}