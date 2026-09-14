<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class KnowledgeLoaderService
{
    private string $path;

    public function __construct()
    {
        $this->path = storage_path('app/ai');
    }

    public function loadSystemPrompt(): string
    {
        $file = $this->path . '/system_prompt.txt';
        if (File::exists($file)) {
            return trim(File::get($file));
        }
        return "Kamu adalah AI Assistant.";
    }

    public function loadKnowledgeBase(): string
    {
        if (!File::exists($this->path)) {
            return "";
        }

        $knowledge = "";
        $files = File::files($this->path);

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            $filename = $file->getFilename();

            // Skip system_prompt as it's loaded separately
            if ($filename === 'system_prompt.txt') {
                continue;
            }

            if (in_array($extension, ['txt', 'md'])) {
                $knowledge .= "\n--- SOURCE: {$filename} ---\n";
                $knowledge .= trim(File::get($file->getRealPath()));
                $knowledge .= "\n-----------------------------\n";
            }
        }

        // Realtime Pricelist Injection from Database (Excluding Settings & Settlement Splitting)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('submission_pricings')) {
                $pricingTiers = \App\Models\SubmissionPricing::where('is_active', true)
                    ->where('category', '!=', 'setting')
                    ->orderBy('category')
                    ->orderBy('sort_order')
                    ->get();

                if ($pricingTiers->isNotEmpty()) {
                    $knowledge .= "\n--- SOURCE: realtime_database_pricelist.txt ---\n";
                    $knowledge .= "# PRICELIST RESMI REALTIME (DATABASE SISTEM)\n\n";
                    $knowledge .= "Berikut adalah daftar harga bersih resmi (total nominal yang dibayar penulis) yang aktif secara realtime di database sistem saat ini:\n\n";

                    foreach ($pricingTiers as $tier) {
                        $formattedPrice = 'Rp ' . number_format($tier->gross_amount, 0, ',', '.');
                        $desc = [];
                        if ($tier->min_authors !== null || $tier->max_authors !== null) {
                            if ($tier->max_authors === null) {
                                $desc[] = "≥{$tier->min_authors} penulis";
                            } elseif ($tier->min_authors === $tier->max_authors && $tier->min_authors > 0) {
                                $desc[] = "{$tier->min_authors} penulis";
                            } elseif ($tier->min_authors > 0) {
                                $desc[] = "{$tier->min_authors}-{$tier->max_authors} penulis";
                            }
                        }
                        if ($tier->with_doi !== null) {
                            $desc[] = $tier->with_doi ? 'Termasuk DOI Resmi' : 'Tanpa DOI';
                        }
                        $descStr = !empty($desc) ? ' (' . implode(', ', $desc) . ')' : '';
                        $knowledge .= "- **{$tier->tier_name}**: {$formattedPrice}{$descStr}\n";
                    }

                    $knowledge .= "\nKanda Putra wajib menggunakan data harga di atas sebagai acuan utama yang valid ketika menjawab pertanyaan mengenai tarif publikasi atau biaya layanan.\n";
                    $knowledge .= "-----------------------------\n";
                }
            }
        } catch (\Throwable $e) {
            // Silently fallback if database is not reachable
        }

        return trim($knowledge);
    }
}
