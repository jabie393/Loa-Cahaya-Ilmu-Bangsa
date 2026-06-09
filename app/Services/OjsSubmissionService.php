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
            // Update tracking to pending
            $submission->update([
                'ojs_status' => 'pending',
                'ojs_synced_at' => now(),
                'ojs_error_message' => null,
            ]);

            $baseUrl = rtrim(config('ojs.base_url'), '/');
            if (empty($baseUrl)) {
                throw new \Exception('OJS Base URL is not configured.');
            }

            $secretKey = config('ojs.secret_key');
            if (empty($secretKey)) {
                throw new \Exception('OJS Secret Key is not configured.');
            }

            $journal = $submission->journal;
            if (!$journal) {
                throw new \Exception('Journal association not found for this submission.');
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
                'institution' => $submission->institution,
                'loa_number' => $loaNumber,
                'loa_date' => $loaDate,
                'pdf_url' => $pdfUrl,
                'volume' => $vol,
                'number' => $no,
                'year' => $year,
                'references' => $submission->references,
            ];

            Log::info("Sending submission ID: {$submission->id} to OJS URL: {$url}");

            // Send POST request with 30s timeout
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->timeout(30)->post($url, $payload);

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

            Log::info("OJS integration succeeded for submission ID: {$submission->id}. OJS Submission ID: {$ojsSubmissionId}");

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
}
