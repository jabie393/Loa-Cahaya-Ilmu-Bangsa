<?php

namespace Database\Seeders;

use App\Models\SubmissionPricing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SubmissionPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
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
                'notes' => 'Naskah nasional 11-15 penulis (+ DOI)',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'key' => 'issn_16_20_with_doi',
                'category' => 'issn',
                'tier_name' => 'ISSN + DOI (16-20 Author)',
                'min_authors' => 16,
                'max_authors' => null,
                'with_doi' => true,
                'gross_amount' => 200000.0,
                'developer_gross_share' => 30000.0,
                'notes' => 'Naskah nasional ≥16 penulis (+ DOI)',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'key' => 'international_1_10',
                'category' => 'international',
                'tier_name' => 'International 1-10 Author + DOI',
                'min_authors' => 1,
                'max_authors' => 10,
                'with_doi' => true,
                'gross_amount' => 150000.0,
                'developer_gross_share' => 20000.0,
                'notes' => 'Naskah internasional 1-10 penulis (+ DOI)',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'key' => 'international_11_15',
                'category' => 'international',
                'tier_name' => 'International 11-15 Author + DOI',
                'min_authors' => 11,
                'max_authors' => null,
                'with_doi' => true,
                'gross_amount' => 200000.0,
                'developer_gross_share' => 30000.0,
                'notes' => 'Naskah internasional ≥11 penulis (+ DOI)',
                'is_active' => true,
                'sort_order' => 8,
            ],
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
                'sort_order' => 9,
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
                'sort_order' => 10,
            ],
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
                'sort_order' => 11,
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
                'sort_order' => 12,
            ],
        ];

        foreach ($tiers as $tier) {
            SubmissionPricing::updateOrCreate(
                ['key' => $tier['key']],
                $tier
            );
        }

        Cache::forget('submission_pricing_tiers');
        Cache::forget('submission_pricing_settings');
    }
}
