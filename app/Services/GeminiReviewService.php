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

        $isExternal = method_exists($record, 'isExternal') && $record->isExternal();
        return $this->extractMetadataFromFile($filePath, $isExternal, $record);
    }

    /**
     * Extract metadata directly from a file path.
     */
    public function extractMetadataFromFile(string $filePath, bool $isExternal = false, ?Model $record = null): array
    {
        $text = $this->extractText($filePath, $record);
        
        if (empty($text)) {
            throw new Exception("Gagal mengekstrak teks dari dokumen.");
        }

        $apiKey = config('services.gemini.review_key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        if (!$apiKey) {
            throw new Exception("API Key Gemini belum diatur.");
        }

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

        $decoded = $this->decodeGeminiJson($rawContent);
        return $this->sanitizeExtractedResults($decoded);
    }

    /**
     * Sanitize and normalize extracted metadata (clean double numbering, spacing glitches, author artifacts, etc.)
     */
    public function sanitizeExtractedResults(array $results): array
    {
        // 1. Sanitize References
        if (!empty($results['detected_references']) && is_string($results['detected_references'])) {
            $lines = preg_split('/\r\n|\r|\n/', $results['detected_references']);
            $cleanedLines = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                // Clean double/nested numbering prefixes:
                // e.g. "1. [1] Author..." -> "[1] Author..."
                $line = preg_replace('/^\s*\d+[\.\)]\s*(\[\d+\])/u', '$1', $line);
                // e.g. "1. (1) Author..." -> "1. Author..."
                $line = preg_replace('/^\s*\d+[\.\)]\s*\((\d+)\)\s*/u', '$1. ', $line);
                // e.g. "1. 1. Author..." or "1. 1) Author..." -> "1. Author..."
                $line = preg_replace('/^\s*(\d+)[\.\)]\s*\1[\.\)]\s*/u', '$1. ', $line);
                $line = preg_replace('/^\s*(\d+)[\.\)]\s*\d+[\.\)]\s*/u', '$1. ', $line);
                // e.g. "[1] [1] Author..." -> "[1] Author..."
                $line = preg_replace('/^\s*\[(\d+)\]\s*\[\1\]\s*/u', '[$1] ', $line);
                // e.g. "[1] 1. Author..." -> "[1] Author..."
                $line = preg_replace('/^\s*\[(\d+)\]\s*\d+[\.\)]\s*/u', '[$1] ', $line);

                // Fix double dots or weird spaced initials (e.g. "M. . W." -> "M. W.")
                $line = preg_replace('/\b([A-Za-z])\.\s*\.\s*/u', '$1. ', $line);
                $line = preg_replace('/(?<!\.)\.\.(?!\.)/u', '.', $line);

                // Fix missing spaces after comma if followed immediately by letter/number (e.g. "Aini,A." -> "Aini, A.")
                $line = preg_replace('/,([A-Za-z0-9])/u', ', $1', $line);

                // Fix missing space after closing parenthesis/bracket dot if followed by uppercase (e.g. "(2007).Sistem" -> "(2007). Sistem")
                $line = preg_replace('/(\)|\])\.([A-Za-z])/u', '$1. $2', $line);

                // Fix missing space after '&' (e.g. "&Wibowo" -> "& Wibowo")
                $line = preg_replace('/&([A-Za-z])/u', '& $1', $line);

                // Collapse multiple spaces
                $line = preg_replace('/[ \t]+/u', ' ', $line);

                $cleanedLines[] = trim($line);
            }

            $results['detected_references'] = implode("\n", $cleanedLines);
        }

        // 2. Sanitize Authors
        if (!empty($results['detected_authors']) && is_array($results['detected_authors'])) {
            $cleanedAuthors = [];
            foreach ($results['detected_authors'] as $author) {
                if (!is_array($author)) {
                    continue;
                }

                $name = trim($author['name'] ?? '');
                $institution = trim($author['institution'] ?? '');

                if ($name === '') {
                    continue;
                }

                // Clean trailing footnote numbers, asterisks, symbols from author name
                $name = preg_replace('/[\d\*\#\†\‡\§\^]+$/u', '', $name);
                $name = trim($name, " ,;\t\n\r\0\x0B");

                // Clean double dots and weird initial spacing in names (e.g. "M. . W." -> "M. W.")
                $name = preg_replace('/\b([A-Za-z])\.\s*\.\s*/u', '$1. ', $name);
                $name = preg_replace('/(?<!\.)\.\.(?!\.)/u', '.', $name);

                // Clean academic titles if prefix (e.g. "Prof. Dr. Ir. Budi" -> "Budi")
                $name = preg_replace('/^(?:(?:Prof|Dr|Drs|Ir|H|Hj)\.?\s*)+/iu', '', $name);

                // Clean academic degree suffixes if present (e.g. ", M.Kom, Ph.D")
                $name = preg_replace('/(?:,\s*(?:M\.?[A-Za-z]+|S\.?[A-Za-z]+|Ph\.?D|Dr\.?[A-Za-z]*|SE|MM|Ak|CA|CPA|Sp\.?[A-Za-z]*))+$/iu', '', $name);

                // Collapse whitespace
                $name = preg_replace('/\s+/u', ' ', $name);
                $name = trim($name, " ,;\t\n\r\0\x0B");

                // Sanitize Institution
                $institution = preg_replace('/^[\d\*\#\†\‡\§\^]+\s*/u', '', $institution);
                $institution = preg_replace('/[\*\#\†\‡\§\^]+/u', '', $institution);
                $institution = preg_replace('/,([A-Za-z])/u', ', $1', $institution);
                $institution = preg_replace('/\s+/u', ' ', $institution);
                $institution = trim($institution, " ,;\t\n\r\0\x0B");

                $cleanedAuthors[] = [
                    'name' => $name,
                    'institution' => $institution,
                ];
            }
            $results['detected_authors'] = $cleanedAuthors;
        }

        // 3. Sanitize Title & Abstract & Keywords
        if (!empty($results['detected_title']) && is_string($results['detected_title'])) {
            $title = preg_replace('/\s+/u', ' ', $results['detected_title']);
            $title = preg_replace('/,([A-Za-z])/u', ', $1', $title);
            $results['detected_title'] = trim($title);
        }

        if (!empty($results['detected_abstract']) && is_string($results['detected_abstract'])) {
            $abstract = preg_replace('/[ \t]+/u', ' ', $results['detected_abstract']);
            $results['detected_abstract'] = trim($abstract);
        }

        if (!empty($results['detected_keywords']) && is_string($results['detected_keywords'])) {
            $keywords = preg_replace('/\s+/u', ' ', $results['detected_keywords']);
            $keywords = preg_replace('/,([A-Za-z0-9])/u', ', $1', $keywords);
            $results['detected_keywords'] = trim($keywords);
        }

        return $results;
    }

    /**
     * Extract text from PDF or DOCX.
     */
    protected function extractText(string $filePath, ?Model $record = null): string
    {
        $disk = 'public';
        $absolutePath = Storage::disk($disk)->path($filePath);

        if (!Storage::disk($disk)->exists($filePath)) {
            // Check if file was stored with renamed ID format
            if ($record && $record->id) {
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $renamedPath = "manuscripts/file-{$record->id}" . ($extension ? ".{$extension}" : "");
                if (Storage::disk($disk)->exists($renamedPath)) {
                    $absolutePath = Storage::disk($disk)->path($renamedPath);
                    $filePath = $renamedPath;
                }
            }
        }

        if (!file_exists($absolutePath)) {
            if (file_exists(storage_path('app/public/' . $filePath))) {
                $absolutePath = storage_path('app/public/' . $filePath);
            } elseif (Storage::exists($filePath)) {
                $absolutePath = Storage::path($filePath);
            }
        }

        if (!file_exists($absolutePath)) {
            throw new Exception("File naskah tidak ditemukan pada server: {$filePath}");
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

        $extractionGuide = '
PANDUAN PENTING EKSTRAKSI & RESTORASI TEKS:
1. PERBAIKI SPASI KATA YANG HILANG (PENTING): Teks dari ekstraksi PDF seringkali kehilangan spasi antar kata atau tanda baca (contoh: \'SistemInformasiGeografisPengertiandanAplikasinya\' atau \'Aini,A.(2007).IntegrasiMateri...\'). Anda WAJIB memisahkan kata-kata yang menempel dan menambahkan spasi yang wajar dan tepat pada semua bagian (Judul, Abstrak, Penulis, Afiliasi, Kata Kunci, dan Referensi) agar terbaca jelas dan profesional.
2. DAFTAR PUSTAKA (detected_references):
   - JANGAN PERNAH membuat penomoran dobel/ganda seperti \'1. [1]\', \'2. [2]\', atau \'1. 1.\'.
   - Jika daftar pustaka asli menggunakan kurung siku \'[1]\', tulis \'[1] Nama Penulis, ...\'. Jika menggunakan nomor biasa \'1.\', tulis \'1. Nama Penulis, ...\'. JANGAN menggabungkan nomor list di depannya.
   - Pisahkan setiap entri referensi dengan baris baru (\n).
   - Perbaiki spasi kata dan tanda baca yang menempel pada setiap entri referensi (contoh: \'Nama,A.(2020).JudulBuku\' -> \'Nama, A. (2020). Judul Buku\').
   - Ambil maksimal 20 entri pertama daftar pustaka formal di akhir naskah. JANGAN mengambil kutipan di dalam paragraf (in-text citation seperti \'Kotler (2022)\').
3. NAMA PENULIS & AFILIASI (detected_authors):
   - Bersihkan angka footnote/superskrip/bintang (*) yang menempel pada nama (contoh: \'Ahmad Dahlan1*\' -> \'Ahmad Dahlan\').
   - Perbaiki inisial nama yang typo atau memiliki spasi/titik ganda (contoh: \'M. . W. E. NP\' -> \'M. W. E. NP\').
   - Hilangkan gelar akademik (Prof., Dr., M.Kom, S.T., Ph.D, dsb).
   - Nama harus dalam format penulisan EYD/Title Case yang benar.
   - Afiliasi/instansi ditulis lengkap (jangan disingkat jika memungkinkan) dan perbaiki spasi yang hilang.';

        if ($isExternal) {
            return 'Anda adalah asisten AI dari \'Cahaya Ilmu Bangsa\'.
            Tugas Anda adalah mengekstrak metadata dari naskah jurnal ilmiah yang diunggah berikut secara akurat.
            Gunakan Bahasa Indonesia yang formal dan profesional.

            ' . $extractionGuide . '

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
                "detected_title": "... (Judul artikel ilmiah lengkap, perbaiki spasi kata yang hilang)",
                "detected_abstract": "... (Teks abstrak lengkap, perbaiki spasi kata yang hilang)",
                "detected_keywords": "... (Kata kunci, pisahkan dengan koma, contoh: pendidikan, teknologi, pembelajaran)",
                "detected_email": "... (Email korespondensi utama yang ditemukan di naskah)",
                "detected_authors": [
                    { "name": "... (Nama Lengkap Penulis 1, sesuaikan EYD, hilangkan gelar akademik dan angka footnote)", "institution": "... (Afiliasi/Instansi Penulis 1, jangan disingkat)" }
                ],
                "detected_references": "... (Daftar pustaka/referensi formal di akhir naskah, maksimal 20 entri, tanpa nomor ganda, pisahkan per baris \\n)"
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

        ' . $extractionGuide . '
        
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
            "detected_title": "... (Judul artikel ilmiah lengkap, perbaiki spasi kata yang hilang)",
            "detected_abstract": "... (Teks abstrak lengkap, perbaiki spasi kata yang hilang)",
            "detected_keywords": "... (Kata kunci, pisahkan dengan koma, contoh: pendidikan, teknologi, pembelajaran)",
            "detected_email": "... (Email korespondensi utama yang ditemukan di naskah)",
            "detected_authors": [
                { "name": "... (Nama Lengkap Penulis 1, sesuaikan EYD, hilangkan gelar akademik dan angka footnote)", "institution": "... (Afiliasi/Instansi Penulis 1, jangan disingkat)" }
            ],
            "detected_references": "... (Daftar pustaka/referensi formal di akhir naskah, maksimal 20 entri, tanpa nomor ganda, pisahkan per baris \\n)"
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
