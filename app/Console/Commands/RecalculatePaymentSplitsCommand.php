<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculatePaymentSplitsCommand extends Command
{
    protected $signature = 'finance:recalculate-splits';
    protected $description = 'Hitung ulang seluruh porsi revenue split pada tabel payments dan payment_items (MDR dibebankan ke jurnal)';

    public function handle(): int
    {
        $this->info('Memulai kalkulasi ulang pembagian hasil (MDR dibebankan ke jurnal)...');

        DB::transaction(function () {
            // 1. Update tabel payment_items
            $itemsUpdated = DB::table('payment_items')->update([
                'developer_net_share' => DB::raw('developer_gross_share'),
                'journal_share' => DB::raw('gross_amount - developer_gross_share - mdr_amount'),
                'updated_at' => now(),
            ]);

            // 2. Update tabel payments
            $paymentsUpdated = DB::table('payments')->update([
                'developer_net_share' => DB::raw('developer_gross_share'),
                'journal_share' => DB::raw('gross_amount - developer_gross_share - mdr_amount'),
                'updated_at' => now(),
            ]);

            $this->info("Berhasil memperbarui:");
            $this->line("- {$paymentsUpdated} data di tabel payments");
            $this->line("- {$itemsUpdated} data di tabel payment_items");
        });

        $this->info('Selesai! Seluruh transaksi telah disinkronkan dengan skema baru.');
        return self::SUCCESS;
    }
}
