<?php

namespace App\Services;

use App\Contracts\PlagiarismParaphraseContract;
use App\Models\PlagiarismParaphrase;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiPlagiarismParaphraseService implements PlagiarismParaphraseContract
{
    /**
     * Perform academic paraphrase using Gemini.
     */
    public function paraphrase(PlagiarismParaphrase $paraphraseRecord): array
    {
        $plagiarismCheck = $paraphraseRecord->plagiarismCheck;
        $highlightedParts = $plagiarismCheck->report_data['highlighted_parts'] ?? [];

        if (empty($highlightedParts)) {
            throw new Exception("Tidak ada bagian teks dengan similarity tinggi yang ditemukan untuk diparafrase.");
        }

        $apiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-1.5-flash'));

        if (!$apiKey) {
            throw new Exception("API Key Gemini belum dikonfigurasi.");
        }

        $prompt = $this->buildPrompt($plagiarismCheck->title, $highlightedParts, $paraphraseRecord->original_score);

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

                // Handle server errors or rate limits
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
                throw new Exception("Koneksi ke AI terputus atau server sibuk. Detail: " . $e->getMessage());
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
            throw new Exception("Gagal memparsing JSON hasil parafrase dari AI.");
        }

        return $result;
    }

    /**
     * Build the structured prompt for academic paraphrase.
     */
    protected function buildPrompt(string $title, array $parts, float $originalScore): string
    {
        $partsJson = json_encode($parts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return "Anda adalah Editor Jurnal Akademik Senior profesional dan ahli bahasa ilmiah terkemuka dari penerbit 'Cahaya Ilmu Bangsa'.
Tugas Anda adalah melakukan parafrase akademis tingkat tinggi untuk menurunkan tingkat kemiripan tinggi (plagiarisme) dari daftar potongan teks yang terdeteksi mirip.

Judul Naskah Jurnal: \"{$title}\"
Skor Similarity Awal: {$originalScore}%

Berikut adalah daftar potongan teks dengan kemiripan tinggi yang wajib Anda parafrase:
---
{$partsJson}
---

PANDUAN & ATURAN EDITORIAL KHUSUS:
1. PARAFRASE HANYA teks yang diberikan dalam daftar di atas. Jangan menulis ulang bagian jurnal lainnya.
2. NADA & GAYA: Gunakan Bahasa Indonesia akademis tingkat tinggi yang elegan, mengalir natural, formal, dan tidak kaku (hindari frasa klise terjemahan mesin AI biasa seperti 'oleh karena itu', 'sangat penting', dll. jika tidak relevan secara kontekstual).
3. INTEGRITAS SITASI & JARGON: Pertahankan istilah teknis khusus (technical jargon), nama tokoh/metode, dan sitasi akademis (misalnya: 'Sujatmiko dkk., 2021' atau '[15]' atau 'Menurut Rahma (2020)...') - JANGAN HILANGKAN ATAU UBAH sitasi ini.
4. ESTIMASI SIMILARITY BARU: Berikan perkiraan (estimasi) persentase similarity baru untuk keseluruhan naskah setelah dilakukan parafrase pada bagian-bagian tersebut secara realistis (nilai ini harus lebih kecil secara logis dari {$originalScore}%, umumnya di kisaran 10% s.d 20% jika parafrase Anda sangat baik).
5. CATATAN EDITOR: Berikan penjelasan akademis singkat tentang perbaikan yang Anda lakukan agar penulis memahami struktur kalimat baru yang direkomendasikan.

PENTING: Format respons harus berupa JSON murni dengan struktur berikut:
{
    \"estimated_new_score\": 15.5,
    \"improvements\": [
        {
            \"original\": \"... (Teks asli dari input) ...\",
            \"improved\": \"... (Teks hasil parafrase akademik Anda) ...\",
            \"explanation\": \"... (Penjelasan perubahan dari sudut pandang editor senior) ...\"
        }
    ]
}";
    }
}
