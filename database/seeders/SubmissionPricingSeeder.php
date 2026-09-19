<?php

namespace Database\Seeders;

use App\Models\SubmissionPricing;
use App\Services\SubmissionPricingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SubmissionPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Skema Tarif Kelipatan 5 Author & Maksimal 30 Author.
     */
    public function run(): void
    {
        $tiers = [
            // ==========================================
            // JURNAL NASIONAL (ISSN)
            // ==========================================
            [
                'key' => 'issn_1_5_no_doi',
                'category' => 'issn',
                'tier_name' => 'ISSN (1-5 Author)',
                'min_authors' => 1,
                'max_authors' => 5,
                'with_doi' => false,
                'gross_amount' => 60000.0,
                'developer_gross_share' => 5000.0,
                'notes' => 'Naskah nasional 1-5 penulis tanpa DOI',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'issn_1_5_with_doi',
                'category' => 'issn',
                'tier_name' => 'ISSN + DOI (1-5 Author)',
                'min_authors' => 1,
                'max_authors' => 5,
                'with_doi' => true,
                'gross_amount' => 80000.0,
                'developer_gross_share' => 5000.0,
                'notes' => 'Naskah nasional 1-5 penulis dengan DOI',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'issn_6_10_no_doi',
                'category' => 'issn',
                'tier_name' => 'ISSN (6-10 Author)',
                'min_authors' => 6,
                'max_authors' => 10,
                'with_doi' => false,
                'gross_amount' => 100000.0,
                'developer_gross_share' => 10000.0,
                'notes' => 'Naskah nasional 6-10 penulis tanpa DOI',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'issn_6_10_with_doi',
                'category' => 'issn',
                'tier_name' => 'ISSN + DOI (6-10 Author)',
                'min_authors' => 6,
                'max_authors' => 10,
                'with_doi' => true,
                'gross_amount' => 120000.0,
                'developer_gross_share' => 10000.0,
                'notes' => 'Naskah nasional 6-10 penulis dengan DOI',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'issn_11_15_with_doi',
                'category' => 'issn',
                'tier_name' => 'ISSN + DOI (11-15 Author)',
                'min_authors' => 11,
                'max_authors' => 15,
                'with_doi' => true,
                'gross_amount' => 150000.0,
                'developer_gross_share' => 20000.0,
                'notes' => 'Naskah nasional 11-15 penulis (+ DOI otomatis)',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'key' => 'issn_16_20_with_doi',
                'category' => 'issn',
                'tier_name' => 'ISSN + DOI (16-20 Author)',
                'min_authors' => 16,
                'max_authors' => 20,
                'with_doi' => true,
                'gross_amount' => 200000.0,
                'developer_gross_share' => 30000.0,
                'notes' => 'Naskah nasional 16-20 penulis (+ DOI otomatis)',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'key' => 'issn_21_25_with_doi',
                'category' => 'issn',
                'tier_name' => 'ISSN + DOI (21-25 Author)',
                'min_authors' => 21,
                'max_authors' => 25,
                'with_doi' => true,
                'gross_amount' => 250000.0,
                'developer_gross_share' => 40000.0,
                'notes' => 'Naskah nasional 21-25 penulis (+ DOI otomatis)',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'key' => 'issn_26_30_with_doi',
                'category' => 'issn',
                'tier_name' => 'ISSN + DOI (26-30 Author)',
                'min_authors' => 26,
                'max_authors' => 30,
                'with_doi' => true,
                'gross_amount' => 300000.0,
                'developer_gross_share' => 50000.0,
                'notes' => 'Naskah nasional 26-30 penulis (+ DOI otomatis)',
                'is_active' => true,
                'sort_order' => 8,
            ],

            // ==========================================
            // JURNAL INTERNASIONAL (Free DOI di semua tier, opsi DOI tetap opsional)
            // ==========================================
            [
                'key' => 'international_1_5',
                'category' => 'international',
                'tier_name' => 'International 1-5 Author (Free DOI)',
                'min_authors' => 1,
                'max_authors' => 5,
                'with_doi' => null, // Free DOI: berlaku baik opsi dengan atau tanpa DOI
                'gross_amount' => 150000.0,
                'developer_gross_share' => 20000.0,
                'notes' => 'Naskah internasional 1-5 penulis (Free DOI)',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'key' => 'international_6_10',
                'category' => 'international',
                'tier_name' => 'International 6-10 Author (Free DOI)',
                'min_authors' => 6,
                'max_authors' => 10,
                'with_doi' => null,
                'gross_amount' => 200000.0,
                'developer_gross_share' => 30000.0,
                'notes' => 'Naskah internasional 6-10 penulis (Free DOI)',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'key' => 'international_11_15',
                'category' => 'international',
                'tier_name' => 'International 11-15 Author (Free DOI)',
                'min_authors' => 11,
                'max_authors' => 15,
                'with_doi' => null,
                'gross_amount' => 250000.0,
                'developer_gross_share' => 40000.0,
                'notes' => 'Naskah internasional 11-15 penulis (Free DOI)',
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'key' => 'international_16_20',
                'category' => 'international',
                'tier_name' => 'International 16-20 Author (Free DOI)',
                'min_authors' => 16,
                'max_authors' => 20,
                'with_doi' => null,
                'gross_amount' => 300000.0,
                'developer_gross_share' => 50000.0,
                'notes' => 'Naskah internasional 16-20 penulis (Free DOI)',
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'key' => 'international_21_25',
                'category' => 'international',
                'tier_name' => 'International 21-25 Author (Free DOI)',
                'min_authors' => 21,
                'max_authors' => 25,
                'with_doi' => null,
                'gross_amount' => 350000.0,
                'developer_gross_share' => 60000.0,
                'notes' => 'Naskah internasional 21-25 penulis (Free DOI)',
                'is_active' => true,
                'sort_order' => 13,
            ],
            [
                'key' => 'international_26_30',
                'category' => 'international',
                'tier_name' => 'International 26-30 Author (Free DOI)',
                'min_authors' => 26,
                'max_authors' => 30,
                'with_doi' => null,
                'gross_amount' => 400000.0,
                'developer_gross_share' => 70000.0,
                'notes' => 'Naskah internasional 26-30 penulis (Free DOI)',
                'is_active' => true,
                'sort_order' => 14,
            ],

            // ==========================================
            // ADD-ON & LAYANAN
            // ==========================================
            [
                'key' => 'addon_doi',
                'category' => 'addon',
                'tier_name' => 'Add-on DOI Repository Identifier',
                'min_authors' => 0,
                'max_authors' => 0,
                'with_doi' => true,
                'gross_amount' => 20000.0,
                'developer_gross_share' => 5000.0,
                'notes' => 'Biaya penambahan nomor DOI repository identifier',
                'is_active' => true,
                'sort_order' => 15,
            ],
            [
                'key' => 'service_replace_pdf',
                'category' => 'addon',
                'tier_name' => 'Ganti PDF Naskah',
                'min_authors' => 0,
                'max_authors' => 0,
                'with_doi' => false,
                'gross_amount' => 25000.0,
                'developer_gross_share' => 5000.0,
                'notes' => 'Biaya administrasi penggantian file PDF naskah',
                'is_active' => true,
                'sort_order' => 16,
            ],

            // ==========================================
            // GLOBAL SETTINGS
            // ==========================================
            [
                'key' => 'setting_member_discount',
                'category' => 'setting',
                'tier_name' => 'Diskon Membership per Naskah',
                'min_authors' => null,
                'max_authors' => null,
                'with_doi' => null,
                'gross_amount' => 10000.0,
                'developer_gross_share' => 0.0,
                'notes' => 'Potongan harga khusus pengguna berstatus Member',
                'is_active' => true,
                'sort_order' => 17,
            ],
            [
                'key' => 'setting_mdr_rate',
                'category' => 'setting',
                'tier_name' => 'Biaya MDR QRIS (0.7% = 0.007)',
                'min_authors' => null,
                'max_authors' => null,
                'with_doi' => null,
                'gross_amount' => 0.007,
                'developer_gross_share' => 0.0,
                'notes' => 'Tarif MDR biaya transaksi QRIS (default 0.7%)',
                'is_active' => true,
                'sort_order' => 18,
            ],
        ];

        foreach ($tiers as $tier) {
            SubmissionPricing::updateOrCreate(
                ['key' => $tier['key']],
                $tier
            );
        }

        // Hapus otomatis semua tier lama/usang yang tidak ada di daftar resmi di atas
        $validKeys = collect($tiers)->pluck('key')->toArray();
        SubmissionPricing::whereNotIn('key', $validKeys)->delete();

        Cache::forget('submission_pricing_tiers');
        Cache::forget('submission_pricing_settings');
        SubmissionPricingService::clearMemoized();
    }
}
