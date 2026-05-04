<?php

namespace App\Mail;

use App\Models\PlagiarismCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlagiarismCheckResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PlagiarismCheck $record
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hasil Cek Plagiasi: ' . ($this->record->title ?: 'Dokumen Jurnal'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'filament.emails.plagiarism-report',
            with: [
                'record' => $this->record,
            ],
        );
    }
}
