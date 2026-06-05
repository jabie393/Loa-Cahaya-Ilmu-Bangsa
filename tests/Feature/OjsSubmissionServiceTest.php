<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\Submission;
use App\Services\OjsSubmissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OjsSubmissionServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure default testing configs are set
        config([
            'ojs.enabled' => true,
            'ojs.base_url' => 'https://cibangsa.com',
            'ojs.secret_key' => 'test-secret',
        ]);
    }

    /**
     * Test integration is skipped when disabled.
     */
    public function test_submitting_when_disabled(): void
    {
        config(['ojs.enabled' => false]);

        Http::fake();

        $journal = Journal::create([
            'name' => 'Test Journal',
            'slug' => 'test-journal',
            'link' => 'https://cibangsa.com/index.php/testjournal',
        ]);

        $submission = Submission::create([
            'author_name' => 'John Doe',
            'title' => 'Test Article',
            'institution' => 'Test Uni',
            'email' => 'john@example.com',
            'journal_id' => $journal->id,
            'status' => 'Pending',
        ]);

        $service = new OjsSubmissionService();
        $result = $service->submit($submission);

        $this->assertTrue($result['success']);
        $this->assertEquals('OJS integration disabled, skipped.', $result['message']);
        Http::assertNothingSent();
    }

    /**
     * Test integration fails and throws exception when config is missing.
     */
    public function test_submitting_missing_config(): void
    {
        config(['ojs.base_url' => '']);

        $journal = Journal::create([
            'name' => 'Test Journal',
            'slug' => 'test-journal',
            'link' => 'https://cibangsa.com/index.php/testjournal',
        ]);

        $submission = Submission::create([
            'author_name' => 'John Doe',
            'title' => 'Test Article',
            'institution' => 'Test Uni',
            'email' => 'john@example.com',
            'journal_id' => $journal->id,
            'status' => 'Pending',
        ]);

        $service = new OjsSubmissionService();

        try {
            $service->submit($submission);
            $this->fail('Expected exception was not thrown.');
        } catch (\Throwable $e) {
            $this->assertEquals('OJS Base URL is not configured.', $e->getMessage());
        }

        $submission->refresh();
        $this->assertEquals('failed', $submission->ojs_status);
        $this->assertEquals('OJS Base URL is not configured.', $submission->ojs_error_message);
    }

    /**
     * Test successful submission triggers correct HTTP POST and updates DB status.
     */
    public function test_submitting_success(): void
    {
        Http::fake([
            'https://cibangsa.com/index.php/testjournal/loa-api/submissions' => Http::response([
                'success' => true,
                'message' => 'Submission created successfully',
                'submissionId' => 888777,
            ], 200)
        ]);

        $journal = Journal::create([
            'name' => 'Test Journal',
            'slug' => 'test-journal',
            'link' => 'https://cibangsa.com/index.php/testjournal',
        ]);

        $submission = Submission::create([
            'author_name' => 'John Doe',
            'title' => 'Test Article',
            'institution' => 'Test Uni',
            'email' => 'john@example.com',
            'journal_id' => $journal->id,
            'status' => 'Pending',
            'abstract' => 'This is the abstract text.',
            'keywords' => 'keyword1, keyword2',
        ]);

        $service = new OjsSubmissionService();
        $result = $service->submit($submission);

        $this->assertTrue($result['success']);
        $this->assertEquals(888777, $result['ojs_submission_id']);

        $submission->refresh();
        $this->assertEquals('submitted', $submission->ojs_status);
        $this->assertEquals('888777', $submission->ojs_submission_id);
        $this->assertEquals('https://cibangsa.com/index.php/testjournal/workflow/index/888777/5', $submission->publication_link);
        $this->assertNull($submission->ojs_error_message);

        Http::assertSent(function ($request) use ($submission) {
            return $request->url() === 'https://cibangsa.com/index.php/testjournal/loa-api/submissions' &&
                $request['secret'] === 'test-secret' &&
                $request['journal_path'] === 'testjournal' &&
                $request['title'] === 'Test Article' &&
                $request['abstract'] === 'This is the abstract text.' &&
                $request['keywords'] === 'keyword1, keyword2' &&
                $request['author_name'] === 'John Doe' &&
                $request['email'] === 'john@example.com' &&
                $request['institution'] === 'Test Uni' &&
                !empty($request['loa_number']) &&
                !empty($request['loa_date']);
        });
    }

    /**
     * Test submission failure handles API failure response.
     */
    public function test_submitting_api_failure(): void
    {
        Http::fake([
            'https://cibangsa.com/index.php/testjournal/loa-api/submissions' => Http::response([
                'success' => false,
                'message' => 'Invalid secret key parameter',
            ], 401)
        ]);

        $journal = Journal::create([
            'name' => 'Test Journal',
            'slug' => 'test-journal',
            'link' => 'https://cibangsa.com/index.php/testjournal',
        ]);

        $submission = Submission::create([
            'author_name' => 'John Doe',
            'title' => 'Test Article',
            'institution' => 'Test Uni',
            'email' => 'john@example.com',
            'journal_id' => $journal->id,
            'status' => 'Pending',
        ]);

        $service = new OjsSubmissionService();

        try {
            $service->submit($submission);
            $this->fail('Expected exception was not thrown.');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('HTTP request failed with status 401', $e->getMessage());
        }

        $submission->refresh();
        $this->assertEquals('failed', $submission->ojs_status);
        $this->assertStringContainsString('HTTP request failed with status 401', $submission->ojs_error_message);
    }
}
