<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $journals = [
            [
                'name' => 'Argopuro: Jurnal Ilmu Bahasa',
                'slug' => 'argopuro',
                'image' => 'https://cibangsa.com/public/journals/15/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/argopurojournal',
                'template_link' => 'https://docs.google.com/document/d/150bV7haWP9IWUIINMwaRb-2I5Zg4R_1C/edit',
                'identifier' => null,
            ],
            [
                'name' => 'Jayabama: Journal Peminat Olahraga',
                'slug' => 'jayabama',
                'image' => 'https://cibangsa.com/public/journals/19/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/jayabamajournal',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Panorama: Jurnal Kajian Pariwisata',
                'slug' => 'panorama',
                'image' => 'https://cibangsa.com/public/journals/20/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/panoramajournal',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Medic Nutricia: Journal Ilmu Kesehatan',
                'slug' => 'medicnutricia',
                'image' => 'https://cibangsa.com/public/journals/17/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/medicnutriciajournal',
                'template_link' => null,
                'identifier' => 'Medic Nutricia',
            ],
            [
                'name' => 'Hibrida: Jurnal Pertanian, Peternakan, Perikanan',
                'slug' => 'hibrida',
                'image' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/hybridajournal',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Trigonometri: Journal Matematika dan Ilmu Pengetahuan Alam',
                'slug' => 'trigonometri',
                'image' => 'https://cibangsa.com/public/journals/16/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/trigonometrijournal',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Musytari: Jurnal Manajemen, Akuntansi, dan Ekonomi',
                'slug' => 'musytari',
                'image' => 'https://cibangsa.com/public/journals/1/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/musytari',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Causa: Jurnal Hukum dan Kewarganegaraan',
                'slug' => 'causa',
                'image' => 'https://cibangsa.com/public/journals/2/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/causa',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Triwikrama: Jurnal Ilmu Sosial',
                'slug' => 'triwikrama',
                'image' => 'https://cibangsa.com/public/journals/3/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/triwikrama',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Krepa: Kreativitas Pada Pengabdian Masyarakat',
                'slug' => 'krepa',
                'image' => 'https://cibangsa.com/public/journals/5/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/krepa',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Sindoro: Cendikia Pendidikan',
                'slug' => 'sindoro',
                'image' => 'https://cibangsa.com/public/journals/6/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/sindoro',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Liberosis: Jurnal Psikologi dan Bimbingan Konseling',
                'slug' => 'liberosis',
                'image' => 'https://cibangsa.com/public/journals/7/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/liberosis',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Kohesi: Jurnal Sains dan Teknologi',
                'slug' => 'kohesi',
                'image' => 'https://cibangsa.com/public/journals/13/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/kohesi',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'Tashdiq: Jurnal Kajian Agama dan Dakwah',
                'slug' => 'tashdiq',
                'image' => 'https://cibangsa.com/public/journals/14/journalThumbnail_en.jpg',
                'link' => 'https://cibangsa.com/index.php/tashdiq',
                'template_link' => null,
                'identifier' => null,
            ],
            [
                'name' => 'International Journal of Economics and Financial Issues',
                'slug' => 'ijefi',
                'image' => 'https://aset.warunayama.org/storage/additional-assets/ijefi-cover.png',
                'link' => 'https://ijefijournal.com/index.php/ijefi',
                'ojs_base_url' => 'https://ijefijournal.com/',
                'ojs_secret_key' => null,
                'template_link' => 'https://docs.google.com/document/d/1gyXMJ59xboWkfRbZNLRLyY34OinyV3Pl/edit?usp=share_link&ouid=108946441122427471297&rtpof=true&sd=true',
                'identifier' => 'international journal of economics and financial issues',
            ],
            [
                'name' => 'Pakistan Journal of Life and Social Sciences',
                'slug' => 'pjls',
                'image' => 'https://aset.warunayama.org/storage/additional-assets/pjls-cover.png',
                'link' => 'https://pjlsedu.com/index.php/pjls',
                'ojs_base_url' => 'https://pjlsedu.com/',
                'ojs_secret_key' => null,
                'template_link' => 'https://docs.google.com/document/d/1rvALlkAp3f95alN5eqm6o_uaHqJKcUvq/edit?usp=share_link&ouid=108946441122427471297&rtpof=true&sd=true',
                'identifier' => 'pakistan journal of life and social sciences',
            ],
        ];

        foreach ($journals as $journal) {
            DB::table('journals')->updateOrInsert(
                ['slug' => $journal['slug']],
                [
                    'name' => $journal['name'],
                    'image' => $journal['image'] ?? null,
                    'link' => $journal['link'] ?? null,
                    'template_link' => $journal['template_link'] ?? null,
                    'identifier' => $journal['identifier'] ?? null,
                    'ojs_base_url' => $journal['ojs_base_url'] ?? null,
                    'ojs_secret_key' => $journal['ojs_secret_key'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}