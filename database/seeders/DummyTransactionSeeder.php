<?php

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $userId = $user?->id ?? 1;

        $journals = Journal::all();
        if ($journals->isEmpty()) {
            return;
        }

        $dummyData = [
            [
                'title' => 'Penerapan Machine Learning Klasifikasi Mahasiswa',
                'author_name' => 'Dr. Ir. Hendra Gunawan',
                'email' => 'hendra.gunawan@univ.ac.id',
                'author_count' => 1,
                'want_doi' => true,
                'has_doi' => true,
                'status' => 'Approved',
                'days_ago' => 1,
            ],
            [
                'title' => 'Model Problem Based Learning di Era Digital',
                'author_name' => 'Siti Rahmawati, M.Pd.',
                'email' => 'siti.rahma@edu.ac.id',
                'author_count' => 3,
                'want_doi' => true,
                'has_doi' => true,
                'status' => 'Approved',
                'days_ago' => 2,
            ],
            [
                'title' => 'Tanggung Jawab Hukum E-Commerce Konsumen',
                'author_name' => 'Muhammad Rizky, S.H.',
                'email' => 'm.rizky@lawyer.ac.id',
                'author_count' => 1,
                'want_doi' => false,
                'has_doi' => false,
                'status' => 'Approved',
                'days_ago' => 3,
            ],
            [
                'title' => 'Pengaruh Budaya Organisasi Kinerja BUMN',
                'author_name' => 'Bambang Soedarmono, M.M.',
                'email' => 'bambang.sdm@corpu.ac.id',
                'author_count' => 7,
                'want_doi' => true,
                'has_doi' => true,
                'status' => 'Approved',
                'days_ago' => 4,
            ],
            [
                'title' => 'Deteksi Retinopati Diabetik dengan CNN',
                'author_name' => 'dr. Dian Kusuma, Sp.M.',
                'email' => 'dian.eye@med.ac.id',
                'author_count' => 8,
                'want_doi' => false,
                'has_doi' => false,
                'status' => 'Approved',
                'days_ago' => 5,
            ],
            [
                'title' => 'Kajian Etnolinguistik Tradisi Sasak Lombok',
                'author_name' => 'Baiq Nurul Aini, M.Hum.',
                'email' => 'baiq.nurul@bahasa.ac.id',
                'author_count' => 2,
                'want_doi' => true,
                'has_doi' => true,
                'status' => 'Approved',
                'days_ago' => 7,
            ],
            [
                'title' => 'Strategi Pemasaran Digital & FinTech UMKM',
                'author_name' => 'Drs. Wahyu Hidayat, M.Si.',
                'email' => 'wahyu.h@ekonomi.ac.id',
                'author_count' => 12,
                'want_doi' => true,
                'has_doi' => true,
                'status' => 'Approved',
                'days_ago' => 9,
            ],
            [
                'title' => 'Komparasi Kebijakan Fiskal Inflasi ASEAN',
                'author_name' => 'Prof. Dr. Anton Wijaya',
                'email' => 'anton.wijaya@finance.ac.id',
                'author_count' => 18,
                'want_doi' => true,
                'has_doi' => true,
                'status' => 'Approved',
                'days_ago' => 11,
            ],
            [
                'title' => 'Analisis Risiko Kejadian Stunting Balita',
                'author_name' => 'dr. Faisal Anwar, Sp.A.',
                'email' => 'faisal.pediatrik@hospital.ac.id',
                'author_count' => 2,
                'want_doi' => true,
                'has_doi' => true,
                'status' => 'Approved',
                'days_ago' => 13,
            ],
            [
                'title' => 'Sistem Informasi Geografis Rawan Banjir',
                'author_name' => 'Eko Wahyudi, M.Cs.',
                'email' => 'eko.gis@geotech.ac.id',
                'author_count' => 3,
                'want_doi' => true,
                'has_doi' => true,
                'status' => 'Approved',
                'days_ago' => 15,
            ],
        ];

        foreach ($dummyData as $index => $data) {
            $journal = $journals[$index % $journals->count()];

            // Generate author list
            $authors = [];
            $authorCount = $data['author_count'] ?? 1;
            for ($a = 1; $a <= $authorCount; $a++) {
                if ($a === 1) {
                    $authors[] = [
                        'name' => $data['author_name'],
                        'affiliation' => 'Universitas Indonesia',
                        'email' => $data['email'],
                    ];
                } else {
                    $authors[] = [
                        'name' => 'Co-Author ' . $a,
                        'affiliation' => 'Pusat Riset Akademik',
                        'email' => 'coauthor' . $a . '@riset.ac.id',
                    ];
                }
            }

            $createdDate = now()->subDays($data['days_ago'])->setHour(rand(8, 17))->setMinute(rand(10, 55));

            Submission::create([
                'user_id' => $userId,
                'journal_id' => $journal->id,
                'title' => $data['title'],
                'author_name' => $data['author_name'],
                'authors' => $authors,
                'email' => $data['email'],
                'status' => $data['status'],
                'volume' => 'Vol. ' . rand(3, 7) . ' No. ' . rand(1, 4) . ' (2026)',
                'publication_link' => 'https://cib.or.id/journal/index.php/' . ($journal->identifier ?? 'cib') . '/article/view/' . (100 + $index),
                'date_of_loa' => $createdDate->format('Y-m-d'),
                'submission_date' => $createdDate->copy()->subDays(2)->format('Y-m-d'),
                'approved_date' => $createdDate->format('Y-m-d'),
                'proof_of_payment' => 'assets/qris.jpg',
                'want_doi' => $data['want_doi'],
                'has_doi' => $data['has_doi'],
                'repository_identifier' => '10.55927/' . ($journal->identifier ?? 'cib') . '.v' . rand(3, 7) . 'i' . rand(1, 4) . '.' . (100 + $index),
                'repository_identifier_status' => 'registered',
                'created_at' => $createdDate,
                'updated_at' => $createdDate,
            ]);
        }
    }
}
