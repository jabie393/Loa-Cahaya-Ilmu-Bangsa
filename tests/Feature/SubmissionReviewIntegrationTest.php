<?php

namespace Tests\Feature;

use App\Contracts\AiReviewContract;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class SubmissionReviewIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup a mock AI Reviewer
        $mockReviewer = Mockery::mock(AiReviewContract::class);
        $mockReviewer->shouldReceive('review')->andReturn([
            'structure_review' => 'Structure review notes',
            'abstract_review' => 'Abstract review notes',
            'introduction_review' => 'Intro review notes',
            'method_review' => 'Method review notes',
            'results_review' => 'Results review notes',
            'conclusion_review' => 'Conclusion review notes',
            'bibliography_review' => 'Bib review notes',
            'general_suggestions' => 'General suggestions notes',
            'detected_title' => 'DETECTED TITLE',
            'detected_abstract' => 'DETECTED ABSTRACT',
            'detected_keywords' => 'keyword1, keyword2',
            'detected_references' => "Reference 1\nReference 2",
        ]);

        $this->app->extend('ai-review', function ($manager) use ($mockReviewer) {
            $manager->extend('gemini', function () use ($mockReviewer) {
                return $mockReviewer;
            });
            return $manager;
        });

        // Mock mail
        Mail::fake();
    }

    public function test_submission_creation_triggers_review_and_sets_draft_status(): void
    {
        $user = User::factory()->create();

        $journal = Journal::create([
            'name' => 'Cahaya Jurnal',
            'slug' => 'cahaya-jurnal',
            'link' => 'https://cibangsa.com/index.php/cahaya',
        ]);

        $submission = Submission::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'author_name' => 'Main Author',
            'email' => 'author@example.com',
            'manuscript_file' => 'manuscripts/test.docx',
            'status' => 'Draft',
        ]);

        // Process review manually (representing afterCreate/afterSave page hooks)
        $submission->processReview();

        $submission->refresh();

        // Assert review status and results are populated correctly
        $this->assertEquals('reviewed', $submission->review_status);
        $this->assertEquals('DETECTED TITLE', $submission->title);
        $this->assertEquals('DETECTED ABSTRACT', $submission->abstract);
        $this->assertEquals('keyword1, keyword2', $submission->keywords);
        $this->assertEquals("Reference 1\nReference 2", $submission->references);
        $this->assertEquals('Structure review notes', $submission->structure_review);
        $this->assertEquals('General suggestions notes', $submission->general_suggestions);
        $this->assertNotNull($submission->review_email_sent_at);

        // Assert review mail is sent to correspondence email
        Mail::assertSent(\App\Mail\PreSubmissionReviewMail::class, function ($mail) use ($submission) {
            return $mail->hasTo('author@example.com') && $mail->review->id === $submission->id;
        });
    }

    public function test_payment_upload_elevates_status_to_pending(): void
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'name' => 'Cahaya Jurnal',
            'slug' => 'cahaya-jurnal',
            'link' => 'https://cibangsa.com/index.php/cahaya',
        ]);

        $submission = Submission::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'author_name' => 'Main Author',
            'email' => 'author@example.com',
            'manuscript_file' => 'manuscripts/test.docx',
            'status' => 'Draft',
        ]);

        // Mock Filament EditSubmission page logic
        $data = [
            'proof_of_payment' => 'proofs/proof.png',
            'title' => 'UPDATED TITLE',
        ];

        // Simulate mutateFormDataBeforeSave logic
        if (in_array($submission->status, ['Draft', 'Rejected']) && !empty($data['proof_of_payment'])) {
            $submission->status = 'Pending';
            $submission->submission_date = now();
        }
        $submission->fill($data);
        $submission->save();

        $submission->refresh();

        $this->assertEquals('Pending', $submission->status);
        $this->assertEquals('proofs/proof.png', $submission->proof_of_payment);
        $this->assertEquals('UPDATED TITLE', $submission->title);
    }
}
