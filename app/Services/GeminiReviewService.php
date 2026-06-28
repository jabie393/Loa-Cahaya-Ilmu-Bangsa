<?php

namespace App\Services;

use App\Contracts\AiReviewContract;
use App\Traits\HandlesGeminiFallback;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Smalot\PdfParser\Parser;
use ZipArchive;
use Exception;

class GeminiReviewService implements AiReviewContract
{
    use HandlesGeminiFallback;
    /**
     * Perform an AI review using Google Gemini.
     */
    public function review(Model $record): array
    {
        $filePath = $record->file_path ?? $record->manuscript_file;
        
        if (empty($filePath)) {
            throw new Exception("File naskah tidak ditemukan.");
        }

        $text = $this->extractText($filePath);
        
        if (empty($text)) {
            throw new Exception("Gagal mengekstrak teks dari dokumen.");
        }

        $apiKey = config('services.gemini.review_key');
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
                'maxOutputTokens' => 8192,
            ]
        ];

        try {
            $response = $this->callGemini($apiKey, $model, $payload, 120);
        } catch (Exception $e) {
            throw new Exception("Koneksi ke AI terputus atau server sibuk. Detail: " . $e->getMessage());
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
            // Try direct path if not in storage/app/public
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
        // Limit text length to avoid token limits (approx 75k chars is ~12k words, more than enough for standard journal paper)
        $text = mb_substr($text, 0, 75000, 'UTF-8');

        return 'Anda adalah seorang reviewer jurnal profesional senior dari \'Cahaya Ilmu Bangsa\'. 
        Tugas Anda adalah memberikan review \'Pra-OJS\' (tahap awal sebelum masuk sistem OJS) yang ramah namun berstandar tinggi.
        Berikan review singkat dan poin-poin yang jelas untuk setiap bagian berikut.
        Gunakan Bahasa Indonesia yang formal dan profesional.
        
        PENTING: Anda harus mengembalikan hasil dalam format JSON murni dengan struktur kunci berikut:
        {
            "structure_review": "...",
            "abstract_review": "...",
            "introduction_review": "...",
            "method_review": "...",
            "results_review": "...",
            "conclusion_review": "...",
            "bibliography_review": "...",
            "general_suggestions": "...",
            "detected_title": "...",
            "detected_abstract": "...",
            "detected_keywords": "... (pisahkan dengan koma, contoh: pendidikan, teknologi, pembelajaran)",
            "detected_references": "... (tuliskan daftar pustaka/referensi yang ditemukan, pisahkan per baris)"
        }

        ATURAN SINTAKS JSON:
        1. Jangan menyertakan tanda petik ganda (") di dalam nilai teks JSON kecuali tanda petik tersebut telah di-escape dengan backslash (\"). Sangat disarankan menggunakan tanda petik tunggal (\') jika ingin mengutip kata/istilah di dalam teks hasil review.
        2. Jangan menyertakan karakter kontrol seperti baris baru langsung di dalam string JSON. Gunakan \n untuk baris baru.
        3. Pastikan format JSON benar-benar valid secara sintaksis dan lengkap (tidak terpotong).

        Isi jurnal untuk di-review:
        ---
        ' . $text . '
        ---';
    }
}
