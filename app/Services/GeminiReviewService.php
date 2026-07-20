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

        $isExternal = method_exists($record, 'isExternal') && $record->isExternal();
        $prompt = $this->buildPrompt($text, $isExternal);

        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'structure_review' => ['type' => 'STRING', 'nullable' => true],
                'abstract_review' => ['type' => 'STRING', 'nullable' => true],
                'introduction_review' => ['type' => 'STRING', 'nullable' => true],
                'method_review' => ['type' => 'STRING', 'nullable' => true],
                'results_review' => ['type' => 'STRING', 'nullable' => true],
                'conclusion_review' => ['type' => 'STRING', 'nullable' => true],
                'bibliography_review' => ['type' => 'STRING', 'nullable' => true],
                'general_suggestions' => ['type' => 'STRING', 'nullable' => true],
                'detected_title' => ['type' => 'STRING', 'nullable' => true],
                'detected_abstract' => ['type' => 'STRING', 'nullable' => true],
                'detected_keywords' => ['type' => 'STRING', 'nullable' => true],
                'detected_email' => ['type' => 'STRING', 'nullable' => true],
                'detected_authors' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING'],
                            'institution' => ['type' => 'STRING'],
                        ],
                        'required' => ['name', 'institution'],
                    ]
                ],
                'detected_references' => ['type' => 'STRING', 'nullable' => true],
            ],
            'required' => [
                'structure_review',
                'abstract_review',
                'introduction_review',
                'method_review',
                'results_review',
                'conclusion_review',
                'bibliography_review',
                'general_suggestions',
                'detected_title',
                'detected_abstract',
                'detected_keywords',
                'detected_email',
                'detected_authors',
                'detected_references',
            ],
        ];

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
                'responseSchema' => $schema,
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
    protected function buildPrompt(string $text, bool $isExternal = false): string
    {
        // Limit text length to avoid token limits (approx 150k chars is more than enough for a full 20-30 page journal paper, ensuring the references section at the end is not truncated)
        $text = mb_substr($text, 0, 150000, 'UTF-8');

        if ($isExternal) {
            return 'Anda adalah asisten AI dari \'Cahaya Ilmu Bangsa\'.
            Tugas Anda adalah mengekstrak metadata dari naskah jurnal ilmiah yang diunggah berikut secara akurat.
            Gunakan Bahasa Indonesia yang formal dan profesional.

            PENTING: Anda harus mengembalikan hasil dalam format JSON murni dengan struktur kunci berikut:
            {
                "structure_review": null,
                "abstract_review": null,
                "introduction_review": null,
                "method_review": null,
                "results_review": null,
                "conclusion_review": null,
                "bibliography_review": null,
                "general_suggestions": null,
                "detected_title": "... (Judul artikel ilmiah lengkap, biasanya di baris atas)",
                "detected_abstract": "... (Teks abstrak lengkap)",
                "detected_keywords": "... (Kata kunci, pisahkan dengan koma, contoh: pendidikan, teknologi, pembelajaran)",
                "detected_email": "... (Email korespondensi utama yang ditemukan di naskah)",
                "detected_authors": [
                    { "name": "... (Nama Lengkap Penulis 1, sesuaikan EYD, hilangkan gelar akademik)", "institution": "... (Afiliasi/Instansi Penulis 1, jangan disingkat)" }
                ],
                "detected_references": "... (Daftar pustaka/referensi formal yang tercantum di bagian akhir naskah/daftar pustaka, ambil maksimal 20 entri pertama saja untuk mencegah kegagalan pemrosesan akibat batas panjang karakter, pisahkan per baris. PENTING: JANGAN mengekstrak kutipan di dalam paragraf seperti \'Kotler (2022)\' atau \'Putri et al. (2025)\')"
            }

            ATURAN SINTAKS JSON:
            1. Jangan menyertakan tanda petik ganda (") di dalam nilai teks JSON kecuali telah di-escape dengan backslash (\").
            2. Jangan menyertakan karakter kontrol seperti baris baru langsung. Gunakan \n untuk baris baru.
            3. Pastikan format JSON benar-benar valid secara sintaksis dan lengkap (tidak terpotong).
            4. Kolom-kolom review (structure_review, abstract_review, dll) HARUS diisi null.

            Isi jurnal untuk diekstrak:
            ---
            ' . $text . '
            ---';
        }

        return 'Anda adalah seorang reviewer jurnal profesional senior dari \'Cahaya Ilmu Bangsa\'. 
        Tugas Anda adalah memberikan review \'Pra-OJS\' (tahap awal sebelum masuk sistem OJS) yang ramah namun berstandar tinggi, sekaligus mengekstrak metadata artikel.
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
            "detected_title": "... (Judul artikel ilmiah lengkap, biasanya di baris atas)",
            "detected_abstract": "... (Teks abstrak lengkap)",
            "detected_keywords": "... (Kata kunci, pisahkan dengan koma, contoh: pendidikan, teknologi, pembelajaran)",
            "detected_email": "... (Email korespondensi utama yang ditemukan di naskah)",
            "detected_authors": [
                { "name": "... (Nama Lengkap Penulis 1, sesuaikan EYD, hilangkan gelar akademik)", "institution": "... (Afiliasi/Instansi Penulis 1, jangan disingkat)" }
            ],
            "detected_references": "... (Daftar pustaka/referensi formal yang tercantum di bagian akhir naskah/daftar pustaka, ambil maksimal 20 entri pertama saja untuk mencegah kegagalan pemrosesan akibat batas panjang karakter, pisahkan per baris. PENTING: JANGAN mengekstrak kutipan di dalam paragraf seperti \'Kotler (2022)\' atau \'Putri et al. (2025)\')"
        }

        ATURAN SINTAKS JSON:
        1. Jangan menyertakan tanda petik ganda (") di dalam nilai teks JSON kecuali tanda petik tersebut telah di-escape dengan backslash (\"). Sangat disarankan menggunakan tanda petik tunggal (\') jika ingin mengutip kata/istilah di dalam teks hasil review.
        2. Jangan menyertakan karakter kontrol seperti baris baru langsung di dalam string JSON. Gunakan \n untuk baris baru.
        3. Pastikan format JSON benar-benar valid secara sintaksis dan lengkap (tidak terpotong).

        Isi jurnal untuk di-review & diekstrak:
        ---
        ' . $text . '
        ---';
    }
}
