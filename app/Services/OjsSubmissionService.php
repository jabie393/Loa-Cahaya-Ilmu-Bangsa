<?php

namespace App\Services;

use App\Models\Submission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OjsSubmissionService
{
    /**
     * Submit a submission to OJS.
     *
     * @param Submission $submission
     * @return array
     * @throws \Throwable
     */
    public function submit(Submission $submission): array
    {
        if (!config('ojs.enabled')) {
            Log::info("OJS integration is disabled. Skipping submission ID: {$submission->id}");
            return [
                'success' => true,
                'message' => 'OJS integration disabled, skipped.',
            ];
        }

        try {
            // If has_doi is true and repository_identifier is empty, generate it!
            if ($submission->has_doi && empty($submission->repository_identifier)) {
                $identifierService = new \App\Services\RepositoryIdentifierService();
                $identifier = $identifierService->generate($submission);
                
                $repoUrl = rtrim(env('REPO_URL', 'http://127.0.0.1:8001'), '/');
                $redirectUrl = $repoUrl . '/' . $identifier;
                $landingPage = "/article/submission-{$submission->id}";
                
                $submission->update([
                    'repository_identifier' => $identifier,
                    'repository_landing_page' => $landingPage,
                    'repository_redirect_url' => $redirectUrl,
                    'repository_identifier_status' => 'active',
                    'repository_identifier_generated_at' => now(),
                ]);
                $submission->refresh();
            }

            // Update tracking to pending
            $submission->update([
                'ojs_status' => 'pending',
                'ojs_synced_at' => now(),
                'ojs_error_message' => null,
            ]);

            $journal = $submission->journal;
            if (!$journal) {
                throw new \Exception('Journal association not found for this submission.');
            }

            $baseUrl = rtrim($journal->ojs_base_url ?: config('ojs.base_url'), '/');
            if (empty($baseUrl)) {
                throw new \Exception('OJS Base URL is not configured.');
            }

            $secretKey = $journal->ojs_secret_key ?: config('ojs.secret_key');
            if (empty($secretKey)) {
                throw new \Exception('OJS Secret Key is not configured.');
            }

            $journalPath = $journal->slug;
            if (!empty($journal->link)) {
                $parsedUrl = parse_url($journal->link);
                if (isset($parsedUrl['path'])) {
                    $pathParts = explode('/', trim($parsedUrl['path'], '/'));
                    $lastPart = end($pathParts);
                    if (!empty($lastPart) && $lastPart !== 'index.php') {
                        $journalPath = $lastPart;
                    }
                }
            }

            if (empty($journalPath)) {
                throw new \Exception('Journal slug/path is not configured.');
            }

            // Construct LOA Number & LOA Date
            $loaNumber = sprintf(
                '%s/CIB%03d/LOA%03d',
                $submission->created_at ? $submission->created_at->format('Y') : now()->format('Y'),
                $submission->journal_id,
                $submission->id
            );

            $loaDate = $submission->date_of_loa ? $submission->date_of_loa->format('Y-m-d') : now()->format('Y-m-d');

            // Parse volume, number, year from volume text
            $vol = null;
            $no = null;
            $year = null;
            if ($submission->volume && preg_match('/Vol\.\s*(.*?)\s+No\.\s*(.*?)\s+(?:Tahun\s+|\()(.*?)\)?$/i', $submission->volume, $matches)) {
                $vol = $matches[1] ?? null;
                $no = $matches[2] ?? null;
                $year = $matches[3] ?? null;
            }

            $pdfUrl = $submission->manuscript_file ? \Illuminate\Support\Facades\Storage::disk('public')->url($submission->manuscript_file) : null;

            // Build API endpoint URL
            if (!str_contains($baseUrl, 'index.php')) {
                $url = "{$baseUrl}/index.php/{$journalPath}/loa-api/submissions";
            } else {
                $url = "{$baseUrl}/{$journalPath}/loa-api/submissions";
            }

            // Prepare Payload
            $payload = [
                'secret' => $secretKey,
                'journal_path' => $journalPath,
                'title' => $submission->title,
                'abstract' => $submission->abstract,
                'keywords' => $submission->keywords,
                'author_name' => $submission->author_name,
                'email' => $submission->email,
                'institution' => null,
                'loa_number' => $loaNumber,
                'loa_date' => $loaDate,
                'pdf_url' => $pdfUrl,
                'volume' => $vol,
                'number' => $no,
                'year' => $year,
                'references' => $submission->references,
                'authors' => $submission->authors,
                'doi' => $submission->repository_redirect_url ?? null,
                'repository_identifier' => $submission->repository_identifier ?? null,
                'ojs_submission_id' => $submission->ojs_submission_id ?? null,
            ];

            $payload = $this->sanitizeUtf8($payload);

            Log::info("Sending submission ID: {$submission->id} to OJS URL: {$url}");

            // Send POST request with 90s timeout to accommodate slow mail sending / file downloads in OJS
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->timeout(90)->post($url, $payload);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                $errorMessage = "HTTP request failed with status {$status}: {$body}";
                throw new \Exception($errorMessage);
            }

            $responseJson = $response->json();
            if (empty($responseJson) || !isset($responseJson['success']) || !$responseJson['success']) {
                $msg = $responseJson['message'] ?? 'Unknown API error response';
                throw new \Exception("OJS API returned failure: {$msg}");
            }

            $ojsSubmissionId = $responseJson['submission_id'] ?? $responseJson['submissionId'] ?? null;
            $articleUrl = $responseJson['article_url'] ?? null;
            $resolvedVolume = $responseJson['volume'] ?? null;

            // Generate OJS Workflow Link as fallback if no article_url is returned
            $publicationLink = $articleUrl;
            if (empty($publicationLink) && $ojsSubmissionId) {
                if (!str_contains($baseUrl, 'index.php')) {
                    $publicationLink = "{$baseUrl}/index.php/{$journalPath}/workflow/index/{$ojsSubmissionId}/5";
                } else {
                    $publicationLink = "{$baseUrl}/{$journalPath}/workflow/index/{$ojsSubmissionId}/5";
                }
            }

            $ojsUsername = $responseJson['ojs_username'] ?? null;
            $ojsPassword = $responseJson['ojs_password'] ?? null;



            // Update submission tracking status on success
            $submission->update([
                'ojs_status' => 'submitted',
                'ojs_submission_id' => $ojsSubmissionId,
                'ojs_synced_at' => now(),
                'ojs_error_message' => null,
                'publication_link' => $publicationLink,
                'volume' => $submission->volume ?: $resolvedVolume,
                'ojs_username' => $ojsUsername,
                'ojs_password' => $ojsPassword,
            ]);

            // Trigger Email sending now that it's successfully pushed/published on OJS
            try {
                $submission->sendApprovalEmail();
            } catch (\Throwable $mailError) {
                Log::error("Failed to send approval email after OJS sync for submission ID: {$submission->id}. Error: {$mailError->getMessage()}");
            }

            Log::info("OJS integration succeeded for submission ID: {$submission->id}. OJS Submission ID: {$ojsSubmissionId}");

            // Sync to Repository
            try {
                self::publishToRepository($submission);
            } catch (\Throwable $repoError) {
                Log::error("Failed to publish to Repository for submission ID: {$submission->id}. Error: {$repoError->getMessage()}");
            }

            return [
                'success' => true,
                'message' => 'OJS submission created successfully',
                'ojs_submission_id' => $ojsSubmissionId,
            ];

        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            Log::error("OJS integration failed for submission ID: {$submission->id}. Error: {$errorMessage}");

            $submission->update([
                'ojs_status' => 'failed',
                'ojs_synced_at' => now(),
                'ojs_error_message' => $errorMessage,
            ]);

            throw $e;
        }
    }

    /**
     * Publish article to Repository database via API.
     *
     * @param Submission $submission
     * @return void
     */
    public static function publishToRepository(Submission $submission): void
    {
        $repoUrl = env('REPO_URL', 'http://localhost:8080');
        $apiToken = env('REPO_API_TOKEN', 'cib_repo_api_token_2026');

        $authorNames = [];
        if (is_array($submission->authors)) {
            foreach ($submission->authors as $author) {
                if (!empty($author['name'])) {
                    $authorNames[] = $author['name'];
                }
            }
        }
        if (empty($authorNames)) {
            $authorNames = array_map('trim', explode(',', $submission->author_name ?: ''));
        }
        $authorNames = array_values(array_filter($authorNames));
        if (empty($authorNames)) {
            $authorNames = ['Author'];
        }

        $payload = [
            'title' => $submission->title,
            'abstract' => $submission->abstract ?: '',
            'authors' => $authorNames,
            'keywords' => $submission->keywords ?: '',
            'journal_id' => (int) $submission->journal_id,
            'doi' => $submission->repository_redirect_url,
            'volume' => $submission->volume,
            'issue' => $submission->issue ?? '',
            'pages' => $submission->pages ?? '',
            'published_date' => $submission->approved_date ? $submission->approved_date->format('Y-m-d') : now()->format('Y-m-d'),
            'pdf_path' => $submission->manuscript_file,
            'ojs_url' => $submission->publication_link,
            'category' => $submission->journal?->name ?: 'Pendidikan',
        ];

        $response = \Illuminate\Support\Facades\Http::withToken($apiToken)
            ->withHeaders(['Accept' => 'application/json'])
            ->post($repoUrl . '/api/v1/articles/publish', $payload);

        if ($response->failed()) {
            throw new \Exception("Repo API failed: " . $response->status() . " - " . $response->body());
        }

        \Illuminate\Support\Facades\Log::info("Successfully published submission ID: {$submission->id} to Repository.");
    }

    /**
     * Dispatch OJS submission to a background CLI command.
     *
     * @param Submission $submission
     * @return void
     */
    public static function submitInBackground(Submission $submission): void
    {
        // Set state to pending in web request so UI updates immediately
        $submission->update([
            'ojs_status' => 'pending',
            'ojs_synced_at' => now(),
            'ojs_error_message' => null,
        ]);

        $id = (int) $submission->id;
        $artisanPath = base_path('artisan');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows background execution
            pclose(popen("start /B php \"" . $artisanPath . "\" submission:sync-ojs " . $id . " > NUL", "r"));
        } else {
            // Linux/Unix background execution - redirect stdin (< /dev/null) to completely detach from PHP-FPM
            exec("php \"" . $artisanPath . "\" submission:sync-ojs " . $id . " < /dev/null > /dev/null 2>&1 &");
        }
    }

    /**
     * Convert 4-byte mathematical alphanumeric characters to standard ASCII equivalents,
     * and strip any remaining 4-byte characters to prevent database errors on OJS
     * systems using 3-byte utf8 encoding.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function sanitizeUtf8(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->sanitizeUtf8($val);
            }
            return $value;
        }

        if (is_string($value)) {
            // Convert common mathematical alphanumeric characters to standard ASCII
            $value = preg_replace_callback('/[\x{1D400}-\x{1D7FF}]/u', function ($matches) {
                $char = $matches[0];
                $code = mb_ord($char, 'UTF-8');

                // Mathematical Bold Capital (A-Z)
                if ($code >= 0x1D400 && $code <= 0x1D419) {
                    return mb_chr($code - 119743, 'UTF-8');
                }
                // Mathematical Bold Small (a-z)
                if ($code >= 0x1D41A && $code <= 0x1D433) {
                    return mb_chr($code - 119737, 'UTF-8');
                }
                // Mathematical Italic Capital (A-Z)
                if ($code >= 0x1D434 && $code <= 0x1D44D) {
                    return mb_chr($code - 119795, 'UTF-8');
                }
                // Mathematical Italic Small (a-z)
                if ($code >= 0x1D44E && $code <= 0x1D467) {
                    return mb_chr($code - 119789, 'UTF-8');
                }
                // Mathematical Bold Italic Capital (A-Z)
                if ($code >= 0x1D468 && $code <= 0x1D481) {
                    return mb_chr($code - 119847, 'UTF-8');
                }
                // Mathematical Bold Italic Small (a-z)
                if ($code >= 0x1D482 && $code <= 0x1D49B) {
                    return mb_chr($code - 119841, 'UTF-8');
                }
                // Mathematical Double-struck Capital (A-Z)
                if ($code >= 0x1D538 && $code <= 0x1D551) {
                    return mb_chr($code - 120057, 'UTF-8');
                }
                // Mathematical Double-struck Small (a-z)
                if ($code >= 0x1D552 && $code <= 0x1D56B) {
                    return mb_chr($code - 120051, 'UTF-8');
                }
                // Mathematical Sans-serif Bold Capital (A-Z)
                if ($code >= 0x1D5D4 && $code <= 0x1D5ED) {
                    return mb_chr($code - 120213, 'UTF-8');
                }
                // Mathematical Sans-serif Bold Small (a-z)
                if ($code >= 0x1D5EE && $code <= 0x1D607) {
                    return mb_chr($code - 120207, 'UTF-8');
                }
                // Mathematical Monospace Capital (A-Z)
                if ($code >= 0x1D670 && $code <= 0x1D689) {
                    return mb_chr($code - 120369, 'UTF-8');
                }
                // Mathematical Monospace Small (a-z)
                if ($code >= 0x1D68A && $code <= 0x1D6A3) {
                    return mb_chr($code - 120363, 'UTF-8');
                }

                // If no mapping matched, return empty string (strip it)
                return '';
            }, $value);

            // Strip any remaining 4-byte characters (emojis, etc.)
            $value = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $value);
        }

        return $value;
    }
}
