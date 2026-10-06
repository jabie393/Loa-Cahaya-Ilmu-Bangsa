<?php

/**
 * Real-time Broadcast Testing Script (PAYOUT) untuk Server Production
 * Server: loa.jurnalcib.com
 *
 * Penggunaan via Terminal / SSH:
 *   php scripts/test_realtime_payout.php              # Buat data payout ID 99999 & broadcast
 *   php scripts/test_realtime_payout.php --cleanup    # Hapus data ID 99999 & reset sequence
 *   php scripts/test_realtime_payout.php --ping       # Cek broadcast saja tanpa menyentuh database
 *   php scripts/test_realtime_payout.php --id=77777   # Menggunakan ID kustom
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\DevFinancialUpdated;
use App\Models\DevPayout;
use Illuminate\Support\Facades\DB;

$options = getopt('', ['cleanup', 'ping', 'id::', 'amount::', 'status::']);
$id = isset($options['id']) ? (int) $options['id'] : 99999;
$amount = isset($options['amount']) ? (float) $options['amount'] : 50000;
$status = isset($options['status']) ? (string) $options['status'] : 'waiting_payout';
$isCleanup = isset($options['cleanup']);
$isPing = isset($options['ping']);

echo "\n======================================================\n";
echo "   🧪 TESTING REALTIME PAYOUT (loa.jurnalcib.com)\n";
echo "======================================================\n";
echo "Target Channel : dev-financial\n";
echo "Event Name     : financial.updated\n";
echo "Target ID      : {$id}\n\n";

if ($isPing) {
    echo "▶ Mode: PING BROADCAST ONLY\n";
    try {
        broadcast(new DevFinancialUpdated(
            userId: 0,
            action: 'payout_updated',
            payoutId: $id,
            message: "Ping realtime test payout pada " . date('Y-m-d H:i:s')
        ));
        echo "✅ Sinyal ping payout berhasil dikirim ke Reverb WebSocket!\n";
    } catch (\Throwable $e) {
        echo "❌ Gagal kirim broadcast: " . $e->getMessage() . "\n";
    }
    echo "\n";
    exit(0);
}

if ($isCleanup) {
    echo "▶ Mode: CLEANUP DUMMY PAYOUT\n";
    $payout = DevPayout::find($id);
    if (!$payout) {
        $payout = DevPayout::where('payout_no', 'like', "%{$id}%")->first();
    }

    if ($payout) {
        $no = $payout->payout_no;
        $payout->delete();
        echo "✅ Record payout [ID #{$id} / {$no}] berhasil dihapus dari database.\n";
    } else {
        echo "ℹ️  Record ID #{$id} tidak ditemukan (mungkin sudah dihapus).\n";
    }

    try {
        $maxId = (int) (DevPayout::max('id') ?? 0);
        $nextId = $maxId + 1;
        DB::statement("ALTER TABLE dev_payouts AUTO_INCREMENT = {$nextId}");
        echo "✅ AUTO_INCREMENT tabel dev_payouts di-reset ke {$nextId}.\n";
    } catch (\Throwable $e) {
        echo "⚠️  Catatan reset auto-increment: " . $e->getMessage() . "\n";
    }

    echo "Mengirim sinyal broadcast pembersihan ke Reverb (Channel: dev-financial)...\n";
    try {
        broadcast(new DevFinancialUpdated(
            userId: 0,
            action: 'payout_deleted',
            payoutId: $id,
            message: "TEST: Payout dummy #{$id} telah dihapus"
        ));
        echo "🚀 Sinyal pembersihan terkirim! Item di aplikasi akan otomatis hilang.\n";
    } catch (\Throwable $e) {
        echo "⚠️  Broadcast gagal: " . $e->getMessage() . "\n";
    }

    echo "\n🎉 Selesai! Database kembali bersih.\n\n";
    exit(0);
}

// Mode: CREATE & BROADCAST
echo "▶ Mode: CREATE DUMMY PAYOUT & BROADCAST\n";

$existing = DevPayout::find($id);
if ($existing) {
    echo "⚠️  Data dummy ID #{$id} sudah ada. Menghapus data lama terlebih dahulu...\n";
    $existing->delete();
}

$payoutNo = 'PO-TEST-' . $id;

echo "Menyimpan ke database (Tabel: dev_payouts):\n";
echo "- ID        : {$id}\n";
echo "- Payout No : {$payoutNo}\n";
echo "- Nominal   : Rp " . number_format($amount, 0, ',', '.') . "\n";
echo "- Status    : {$status}\n";

try {
    DevPayout::forceCreate([
        'id' => $id,
        'payout_no' => $payoutNo,
        'user_id' => null,
        'amount' => $amount,
        'bank_name' => 'BCA (TEST REALTIME)',
        'account_no' => '9999999999',
        'notes' => 'Testing realtime broadcast Reverb production',
        'status' => $status,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ Dummy record berhasil disimpan ke tabel dev_payouts!\n";
} catch (\Throwable $e) {
    echo "❌ Gagal menyimpan ke DB: " . $e->getMessage() . "\n";
    exit(1);
}

// Gunakan userId: 0 agar persis seperti transaksi (broadcast ke channel publik dev-financial)
echo "Mengirim sinyal broadcast ke Reverb WebSocket (Channel: dev-financial)...\n";
try {
    broadcast(new DevFinancialUpdated(
        userId: 0,
        action: 'payout_updated',
        payoutId: $id,
        message: "TEST REALTIME: Payout dummy #{$payoutNo} status: {$status}"
    ));
    echo "🚀 Sinyal broadcast BERHASIL terkirim!\n\n";
    echo "📱 CEK APLIKASI (floafinwatch):\n";
    echo "1. Buka aplikasi di halaman Payout (pastikan filter chip di 'Semua' atau 'Pending') atau Dashboard.\n";
    echo "2. Item Payout #{$payoutNo} (Rp " . number_format($amount, 0, ',', '.') . ") akan langsung muncul otomatis di layar.\n\n";
    echo "🧹 CARA HAPUS SETELAH SELESAI TESTING:\n";
    echo "Jalankan perintah:\n";
    echo "   php scripts/test_realtime_payout.php --cleanup --id={$id}\n\n";
} catch (\Throwable $e) {
    echo "❌ Gagal broadcast: " . $e->getMessage() . "\n";
    echo "Periksa apakah service Reverb (port 8080/8081) sedang berjalan di server.\n\n";
    exit(1);
}
