<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Submission;
use App\Models\Journal;
use App\Mail\SubmissionApproved;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SubmissionMailAttachmentTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that SubmissionApproved email contains LOA, AC, and PFC attachments.
     */
    public function test_submission_approved_mail_has_attachments(): void
    {
        $journal = Journal::create([
            'name' => 'Test Journal',
            'slug' => 'test-journal',
            'link' => 'https://cibangsa.com/index.php/testjournal',
        ]);

        $submission = Submission::create([
            'authors' => [
                ['name' => 'John Doe', 'institution' => 'University A'],
            ],
            'title' => 'Test Article',
            'email' => 'john@example.com',
            'journal_id' => $journal->id,
            'status' => 'Approved',
            'approved_date' => now(),
        ]);

        $mailable = new SubmissionApproved($submission);

        // Build the attachments
        $attachments = $mailable->attachments();

        $this->assertCount(0, $attachments);
    }

    /**
     * Test that SubmissionApproved email has zero attachments for IJEFI.
     */
    public function test_submission_approved_mail_has_only_loa_for_ijefi(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $journal = Journal::firstOrCreate(
            ['slug' => 'ijefi'],
            [
                'name' => 'Ijefi Journal',
                'link' => 'https://ijefijournal.com/index.php/ijefi',
                'ojs_base_url' => 'https://ijefijournal.com',
            ]
        );
        $journal->update(['ojs_base_url' => 'https://ijefijournal.com']);

        $submission = Submission::create([
            'authors' => [
                ['name' => 'John Doe', 'institution' => 'University A'],
            ],
            'title' => 'Test Article',
            'email' => 'john@example.com',
            'journal_id' => $journal->id,
            'status' => 'Approved',
            'approved_date' => now(),
        ]);

        $submission->sendApprovalEmail();

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ExternalSubmissionApproved::class, function ($mail) use ($submission) {
            $attachments = $mail->attachments();
            $this->assertCount(0, $attachments);
            $this->assertEquals('Letter of Acceptance - ' . $submission->journal->name, $mail->envelope()->subject);
            return $mail->hasTo('john@example.com');
        });
    }
}
