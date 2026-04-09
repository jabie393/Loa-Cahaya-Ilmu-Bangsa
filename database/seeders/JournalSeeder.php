<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $journals = [
            ['name' => 'Argopuro: Jurnal Ilmu Bahasa', 'slug' => 'argopuro', 'image' => 'https://cibangsa.com/public/journals/15/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/argopurojournal'],
            ['name' => 'Jayabama: Journal Peminat Olahraga', 'slug' => 'jayabama', 'image' => 'https://cibangsa.com/public/journals/19/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/jayabamajournal'],
            ['name' => 'Panorama: Jurnal Kajian Pariwisata', 'slug' => 'panorama', 'image' => 'https://cibangsa.com/public/journals/20/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/panoramajournal'],
            ['name' => 'Medic Nutricia: Journal Ilmu Kesehatan', 'slug' => 'medicnutricia', 'image' => 'https://cibangsa.com/public/journals/17/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/medicnutriciajournal'],
            ['name' => 'Hibrida: Jurnal Pertanian, Peternakan, Perikanan', 'slug' => 'hibrida', 'image' => 'https://cibangsa.com/public/journals/18/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/hybridajournal'],
            ['name' => 'Trigonometri: Journal Matematika dan Ilmu Pengetahuan Alam', 'slug' => 'trigonometri', 'image' => 'https://cibangsa.com/public/journals/16/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/trigonometrijournal'],
            ['name' => 'Musytari: Jurnal Manajemen, Akuntansi, dan Ekonomi', 'slug' => 'musytari', 'image' => 'https://cibangsa.com/public/journals/1/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/musytari'],
            ['name' => 'Causa: Jurnal Hukum dan Kewarganegaraan', 'slug' => 'causa', 'image' => 'https://cibangsa.com/public/journals/2/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/causa'],
            ['name' => 'Triwikrama: Jurnal Ilmu Sosial', 'slug' => 'triwikrama', 'image' => 'https://cibangsa.com/public/journals/3/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/triwikrama'],
            ['name' => 'Krepa: Kreativitas Pada Pengabdian Masyarakat', 'slug' => 'krepa', 'image' => 'https://cibangsa.com/public/journals/5/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/krepa'],
            ['name' => 'Sindoro: Cendikia Pendidikan', 'slug' => 'sindoro', 'image' => 'https://cibangsa.com/public/journals/6/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/sindoro'],
            ['name' => 'Liberosis: Jurnal Psikologi dan Bimbingan Konseling', 'slug' => 'liberosis', 'image' => 'https://cibangsa.com/public/journals/7/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/liberosis'],
            ['name' => 'Kohesi: Jurnal Sains dan Teknologi', 'slug' => 'kohesi', 'image' => 'https://cibangsa.com/public/journals/13/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/kohesi'],
            ['name' => 'Tashdiq: Jurnal Kajian Agama dan Dakwah', 'slug' => 'tashdiq', 'image' => 'https://cibangsa.com/public/journals/14/journalThumbnail_en.jpg', 'link' => 'https://cibangsa.com/index.php/tashdiq'],
        ];

        foreach ($journals as $journal) {
            DB::table('journals')->updateOrInsert(
                ['slug' => $journal['slug']], // unik berdasarkan slug
                [
                    'name' => $journal['name'],
                    'image' => $journal['image'] ?? null,
                    'link' => $journal['link'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}