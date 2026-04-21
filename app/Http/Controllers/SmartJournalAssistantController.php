<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class SmartJournalAssistantController extends Controller
{
    public function index()
    {
        return view('filament.pages.smart-assistant');
    }

    public function review(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,docx,doc|max:10240', // max 10MB
        ], [
            'document.required' => 'Silakan unggah dokumen.',
            'document.mimes' => 'Format file yang didukung saat ini adalah PDF dan DOCX.',
            'document.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        if (!env('GEMINI_API_KEY')) {
            return back()->with('error', 'API Key Gemini belum diatur di server.');
        }

        $file = $request->file('document');
        $extension = strtolower($file->getClientOriginalExtension());
        $filePath = $file->getRealPath();

        $text = '';

        try {
            if ($extension === 'pdf') {
                $parser = new Parser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();
            } elseif ($extension === 'docx') {
                $text = $this->extractTextFromDocx($filePath);
            } else {
                return back()->with('error', 'Format file .doc versi lama saat ini belum didukung secara penuh. Mohon simpan sebagai .docx atau .pdf.');
            }

            // Batasi panjang teks jika terlalu panjang
            $text = substr(trim($text), 0, 150000);

            if (empty($text)) {
                return back()->with('error', 'Tidak ada teks yang dapat diekstrak dari dokumen ini. Pastikan dokumen bukan hasil scan gambar tanpa OCR.');
            }

            // Panggil API Gemini
            $model = env('GEMINI_MODEL', 'gemini-flash-latest');
            $response = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . env('GEMINI_API_KEY'), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Review jurnal berikut secara sederhana.\n\nPeriksa:\n- struktur jurnal\n- abstrak\n- daftar pustaka\n- saran revisi umum\n\nBerikan hasil singkat,\nprofesional,\ndan mudah dipahami.\n\nIsi jurnal:\n" . $text]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $review = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Gagal membaca response dari AI.';
                return back()->with('review', $review);
            } else {
                return back()->with('error', 'Gagal menghubungi API Gemini: ' . $response->body());
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }
    }

    private function extractTextFromDocx($filePath)
    {
        $text = '';
        $zip = new \ZipArchive;
        if ($zip->open($filePath) === TRUE) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                $text = strip_tags(str_replace(['<w:p>', '<w:p ', '</w:p>'], ["\n\n", "\n\n", ""], $data));
            } else {
                $zip->close();
            }
        }
        return $text;
    }
}
