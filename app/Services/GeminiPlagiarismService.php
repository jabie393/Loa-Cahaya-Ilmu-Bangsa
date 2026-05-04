<?php

namespace App\Services;

use App\Contracts\PlagiarismContract;
use App\Models\PlagiarismCheck;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use ZipArchive;
use Exception;

class GeminiPlagiarismService implements PlagiarismContract
{
    /**
     * Perform plagiarism check using Gemini.
     */
    public function check(PlagiarismCheck $record): array
    {
        $text = $this->extractText($record->file_path);
        
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
            try {
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

                // Handle server errors or rate limiting
                if (in_array($response->status(), [429, 500, 503, 504])) {
                    $retryCount++;
                    if ($retryCount < $maxRetries) {
                        sleep(3);
                        continue;
                    }
                }

                throw new Exception("API Gemini Error (Status: {$response->status()}): " . $response->body());

            } catch (Exception $e) {
                $retryCount++;
                if ($retryCount < $maxRetries) {
                    sleep(3);
                    continue;
                }
                throw new Exception("Koneksi ke AI terputus atau server sibuk (cURL Error). Silakan coba beberapa saat lagi. Detail: " . $e->getMessage());
            }
        }

        $data = $response->json();
        $rawContent = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$rawContent) {
            throw new Exception("Format respons AI tidak valid.");
        }

        $rawContent = trim($rawContent);
        if (str_starts_with($rawContent, '```')) {
            $rawContent = preg_replace('/^```(?:json)?\s+/', '', $rawContent);
            $rawContent = preg_replace('/\s+```$/', '', $rawContent);
            $rawContent = trim($rawContent);
        }

        $result = json_decode($rawContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Gagal memparsing JSON dari AI.");
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

    protected function extractTextFromDocx(string $filePath): string
    {
        $text = '';
        $zip = new ZipArchive();
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                $text = strip_tags(str_replace(['<w:p>', '<w:p ', '</w:p>'], ["\n\n", "\n\n", ""], $data));
            } else {
                $zip->close();
            }
        }
        return trim($text);
    }

    protected function buildPrompt(string $text): string
    {
        $text = substr($text, 0, 100000); // Limit to 100k chars for plagiarism check

        return "Anda adalah sistem 'Cek Plagiasi' cerdas.
        Tugas Anda adalah menganalisis teks berikut dan memberikan estimasi kemiripan (similarity score) berdasarkan pengetahuan luas Anda tentang literatur akademik.
        Berikan skor dalam persentase (0-100).
        Berikan juga beberapa bagian teks yang paling terindikasi memiliki kemiripan tinggi.
        Gunakan Bahasa Indonesia.
        
        PENTING: Kembalikan dalam format JSON murni:
        {
            \"similarity_score\": 25.5,
            \"similarity_category\": \"rendah\",
            \"highlighted_parts\": [
                {
                    \"text\": \"...\",
                    \"source\": \"... (jika diketahui, atau tulis 'External Source')\",
                    \"reason\": \"...\"
                }
            ],
            \"detected_title\": \"...\"
        }

        Kategori: 0-20% (rendah), 21-50% (sedang), >50% (tinggi).

        Teks Jurnal:
        ---
        {$text}
        ---";
    }
}
