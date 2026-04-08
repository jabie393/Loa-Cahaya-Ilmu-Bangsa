<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $journals = [
            ['name' => 'Argopuro: Jurnal Ilmu Bahasa', 'slug' => 'argopuro'],
            ['name' => 'Jayabama: Journal Peminat Olahraga', 'slug' => 'jayabama'],
            ['name' => 'Panorama: Jurnal Kajian Pariwisata', 'slug' => 'panorama'],
            ['name' => 'Medic Nutricia: Journal Ilmu Kesehatan', 'slug' => 'medicnutricia'],
            ['name' => 'Hibrida: Jurnal Pertanian, Peternakan, Perikanan', 'slug' => 'hibrida'],
            ['name' => 'Trigonometri: Journal Matematika dan Ilmu Pengetahuan Alam', 'slug' => 'trigonometri'],
            ['name' => 'Musytari: Jurnal Manajemen, Akuntansi, dan Ekonomi', 'slug' => 'musytari'],
            ['name' => 'Causa: Jurnal Hukum dan Kewarganegaraan', 'slug' => 'causa'],
            ['name' => 'Triwikrama: Jurnal Ilmu Sosial', 'slug' => 'triwikrama'],
            ['name' => 'Krepa: Kreativitas Pada Pengabdian Masyarakat', 'slug' => 'krepa'],
            ['name' => 'Sindoro: Cendikia Pendidikan', 'slug' => 'sindoro'],
            ['name' => 'Liberosis: Jurnal Psikologi dan Bimbingan Konseling', 'slug' => 'liberosis'],
            ['name' => 'Kohesi: Jurnal Sains dan Teknologi', 'slug' => 'kohesi'],
            ['name' => 'Tashdiq: Jurnal Kajian Agama dan Dakwah', 'slug' => 'tashdiq'],
        ];

        foreach ($journals as $journal) {
            DB::table('journals')->updateOrInsert(
                ['slug' => $journal['slug']], // unik berdasarkan slug
                [
                    'name' => $journal['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}