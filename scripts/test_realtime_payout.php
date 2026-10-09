<?php

/**
 * Real-time Broadcast & FCM Push Notification Testing Script (PAYOUT)
 * Server: loa.jurnalcib.com / Local Development
 *
 * Penggunaan via Terminal / SSH:
 *   php scripts/test_realtime_payout.php              # Buat dummy payout 'waiting_confirmation', broadcast Reverb & kirim FCM Push Notif
 *   php scripts/test_realtime_payout.php --cleanup    # Hapus dummy payout, reset auto-increment, & sync widget kembali normal
 *   php scripts/test_realtime_payout.php --ping       # Cek broadcast Reverb saja tanpa database
 *   php scripts/test_realtime_payout.php --no-fcm     # Broadcast Reverb saja tanpa push notification
 *   php scripts/test_realtime_payout.php --amount=75000 --status=waiting_confirmation
 *   php scripts/test_realtime_payout.php --id=77777   # Menggunakan ID kustom
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\DevFinancialUpdated;
use App\Models\DevPayout;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\DB;

$options = getopt('', ['cleanup', 'ping', 'id::', 'amount::', 'status::', 'no-fcm', 'no-reverb']);
$id = isset($options['id']) ? (int) $options['id'] : 99999;
$amount = isset($options['amount']) ? (float) $options['amount'] : 50000;
$status = isset($options['status']) ? (string) $options['status'] : 'waiting_confirmation';
$isCleanup = isset($options['cleanup']);
$isPing = isset($options['ping']);
$sendFcm = !isset($options['no-fcm']);
$sendReverb = !isset($options['no-reverb']);

echo "\n======================================================\n";
echo "   🧪 TESTING REALTIME & FCM PAYOUT (F Loafinwatch)\n";
echo "======================================================\n";
echo "Target ID      : {$id}\n";
echo "Reverb Channel : dev-financial\n";
echo "FCM Push Notif : " . ($sendFcm ? "AKTIF" : "NONAKTIF") . "\n\n";

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
        echo "ℹ️  Record ID #{$id} tidak ditemukan di database (mungkin sudah dihapus).\n";
    }

    try {
        $maxId = (int) (DevPayout::max('id') ?? 0);
        $nextId = $maxId + 1;
        DB::statement("ALTER TABLE dev_payouts AUTO_INCREMENT = {$nextId}");
        echo "✅ AUTO_INCREMENT tabel dev_payouts di-reset ke {$nextId}.\n";
    } catch (\Throwable $e) {
        echo "⚠️  Catatan reset auto-increment: " . $e->getMessage() . "\n";
    }

    // Broadcast ke Reverb
    if ($sendReverb) {
        echo "Mengirim sinyal broadcast pembersihan ke Reverb WebSocket...\n";
        try {
            broadcast(new DevFinancialUpdated(
                userId: 0,
                action: 'payout_deleted',
                payoutId: $id,
                message: "TEST: Payout dummy #{$id} telah dihapus"
            ));
            echo "🚀 Sinyal Reverb terkirim! Item di aplikasi akan otomatis hilang.\n";
        } catch (\Throwable $e) {
            echo "⚠️  Broadcast Reverb gagal: " . $e->getMessage() . "\n";
        }
    }

    // Kirim sinyal silent sync ke FCM agar widget homescreen di HP langsung sinkron kembali
    if ($sendFcm) {
        echo "Mengirim sinyal background sync FCM ke HP developer...\n";
        try {
            $fcm = app(FirebaseNotificationService::class);
            $fcm->notifyDeveloper(
                '🔄 Widget Disinkronkan',
                'Data dummy telah dibersihkan. Widget kembali normal.',
                ['action' => 'sync_widgets']
            );
            echo "📱 Sinyal sinkronisasi widget FCM terkirim ke HP!\n";
        } catch (\Throwable $e) {
            echo "⚠️  FCM sync gagal: " . $e->getMessage() . "\n";
        }
    }

    echo "\n🎉 Selesai! Database dan tampilan HP kembali bersih.\n\n";
    exit(0);
}

// Mode: CREATE & BROADCAST
echo "▶ Mode: CREATE DUMMY PAYOUT, REVERB & FCM NOTIFICATION\n";

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
        'reference_no' => 'QRIS-TEST-' . date('YmdHis'),
        'bank_name' => 'QRIS (SIMULASI PEMBAYARAN)',
        'account_no' => '9999999999',
        'notes' => 'Testing realtime payout & push notifikasi FCM',
        'status' => $status,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ Dummy record berhasil disimpan ke tabel dev_payouts!\n";
} catch (\Throwable $e) {
    echo "❌ Gagal menyimpan ke DB: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Broadcast ke Reverb WebSocket
if ($sendReverb) {
    echo "Mengirim sinyal broadcast ke Reverb WebSocket (Channel: dev-financial)...\n";
    try {
        broadcast(new DevFinancialUpdated(
            userId: 0,
            action: 'payout_updated',
            payoutId: $id,
            message: "TEST REALTIME: Payout dummy #{$payoutNo} status: {$status}"
        ));
        echo "🚀 Sinyal Reverb WebSocket BERHASIL terkirim!\n";
    } catch (\Throwable $e) {
        echo "⚠️  Reverb broadcast gagal: " . $e->getMessage() . "\n";
    }
}

// 2. Kirim Push Notifikasi FCM & Sinyal Background Widget Sync
if ($sendFcm) {
    echo "Mengirim FCM Push Notification & Background Widget Sync ke HP developer...\n";
    try {
        $fcm = app(FirebaseNotificationService::class);
        $title = '💸 Payout Siap Dikonfirmasi!';
        $body = "Dana Rp " . number_format($amount, 0, ',', '.') . " telah ditransfer via QRIS ({$payoutNo}). Silakan konfirmasi di F Loafinwatch.";

        $sent = $fcm->notifyDeveloper($title, $body, [
            'action' => 'sync_widgets',
            'payout_id' => (string) $id,
            'payout_no' => $payoutNo,
        ]);

        if ($sent) {
            echo "📲 Push Notification FCM BERHASIL terkirim ke HP!\n";
            echo "   (Heads-up notif muncul & widget homescreen akan otomatis ter-update)\n";
        } else {
            echo "ℹ️  FCM: Belum ada developer dengan fcm_token terdaftar di database (silakan login di aplikasi HP dulu).\n";
        }
    } catch (\Throwable $e) {
        echo "⚠️  Gagal kirim FCM: " . $e->getMessage() . "\n";
    }
}

echo "\n📱 CEK HASIL DI HP (floafinwatch):\n";
echo "1. Homescreen Widget: Status & nominal payout akan otomatis terupdate.\n";
echo "2. Push Notification: Banner notifikasi akan muncul di layar HP.\n";
echo "3. Aplikasi: Buka menu Payout & Konfirmasi Payout, item #{$payoutNo} akan muncul.\n\n";
echo "🧹 CARA HAPUS / CLEANUP SETELAH SELESAI TESTING:\n";
echo "Jalankan perintah:\n";
echo "   php scripts/test_realtime_payout.php --cleanup --id={$id}\n\n";
