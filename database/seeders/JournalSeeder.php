<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $journals = [
            'Argopuro: Jurnal Ilmu Bahasa',
            'Jayabama: Journal Peminat Olahraga',
            'Panorama: Jurnal Kajian Pariwisata',
            'Medic Nutricia : Journal Ilmu Kesehatan',
            'Hibrida: Jurnal Pertanian, Peternakan, Perikanan',
            'Trigonometri : Journal Matematika dan Ilmu Pengetahuan Alam',
            'Musytari : Jurnal Manajemen, Akuntansi, dan Ekonomi',
            'Causa: Jurnal Hukum dan Kewarganegaraan',
            'Triwikrama: Jurnal Ilmu Sosial',
            'Krepa: Kreativitas Pada Pengabdian Masyarakat',
            'Sindoro: Cendikia Pendidikan',
            'Liberosis: Jurnal Psikologi dan Bimbingan Konseling',
            'Kohesi: Jurnal Sains dan Teknologi',
            'Tashdiq: Jurnal Kajian Agama dan Dakwah',
        ];

        foreach ($journals as $journal) {
            DB::table('journals')->insert([
                'name' => $journal,
                'slug' => Str::slug($journal),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}