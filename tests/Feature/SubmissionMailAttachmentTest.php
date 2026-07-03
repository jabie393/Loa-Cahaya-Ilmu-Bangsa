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

        $this->assertCount(3, $attachments);
        $this->assertEquals('Letter_of_Acceptance.pdf', $attachments[0]->as);
        $this->assertEquals('Author_Certificate.pdf', $attachments[1]->as);
        $this->assertEquals('Plagiarism_Free_Certificate.pdf', $attachments[2]->as);
    }
}
