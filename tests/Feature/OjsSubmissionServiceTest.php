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
            'authors' => [
                ['name' => 'John Doe', 'institution' => 'University A'],
            ],
            'title' => 'Test Article',
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
            'authors' => [
                ['name' => 'John Doe', 'institution' => 'University A'],
            ],
            'title' => 'Test Article',
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
                'submission_id' => 888777,
                'article_url' => 'https://cibangsa.com/index.php/testjournal/article/view/888777',
                'volume' => 'Vol. 3 No. 2 (2026)',
                'ojs_username' => 'johndoe',
                'ojs_password' => 'Pass123!',
            ], 200)
        ]);

        $journal = Journal::create([
            'name' => 'Test Journal',
            'slug' => 'test-journal',
            'link' => 'https://cibangsa.com/index.php/testjournal',
        ]);

        $submission = Submission::create([
            'authors' => [
                ['name' => 'John Doe', 'institution' => 'University A'],
                ['name' => 'Jane Smith', 'institution' => 'University B'],
            ],
            'title' => 'Test Article',
            'email' => 'john@example.com',
            'journal_id' => $journal->id,
            'status' => 'Pending',
            'abstract' => 'This is the abstract text.',
            'keywords' => 'keyword1, keyword2',
            'references' => "Reference 1\nReference 2",
            'volume' => null,
            'manuscript_file' => 'manuscripts/test.pdf',
        ]);

        $service = new OjsSubmissionService();
        $result = $service->submit($submission);

        $this->assertTrue($result['success']);
        $this->assertEquals(888777, $result['ojs_submission_id']);

        $submission->refresh();
        $this->assertEquals('submitted', $submission->ojs_status);
        $this->assertEquals('888777', $submission->ojs_submission_id);
        $this->assertEquals('https://cibangsa.com/index.php/testjournal/article/view/888777', $submission->publication_link);
        $this->assertEquals('Vol. 3 No. 2 (2026)', $submission->volume);
        $this->assertEquals('johndoe', $submission->ojs_username);
        $this->assertEquals('Pass123!', $submission->ojs_password);
        $this->assertNull($submission->ojs_error_message);

        Http::assertSent(function ($request) use ($submission) {
            return $request->url() === 'https://cibangsa.com/index.php/testjournal/loa-api/submissions' &&
                $request['secret'] === 'test-secret' &&
                $request['journal_path'] === 'testjournal' &&
                $request['title'] === 'Test Article' &&
                $request['abstract'] === 'This is the abstract text.' &&
                $request['keywords'] === 'keyword1, keyword2' &&
                $request['references'] === "Reference 1\nReference 2" &&
                $request['author_name'] === 'John Doe, Jane Smith' &&
                $request['email'] === 'john@example.com' &&
                $request['institution'] === null &&
                $request['volume'] === null &&
                $request['number'] === null &&
                $request['year'] === null &&
                $request['authors'] === [
                    ['name' => 'John Doe', 'institution' => 'University A'],
                    ['name' => 'Jane Smith', 'institution' => 'University B'],
                ] &&
                !empty($request['pdf_url']) &&
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
            'authors' => [
                ['name' => 'John Doe', 'institution' => 'University A'],
            ],
            'title' => 'Test Article',
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

    /**
     * Test sync console command execution.
     */
    public function test_sync_command_success(): void
    {
        Http::fake([
            'https://cibangsa.com/index.php/testjournal/loa-api/submissions' => Http::response([
                'success' => true,
                'message' => 'Submission created successfully',
                'submission_id' => 112233,
            ], 200)
        ]);

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
            'status' => 'Pending',
        ]);

        $this->artisan('submission:sync-ojs', ['id' => $submission->id])
            ->assertExitCode(0);

        $submission->refresh();
        $this->assertEquals('submitted', $submission->ojs_status);
        $this->assertEquals('112233', $submission->ojs_submission_id);
    }

    /**
     * Test sanitization of 4-byte mathematical alphanumeric characters.
     */
    public function test_submitting_sanitizes_4byte_mathematical_characters(): void
    {
        Http::fake([
            'https://cibangsa.com/index.php/testjournal/loa-api/submissions' => Http::response([
                'success' => true,
                'message' => 'Submission created successfully',
                'submission_id' => 888777,
            ], 200)
        ]);

        $journal = Journal::create([
            'name' => 'Test Journal',
            'slug' => 'test-journal',
            'link' => 'https://cibangsa.com/index.php/testjournal',
        ]);

        // "𝑠" is U+1D460 (Mathematical Italic Small S)
        // "𝑡" is U+1D461 (Mathematical Italic Small T)
        // We also add an emoji (e.g. 🚀) which should be stripped
        $submission = Submission::create([
            'authors' => [
                ['name' => 'John Doe', 'institution' => 'University A'],
            ],
            'title' => 'Test Article 𝑠',
            'email' => 'john@example.com',
            'journal_id' => $journal->id,
            'status' => 'Pending',
            'abstract' => 'Analisis Betweenness Centrality dihitung untuk seluruh pasangan simpul asal 𝑠 dan tujuan 𝑡 melalui simpul perantara 🚀',
            'keywords' => 'test 𝑠, 𝑡, 🚀',
        ]);

        $service = new OjsSubmissionService();
        $result = $service->submit($submission);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://cibangsa.com/index.php/testjournal/loa-api/submissions' &&
                $request['title'] === 'Test Article s' &&
                $request['abstract'] === 'Analisis Betweenness Centrality dihitung untuk seluruh pasangan simpul asal s dan tujuan t melalui simpul perantara ' &&
                $request['keywords'] === 'test s, t, ';
        });
    }

    /**
     * Test that OjsSubmissionService uses journal-specific base URL and secret key if defined.
     */
    public function test_submitting_uses_journal_specific_credentials(): void
    {
        Http::fake([
            'https://custom-journal-domain.com/index.php/custom-slug/loa-api/submissions' => Http::response([
                'success' => true,
                'message' => 'Submission created successfully',
                'submission_id' => 999111,
            ], 200)
        ]);

        $journal = Journal::create([
            'name' => 'Custom OJS Journal',
            'slug' => 'custom-slug',
            'link' => 'https://custom-journal-domain.com/index.php/custom-slug',
            'ojs_base_url' => 'https://custom-journal-domain.com',
            'ojs_secret_key' => 'custom-secret-key-123',
        ]);

        $submission = Submission::create([
            'authors' => [
                ['name' => 'Jane Doe', 'institution' => 'University B'],
            ],
            'title' => 'Test Article',
            'email' => 'jane@example.com',
            'journal_id' => $journal->id,
            'status' => 'Pending',
        ]);

        $service = new OjsSubmissionService();
        $result = $service->submit($submission);

        $this->assertTrue($result['success']);
        $this->assertEquals(999111, $result['ojs_submission_id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://custom-journal-domain.com/index.php/custom-slug/loa-api/submissions' &&
                $request['secret'] === 'custom-secret-key-123' &&
                $request['journal_path'] === 'custom-slug';
        });
    }
}


