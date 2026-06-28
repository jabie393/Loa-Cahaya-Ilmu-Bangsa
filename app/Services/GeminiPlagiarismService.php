<?php

namespace App\Services;

use App\Contracts\PlagiarismContract;
use App\Models\PlagiarismCheck;
use App\Traits\HandlesGeminiFallback;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use ZipArchive;
use Exception;

class GeminiPlagiarismService implements PlagiarismContract
{
    use HandlesGeminiFallback;
    /**
     * Perform plagiarism check using Gemini.
     */
    public function check(PlagiarismCheck $record): array
    {
        $text = $this->extractText($record->file_path);
        
        if (empty($text)) {
            throw new Exception("Gagal mengekstrak teks dari dokumen.");
        }

        $apiKey = config('services.gemini.plagiarism_key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        if (!$apiKey) {
            throw new Exception("API Key Gemini belum diatur.");
        }

        $prompt = $this->buildPrompt($text);

        $payload = [
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
        ];

        try {
            $response = $this->callGemini($apiKey, $model, $payload, 120);
        } catch (Exception $e) {
            throw new Exception("Koneksi ke AI terputus atau server sibuk. Silakan coba beberapa saat lagi. Detail: " . $e->getMessage());
        }

        $data = $response->json();
        $rawContent = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$rawContent) {
            throw new Exception("Format respons AI tidak valid.");
        }

        return $this->decodeGeminiJson($rawContent);
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
        $text = '';

        if ($extension === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($absolutePath);
            $text = $pdf->getText();
        } elseif ($extension === 'docx') {
            $text = $this->extractTextFromDocx($absolutePath);
        }

        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
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
        $text = mb_substr($text, 0, 50000, 'UTF-8'); // Limit to 50k chars for plagiarism check (~8k words)

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
