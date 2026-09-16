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
                            'institution' => ['type' => 'STRING', 'nullable' => true],
                        ],
                        'required' => ['name'],
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
        return $this->sanitizeExtractedResults($decoded, $text);
    }

    /**
     * Sanitize and normalize extracted metadata (clean double numbering, spacing glitches, author artifacts, etc.)
     */
    public function sanitizeExtractedResults(array $results, string $sourceText = ''): array
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
        $rawAuthors = $results['detected_authors'] ?? $results['authors'] ?? $results['author'] ?? $results['penulis'] ?? null;
        $authorList = [];

        if (is_string($rawAuthors)) {
            // Raw string containing author names separated by commas/newlines
            $splitNames = preg_split('/[,\n;]|\s+dan\s+|\s+and\s+/iu', $rawAuthors);
            foreach ($splitNames as $namePart) {
                $trimmed = trim($namePart);
                if ($trimmed !== '') {
                    $authorList[] = ['name' => $trimmed, 'institution' => ''];
                }
            }
        } elseif (is_array($rawAuthors)) {
            foreach ($rawAuthors as $author) {
                if (is_string($author)) {
                    $trimmed = trim($author);
                    if ($trimmed !== '') {
                        $authorList[] = ['name' => $trimmed, 'institution' => ''];
                    }
                } elseif (is_array($author)) {
                    $authorList[] = $author;
                }
            }
        }

        $cleanedAuthors = [];
        $blacklistedNames = [
            'author', 'penulis', 'copyright', 'publish by', 'published by', 'plagiarism checker',
            'reviewer', 'abstract', 'abstrak', 'keywords', 'kata kunci', 'e-mail', 'email',
            'issn', 'vol', 'volume', 'anonymous', 'null', 'none', 'n/a', 'tanpa nama'
        ];

        foreach ($authorList as $author) {
            $name = trim($author['name'] ?? $author['author'] ?? $author['penulis'] ?? $author['nama'] ?? '');
            $institution = trim($author['institution'] ?? $author['affiliation'] ?? $author['instansi'] ?? $author['afiliasi'] ?? '');

            if ($name === '') {
                continue;
            }

            // Clean leading and trailing footnote numbers, asterisks, symbols from author name
            $name = preg_replace('/^[\d\s,\*\#\†\‡\§\^]+/u', '', $name);
            $name = preg_replace('/[\d\*\#\†\‡\§\^]+$/u', '', $name);
            $name = preg_replace('/(?<=[a-zA-Z])\s*\d+\s*(?=,|$|\s)/u', '', $name);
            $name = trim($name, " ,;\t\n\r\0\x0B");

            // Clean double dots and weird initial spacing in names (e.g. "M. . W." -> "M. W.")
            $name = preg_replace('/\b([A-Za-z])\.\s*\.\s*/u', '$1. ', $name);
            $name = preg_replace('/(?<!\.)\.\.(?!\.)/u', '.', $name);

            // Clean academic titles if prefix (e.g. "Prof. Dr. Ir. Budi" -> "Budi", but keep names like "Hilmi")
            $name = preg_replace('/^(?:(?:Prof|Dr|Drs|Ir|Hj)\b\.?\s*|H\.\s+)+/iu', '', $name);

            // Clean academic degree suffixes if present (e.g. ", M.Kom, Ph.D")
            $name = preg_replace('/(?:,\s*(?:M\.?[A-Za-z]+|S\.?[A-Za-z]+|Ph\.?D|Dr\.?[A-Za-z]*|SE|MM|Ak|CA|CPA|Sp\.?[A-Za-z]*))+$/iu', '', $name);

            // Collapse whitespace
            $name = preg_replace('/\s+/u', ' ', $name);
            $name = trim($name, " ,;\t\n\r\0\x0B");

            if (mb_strlen($name) < 2) {
                continue;
            }

            $lowerName = strtolower($name);
            if (in_array($lowerName, $blacklistedNames) || str_contains($lowerName, 'copyright') || str_contains($lowerName, 'publish by') || str_contains($lowerName, 'published by') || str_contains($lowerName, 'plagiarism') || str_contains($lowerName, 'checker')) {
                continue;
            }

            // Filter out names that look like filenames or emails
            if (preg_match('/\.[a-z0-9]{2,5}$/i', $name) || str_contains($name, '@')) {
                continue;
            }

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

        // 2b. Heuristic validation & enrichment:
        // If Gemini missed authors or truncated (e.g. document has 31 authors but Gemini only returned 18),
        // or if Gemini returned empty authors, use the deterministic heuristic author extraction.
        if (!empty($sourceText)) {
            $heuristicAuthors = $this->extractAuthorsFromText($sourceText, $results['detected_title'] ?? null);
            if (!empty($heuristicAuthors)) {
                if (empty($cleanedAuthors) || count($heuristicAuthors) > count($cleanedAuthors)) {
                    $geminiAffilMap = [];
                    foreach ($cleanedAuthors as $ga) {
                        $lower = strtolower(trim($ga['name'] ?? ''));
                        if (!empty($ga['institution'])) {
                            $geminiAffilMap[$lower] = $ga['institution'];
                        }
                    }

                    $commonAffil = '';
                    foreach ($cleanedAuthors as $ga) {
                        if (!empty($ga['institution'])) {
                            $commonAffil = $ga['institution'];
                            break;
                        }
                    }

                    foreach ($heuristicAuthors as &$ha) {
                        $lower = strtolower(trim($ha['name'] ?? ''));
                        if (isset($geminiAffilMap[$lower])) {
                            $ha['institution'] = $geminiAffilMap[$lower];
                        } elseif (empty($ha['institution']) && !empty($commonAffil)) {
                            $ha['institution'] = $commonAffil;
                        }
                    }
                    unset($ha);

                    $cleanedAuthors = $heuristicAuthors;
                }
            }
        }

        $results['detected_authors'] = $cleanedAuthors;

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
     * Deterministic heuristic to extract author names and affiliations from first page text.
     */
    public function extractAuthorsFromText(string $text, ?string $title = null): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Limit to first section up to Abstrak / Abstract
        $target = $text;
        if (preg_match('/\b(abstrak|abstract)\b/iu', $text, $m, PREG_OFFSET_CAPTURE)) {
            $target = substr($text, 0, $m[0][1]);
        }

        // If title is known, extract everything AFTER the title
        if (!empty($title)) {
            $words = preg_split('/\s+/u', trim($title));
            $words = array_filter($words, fn($w) => mb_strlen($w) > 2);
            $words = array_values($words);

            for ($len = min(5, count($words)); $len >= 1; $len--) {
                $slice = array_slice($words, -$len);
                $pattern = '/' . implode('[\s\n\r]+', array_map(fn($w) => preg_quote($w, '/'), $slice)) . '/iu';
                if (preg_match($pattern, $target, $m, PREG_OFFSET_CAPTURE)) {
                    $target = substr($target, $m[0][1] + strlen($m[0][0]));
                    break;
                }
            }
        }

        // Split into lines
        $rawLines = explode("\n", $target);
        $lines = [];
        foreach ($rawLines as $l) {
            $t = trim($l);
            if ($t !== '') {
                $lines[] = $t;
            }
        }

        $affilPatterns = [
            '/jurusan/i', '/fakultas/i', '/universitas/i', '/prodi/i', '/program studi/i',
            '/institut/i', '/sekolah tinggi/i', '/politeknik/i', '/akademi/i',
            '/department/i', '/faculty/i', '/university/i', '/institute/i', '/college/i',
            '/e-?mail/i', '/@/'
        ];

        $authorLines = [];
        $affilLines = [];
        $foundAffil = false;

        foreach ($lines as $line) {
            $isAffil = false;
            foreach ($affilPatterns as $p) {
                if (preg_match($p, $line)) {
                    $isAffil = true;
                    $foundAffil = true;
                    break;
                }
            }

            if ($foundAffil) {
                if (!preg_match('/@|e-?mail/i', $line)) {
                    $cleanAffil = preg_replace('/^[\d\s,\*\#\†\‡\§\^]+/u', '', $line);
                    if (mb_strlen($cleanAffil) > 3) {
                        $affilLines[] = $cleanAffil;
                    }
                }
            } else {
                $authorLines[] = $line;
            }
        }

        $commonAffiliation = implode(', ', array_unique($affilLines));

        $authorBlock = implode(' ', $authorLines);
        // Replace superscript numbers and symbols
        $authorBlock = preg_replace('/(?<=[a-zA-Z])\s*\d+\s*(?=,|$|\s)/u', '', $authorBlock);
        $authorBlock = preg_replace('/\b\d+\b/u', '', $authorBlock);
        $authorBlock = preg_replace('/[\*\#\†\‡\§\^]+/u', '', $authorBlock);

        $rawCandidates = preg_split('/,|;|\s+dan\s+|\s+and\s+|\s+&\s+/iu', $authorBlock);
        $authors = [];

        foreach ($rawCandidates as $candidate) {
            $name = trim($candidate, " \t\n\r\0\x0B,.;-");
            $name = preg_replace('/^(?:(?:Prof|Dr|Drs|Ir|Hj)\b\.?\s*|H\.\s+)+/iu', '', $name);
            $name = preg_replace('/(?:,\s*(?:M\.?[A-Za-z]+|S\.?[A-Za-z]+|Ph\.?D|Dr\.?[A-Za-z]*|SE|MM|Ak|CA|CPA|Sp\.?[A-Za-z]*))+$/iu', '', $name);
            $name = preg_replace('/\s+/u', ' ', $name);
            $name = trim($name);

            // Format to Title Case if lowercase
            if (!empty($name) && strtolower($name) === $name) {
                $name = ucwords($name);
            }

            if (mb_strlen($name) >= 3 && preg_match('/^[A-Za-z\s\.\'\-]+$/u', $name)) {
                if (!preg_match('/^(author|penulis|abstract|abstrak|keywords|e-?mail|copyright|publish|issn|vol|volume)/iu', $name)) {
                    $authors[] = [
                        'name' => $name,
                        'institution' => $commonAffiliation,
                    ];
                }
            }
        }

        return $authors;
    }

    /**
     * Public helper to extract authors directly from a document using heuristic text parsing.
     */
    public function extractAuthorsFromDocument(string $filePath, ?string $title = null, ?Model $record = null): array
    {
        try {
            $text = $this->extractText($filePath, $record);
            if (!empty($text)) {
                return $this->extractAuthorsFromText($text, $title);
            }
        } catch (\Throwable $e) {
            // Ignore error
        }
        return [];
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
3. NAMA PENULIS & AFILIASI (detected_authors) - SANGAT PENTING:
   - Lokasi Penulis: Penulis terletak di halaman 1 naskah, tepat di bawah Judul Artikel dan di atas Abstrak/Afiliasi.
   - Pada teks PDF yang diekstrak, nama penulis seringkali diikuti oleh angka superskrip footnote di baris baru (contoh: "Nichar F. Aruperes\n1\n, Ivonne S. Saerang\n2\n, Rudy S. Wenas\n3"). Anda WAJIB mengekstrak SEMUA nama penulis ini satu per satu! Hapus angka 1, 2, 3 tersebut.
   - PENTING: Jika terdapat BANYAK penulis (misalnya 15, 20, 30, hingga 35+ penulis), Anda WAJIB mengekstrak SEMUA nama penulis tanpa terkecuali dari nama pertama hingga nama terakhir! JANGAN PERNAH berhenti di tengah jalan atau menyingkat daftar penulis.
   - Bersihkan tanda footnote/bintang (*) atau simbol lainnya yang menempel pada nama.
   - Hilangkan gelar akademik (Prof., Dr., Drs., Ir., M.Kom, S.T., Ph.D, SE, MM, dsb).
   - Nama harus dalam format penulisan EYD/Title Case yang benar.
   - JANGAN PERNAH mengembalikan array kosong untuk detected_authors jika terdapat nama penulis di bawah judul artikel!
   - JANGAN mengambil teks metadata penerbitan atau footer/sidebar (seperti \'Copyright : author\', \'Publish by\', \'Plagiarism checker\', atau \'Article History\') sebagai nama penulis.
   - Afiliasi/instansi ditulis lengkap (jangan disingkat jika memungkinkan). Jika beberapa penulis memiliki afiliasi yang sama, cantumkan afiliasi tersebut pada masing-masing penulis.';

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
