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

class ExternalSubmissionApproved extends Mailable
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
        $journalName = $this->submission->journal?->name ?: 'Journal';
        return new Envelope(
            subject: "Letter of Acceptance - {$journalName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'filament.emails.external-submission-approved',
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

        // Only attach Letter of Acceptance (LOA)
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
            \Illuminate\Support\Facades\Log::error("Failed to attach LOA PDF to external approval email: " . $e->getMessage());
        }

        return $attachments;
    }
}
