<?php

namespace App\Console\Commands;

use App\Events\DevFinancialUpdated;
use App\Models\DevPayout;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestRealtimeBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:test-realtime 
                            {--type=transaction : Tipe data yang diuji (transaction / payout)}
                            {--id=99999 : ID khusus untuk data dummy testing} 
                            {--amount=50000 : Nominal developer share (Rp)} 
                            {--cleanup : Hapus data dummy dan reset auto-increment kembali normal} 
                            {--broadcast-only : Hanya kirim event WebSocket tanpa insert database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing real-time WebSocket Reverb broadcasting di server production secara aman';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = strtolower((string) $this->option('type'));
        $id = (int) $this->option('id');
        $amount = (float) $this->option('amount');
        $isCleanup = (bool) $this->option('cleanup');
        $broadcastOnly = (bool) $this->option('broadcast-only');

        $driver = config('broadcasting.default');
        $reverbHost = config('broadcasting.connections.reverb.options.host');
        $reverbPort = config('broadcasting.connections.reverb.options.port');

        $this->newLine();
        $this->info("==================================================");
        $this->info("   🧪 TESTING REALTIME BROADCASTING (" . strtoupper($type) . ")");
        $this->info("==================================================");
        $this->line("Broadcast Driver : {$driver}");
        $this->line("Reverb Endpoint  : http://{$reverbHost}:{$reverbPort}");
        $this->line("Target Channel   : dev-financial");
        $this->line("Event Name       : financial.updated");
        $this->line("Testing Target   : " . ($type === 'payout' ? 'Payout (dev_payouts)' : 'Transaksi (payments)'));
        $this->newLine();

        if ($broadcastOnly) {
            return $this->handleBroadcastOnly($id, $type);
        }

        if ($type === 'payout') {
            return $isCleanup ? $this->handleCleanupPayout($id) : $this->handleCreatePayout($id, $amount);
        }

        // Default: transaction
        return $isCleanup ? $this->handleCleanupTransaction($id) : $this->handleCreateTransaction($id, $amount);
    }

    /**
     * Buat dummy TRANSAKSI di DB dan kirim event realtime.
     */
    protected function handleCreateTransaction(int $id, float $amount): int
    {
        $this->info("▶ MODE: CREATE DUMMY TRANSAKSI & BROADCAST");

        // 1. Cek apakah record dengan ID ini sudah pernah ada
        $existing = Payment::find($id);
        if ($existing) {
            $this->warn("⚠️  Record Payment ID #{$id} sudah ada. Menghapus data lama...");
            PaymentItem::where('payment_id', $id)->delete();
            $existing->delete();
        }

        $devUser = User::whereHas('roles', fn($q) => $q->where('name', 'ryu_dev'))->first() ?? User::first();
        $userId = $devUser?->id ?? 1;

        $orderId = "ORDER-TEST-{$id}";
        $invoiceNumber = "INV-TEST-{$id}";
        $grossAmount = $amount + 100000; // Total bayar publikasi
        $mdr = 1000.00;
        $journalShare = $grossAmount - $amount - $mdr;

        $this->line("Menyimpan ke database (payments & payment_items)...");
        $this->line("- ID            : {$id}");
        $this->line("- Order ID      : {$orderId}");
        $this->line("- Invoice       : {$invoiceNumber}");
        $this->line("- Gross Amount  : Rp " . number_format($grossAmount, 0, ',', '.'));
        $this->line("- Dev Net Share : Rp " . number_format($amount, 0, ',', '.'));
        $this->line("- Status        : paid (settlement)");

        try {
            $payment = Payment::forceCreate([
                'id' => $id,
                'user_id' => $userId,
                'submission_id' => null,
                'submission_ids' => null,
                'invoice_number' => $invoiceNumber,
                'order_id' => $orderId,
                'transaction_id' => "TRX-TEST-{$id}",
                'gateway' => 'midtrans',
                'payment_method' => 'qris',
                'type' => 'submission',
                'payer_name' => 'Tester Realtime Production',
                'payer_email' => 'tester@jurnalcib.com',
                'gross_amount' => $grossAmount,
                'journal_share' => $journalShare,
                'developer_gross_share' => $amount + $mdr,
                'mdr_amount' => $mdr,
                'developer_net_share' => $amount,
                'transaction_status' => 'settlement',
                'payment_status' => 'paid',
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            PaymentItem::forceCreate([
                'id' => $id,
                'payment_id' => $payment->id,
                'submission_id' => null,
                'item_type' => 'publication',
                'item_name' => 'Publikasi Jurnal CIB (TEST REALTIME)',
                'gross_amount' => $grossAmount,
                'journal_share' => $journalShare,
                'developer_gross_share' => $amount + $mdr,
                'mdr_amount' => $mdr,
                'developer_net_share' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("✅ Record dummy transaksi ID #{$id} berhasil disimpan di database!");
        } catch (\Throwable $e) {
            $this->error("❌ Gagal menyimpan record transaksi: " . $e->getMessage());
            return self::FAILURE;
        }

        // Broadcast event realtime
        $this->line("Mengirim sinyal broadcast ke Reverb WebSocket...");
        try {
            broadcast(new DevFinancialUpdated(
                userId: 0,
                action: 'payment_received',
                payoutId: null,
                message: "Transaksi baru masuk: Order #{$orderId}"
            ));

            $this->info("🚀 Sinyal broadcast BERHASIL dikirim ke Reverb!");
            $this->newLine();
            $this->comment("📱 CEK APLIKASI (floafinwatch):");
            $this->line("1. Buka halaman Riwayat Transaksi atau Dashboard.");
            $this->line("2. Transaksi #{$orderId} (Dev Share: Rp " . number_format($amount, 0, ',', '.') . ") akan langsung muncul seketika.");
            $this->line("3. Saldo Siap Cair / Pendapatan di Dashboard akan otomatis bertambah Rp " . number_format($amount, 0, ',', '.') . " secara realtime!");
            $this->newLine();
            $this->comment("🧹 CARA BERSIHKAN / HAPUS SETELAH TESTING:");
            $this->line("Jalankan perintah ini di server:");
            $this->info("php artisan dev:test-realtime --type=transaction --cleanup --id={$id}");
            $this->newLine();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Gagal mengirim broadcast: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Hapus dummy transaksi dan reset auto-increment.
     */
    protected function handleCleanupTransaction(int $id): int
    {
        $this->info("▶ MODE: CLEANUP DUMMY TRANSAKSI & RESET AUTO-INCREMENT");

        $itemCount = PaymentItem::where('payment_id', $id)->delete();
        if ($itemCount > 0) {
            $this->info("✅ {$itemCount} item pada tabel payment_items berhasil dihapus.");
        }

        $payment = Payment::find($id);
        if (!$payment) {
            $payment = Payment::where('order_id', "ORDER-TEST-{$id}")->first();
        }

        if ($payment) {
            $orderId = $payment->order_id;
            $payment->delete();
            $this->info("✅ Record transaksi [ID #{$id} / {$orderId}] berhasil dihapus dari database.");
        } else {
            $this->warn("ℹ️  Record transaksi ID #{$id} tidak ditemukan.");
        }

        // Reset auto increment
        try {
            $maxPayId = (int) (Payment::max('id') ?? 0);
            $nextPayId = $maxPayId + 1;
            DB::statement("ALTER TABLE payments AUTO_INCREMENT = {$nextPayId}");
            $this->info("✅ Auto-increment tabel payments di-reset ke ID #{$nextPayId}.");

            $maxItemId = (int) (PaymentItem::max('id') ?? 0);
            $nextItemId = $maxItemId + 1;
            DB::statement("ALTER TABLE payment_items AUTO_INCREMENT = {$nextItemId}");
            $this->info("✅ Auto-increment tabel payment_items di-reset ke ID #{$nextItemId}.");
        } catch (\Throwable $e) {
            $this->warn("⚠️  Catatan reset auto-increment: " . $e->getMessage());
        }

        // Broadcast cleanup event
        $this->line("Mengirim sinyal broadcast pembersihan ke Reverb...");
        try {
            broadcast(new DevFinancialUpdated(
                userId: 0,
                action: 'payment_deleted',
                payoutId: null,
                message: "TEST: Transaksi dummy #{$id} telah dihapus"
            ));

            $this->info("🚀 Sinyal broadcast pembersihan BERHASIL dikirim!");
            $this->line("Aplikasi akan otomatis mendeteksi perubahan dan menghapus dummy dari layar.");
        } catch (\Throwable $e) {
            $this->warn("⚠️  Broadcast pembersihan gagal: " . $e->getMessage());
        }

        $this->newLine();
        $this->info("🎉 Selesai! Server production Anda bersih kembali.");
        return self::SUCCESS;
    }

    /**
     * Buat dummy PAYOUT di DB dan kirim event realtime.
     */
    protected function handleCreatePayout(int $id, float $amount): int
    {
        $this->info("▶ MODE: CREATE DUMMY PAYOUT & BROADCAST");

        $existing = DevPayout::find($id);
        if ($existing) {
            $this->warn("⚠️  Record DevPayout dengan ID #{$id} sudah ada. Menghapus data lama...");
            $existing->delete();
        }

        $devUser = User::whereHas('roles', fn($q) => $q->where('name', 'ryu_dev'))->first() ?? User::first();
        $userId = $devUser?->id ?? 1;
        $payoutNo = 'PO-TEST-' . $id;

        $this->line("Menyimpan ke database (dev_payouts)...");
        $this->line("- ID        : {$id}");
        $this->line("- No Payout : {$payoutNo}");
        $this->line("- User      : {$userId} (" . ($devUser?->name ?? 'System') . ")");
        $this->line("- Amount    : Rp " . number_format($amount, 0, ',', '.'));
        $this->line("- Status    : waiting_payout");

        try {
            DevPayout::forceCreate([
                'id' => $id,
                'payout_no' => $payoutNo,
                'user_id' => $userId,
                'amount' => $amount,
                'bank_name' => 'BCA (TEST REALTIME)',
                'account_no' => '9999999999',
                'notes' => 'Testing realtime broadcast Reverb production',
                'status' => 'waiting_payout',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("✅ Record dummy DevPayout ID #{$id} berhasil disimpan di database!");
        } catch (\Throwable $e) {
            $this->error("❌ Gagal menyimpan record: " . $e->getMessage());
            return self::FAILURE;
        }

        $this->line("Mengirim sinyal broadcast ke Reverb WebSocket...");
        try {
            broadcast(new DevFinancialUpdated(
                userId: $userId,
                action: 'payout_created',
                payoutId: $id,
                message: "Testing Realtime: Payout #{$payoutNo} dibuat"
            ));

            $this->info("🚀 Sinyal broadcast BERHASIL dikirim ke Reverb!");
            $this->newLine();
            $this->comment("📱 CEK APLIKASI (floafinwatch):");
            $this->line("1. Buka aplikasi di menu Dashboard / Payouts.");
            $this->line("2. Data Payout #{$payoutNo} akan langsung muncul seketika.");
            $this->newLine();
            $this->comment("🧹 CARA BERSIHKAN / HAPUS SETELAH TESTING:");
            $this->info("php artisan dev:test-realtime --type=payout --cleanup --id={$id}");
            $this->newLine();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Gagal mengirim broadcast: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Hapus data dummy payout dan broadcast.
     */
    protected function handleCleanupPayout(int $id): int
    {
        $this->info("▶ MODE: CLEANUP DUMMY PAYOUT & RESET AUTO-INCREMENT");

        $payout = DevPayout::find($id);
        if (!$payout) {
            $payout = DevPayout::where('payout_no', 'like', "%{$id}%")->first();
        }

        if ($payout) {
            $payoutNo = $payout->payout_no;
            $payout->delete();
            $this->info("✅ Record dummy payout [ID #{$id} / {$payoutNo}] berhasil dihapus dari database.");
        } else {
            $this->warn("ℹ️  Record dengan ID #{$id} tidak ditemukan.");
        }

        try {
            $maxId = (int) (DevPayout::max('id') ?? 0);
            $nextId = $maxId + 1;
            DB::statement("ALTER TABLE dev_payouts AUTO_INCREMENT = {$nextId}");
            $this->info("✅ Auto-increment tabel dev_payouts di-reset ke ID #{$nextId}.");
        } catch (\Throwable $e) {
            $this->warn("⚠️  Catatan: Gagal reset auto-increment: " . $e->getMessage());
        }

        $this->line("Mengirim sinyal broadcast pembersihan ke Reverb...");
        try {
            broadcast(new DevFinancialUpdated(
                userId: 0,
                action: 'payout_deleted',
                payoutId: $id,
                message: "TEST: Payout dummy #{$id} telah dihapus"
            ));
            $this->info("🚀 Sinyal broadcast pembersihan BERHASIL dikirim!");
        } catch (\Throwable $e) {
            $this->warn("⚠️  Broadcast pembersihan gagal: " . $e->getMessage());
        }

        $this->newLine();
        $this->info("🎉 Selesai! Server production Anda bersih kembali.");
        return self::SUCCESS;
    }

    /**
     * Broadcast saja tanpa query database.
     */
    protected function handleBroadcastOnly(int $id, string $type): int
    {
        $this->info("▶ MODE: BROADCAST ONLY (Tanpa sentuh database)");

        try {
            broadcast(new DevFinancialUpdated(
                userId: 0,
                action: $type === 'payout' ? 'test_ping' : 'payment_received',
                payoutId: $id,
                message: "Testing Realtime Ping: WebSocket Reverb OK (" . strtoupper($type) . ")"
            ));

            $this->info("🚀 Sinyal broadcast BERHASIL dikirim!");
            $this->line("Aplikasi yang sedang aktif akan otomatis memicu sinkronisasi data.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Gagal broadcast: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
