<?php

namespace App\Services;

use App\Contracts\AiReviewContract;
use App\Models\PreSubmissionReview;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use ZipArchive;
use Exception;

class GeminiReviewService implements AiReviewContract
{
    /**
     * Perform an AI review using Google Gemini.
     */
    public function review(PreSubmissionReview $reviewRecord): array
    {
        $text = $this->extractText($reviewRecord->file_path);
        
        if (empty($text)) {
            throw new Exception("Gagal mengekstrak teks dari dokumen.");
        }

        $apiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-1.5-flash'));

        if (!$apiKey) {
            throw new Exception("API Key Gemini belum diatur.");
        }

        $prompt = $this->buildPrompt($text);

        $maxRetries = 3;
        $retryCount = 0;
        $response = null;

        while ($retryCount < $maxRetries) {
            $response = Http::timeout(120)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                break;
            }

            // If 503 (Overloaded) or 429 (Rate Limit), wait and retry
            if (in_array($response->status(), [503, 429])) {
                $retryCount++;
                if ($retryCount < $maxRetries) {
                    sleep(2); // wait 2 seconds before retry
                    continue;
                }
            }

            throw new Exception("API Gemini Error: " . $response->body());
        }

        $data = $response->json();
        $rawContent = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$rawContent) {
            throw new Exception("Format respons AI tidak valid.");
        }

        // Clean up the response from markdown backticks if present
        $rawContent = trim($rawContent);
        if (str_starts_with($rawContent, '```')) {
            $rawContent = preg_replace('/^```(?:json)?\s+/', '', $rawContent);
            $rawContent = preg_replace('/\s+```$/', '', $rawContent);
            $rawContent = trim($rawContent);
        }

        $result = json_decode($rawContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Gagal memparsing JSON dari AI: " . json_last_error_msg() . " | Raw Content: " . substr($rawContent, 0, 100) . "...");
        }

        return $result;
    }

    /**
     * Extract text from PDF or DOCX.
     */
    protected function extractText(string $filePath): string
    {
        $absolutePath = storage_path('app/public/' . $filePath);
        
        if (!file_exists($absolutePath)) {
            // Try direct path if not in storage/app/public
            $absolutePath = Storage::path($filePath);
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($absolutePath);
            return $pdf->getText();
        }

        if ($extension === 'docx') {
            return $this->extractTextFromDocx($absolutePath);
        }

        return '';
    }

    /**
     * Logic to extract text from DOCX file.
     */
    protected function extractTextFromDocx(string $filePath): string
    {
        $text = '';
        $zip = new ZipArchive();
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                // Replace paragraph tags with newlines for better structure
                $text = strip_tags(str_replace(['<w:p>', '<w:p ', '</w:p>'], ["\n\n", "\n\n", ""], $data));
            } else {
                $zip->close();
            }
        }
        return trim($text);
    }

    /**
     * Build the structured prompt for Gemini.
     */
    protected function buildPrompt(string $text): string
    {
        // Limit text length to avoid token limits (approx 150k chars)
        $text = substr($text, 0, 150000);

        return "Anda adalah seorang reviewer jurnal profesional senior dari 'Cahaya Ilmu Bangsa'. 
        Tugas Anda adalah memberikan review 'Pra-OJS' (tahap awal sebelum masuk sistem OJS) yang ramah namun berstandar tinggi.
        Berikan review singkat dan poin-poin yang jelas untuk setiap bagian berikut.
        Gunakan Bahasa Indonesia yang formal dan profesional.
        
        PENTING: Anda harus mengembalikan hasil dalam format JSON murni dengan struktur kunci berikut:
        {
            \"structure_review\": \"...\",
            \"abstract_review\": \"...\",
            \"introduction_review\": \"...\",
            \"method_review\": \"...\",
            \"results_review\": \"...\",
            \"conclusion_review\": \"...\",
            \"bibliography_review\": \"...\",
            \"general_suggestions\": \"...\",
            \"detected_title\": \"...\"
        }

        Isi jurnal untuk di-review:
        ---
        {$text}
        ---";
    }
}
