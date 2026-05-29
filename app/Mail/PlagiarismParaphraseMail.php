<?php

namespace App\Mail;

use App\Models\PlagiarismParaphrase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlagiarismParaphraseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PlagiarismParaphrase $paraphrase
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hasil Parafrase & Optimasi Naskah: ' . ($this->paraphrase->plagiarismCheck->title ?: 'Dokumen Jurnal'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'filament.emails.plagiarism-paraphrase',
            with: [
                'paraphrase' => $this->paraphrase,
            ],
        );
    }
}
