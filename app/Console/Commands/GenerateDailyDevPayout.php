<?php

namespace App\Console\Commands;

use App\Models\DevPayout;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateDailyDevPayout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev-payout:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generates daily developer payout draft if there is remaining available balance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking remaining developer share balance...');

        $earned = (float) Payment::where('payment_status', 'paid')->sum('developer_net_share');
        $locked = (float) DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
        $available = max(0, $earned - $locked);

        if ($available <= 0) {
            $this->info('No available developer share to payout (Available: Rp ' . number_format($available, 0, ',', '.') . '). Skipping.');
            return self::SUCCESS;
        }

        $payoutCount = DevPayout::count() + 1;
        $payoutNo = 'PO-DEV-' . now()->format('Ym') . '-' . sprintf('%03d', $payoutCount);

        while (DevPayout::where('payout_no', $payoutNo)->exists()) {
            $payoutCount++;
            $payoutNo = 'PO-DEV-' . now()->format('Ym') . '-' . sprintf('%03d', $payoutCount);
        }

        $payout = DevPayout::create([
            'payout_no' => $payoutNo,
            'user_id' => null,
            'amount' => $available,
            'reference_no' => null,
            'proof_file' => null,
            'notes' => 'Payout Otomatis Harian (Pukul 17:30)',
            'status' => 'waiting_payout',
        ]);

        $msg = "Daily developer payout draft created: {$payout->payout_no} for Rp " . number_format($available, 0, ',', '.');
        $this->info($msg);
        Log::info($msg, ['payout_id' => $payout->id, 'amount' => $available]);

        return self::SUCCESS;
    }
}
