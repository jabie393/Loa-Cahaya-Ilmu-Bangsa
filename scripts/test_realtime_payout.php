<?php

/**
 * Real-time Broadcast & FCM Push Notification Testing Script (PAYOUT)
 * Server: loa.jurnalcib.com / Local Development
 *
 * Sinkron dengan arsitektur terbaru:
 * - App\Models\DevPayout (booted lifecycle events)
 * - App\Livewire\DevPayoutsTable (payout & repay via QRIS)
 * - App\Services\FirebaseNotificationService (v1 data payload with snapshot)
 * - Role Authorization: ryu_dev
 *
 * Penggunaan via Terminal / SSH:
 *   php scripts/test_realtime_payout.php                 # Dummy payout 'waiting_confirmation', broadcast Reverb & kirim FCM Push Notif
 *   php scripts/test_realtime_payout.php --repay         # Uji notifikasi "Bayar Ulang" (retry payout via QRIS)
 *   php scripts/test_realtime_payout.php --status=waiting_payout  # Antrean payout terbit (menunggu pembayaran admin)
 *   php scripts/test_realtime_payout.php --cleanup       # Hapus dummy payout, reset auto-increment, & silent sync widget
 *   php scripts/test_realtime_payout.php --ping          # Cek broadcast Reverb saja tanpa menyentuh database
 *   php scripts/test_realtime_payout.php --no-fcm        # Broadcast Reverb saja tanpa push notification
 *   php scripts/test_realtime_payout.php --amount=75000  # Nominal kustom
 *   php scripts/test_realtime_payout.php --id=77777      # ID dummy kustom
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\DevFinancialUpdated;
use App\Models\DevPayout;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\DB;

$options = getopt('', ['cleanup', 'ping', 'id::', 'amount::', 'status::', 'repay', 'no-fcm', 'no-reverb']);
$id = isset($options['id']) ? (int) $options['id'] : 99999;
$amount = isset($options['amount']) ? (float) $options['amount'] : 50000;
$isRepay = isset($options['repay']);
$status = isset($options['status']) ? (string) $options['status'] : ($isRepay ? 'waiting_confirmation' : 'waiting_confirmation');
$isCleanup = isset($options['cleanup']);
$isPing = isset($options['ping']);
$sendFcm = !isset($options['no-fcm']);
$sendReverb = !isset($options['no-reverb']);

// Dapatkan user developer ryu_dev (hanya jika bukan mode ping)
$devUser = null;
$userId = 1;

if (!$isPing) {
    try {
        $devUser = User::role('ryu_dev')->first()
            ?? User::whereHas('roles', fn($q) => $q->where('name', 'ryu_dev'))->first()
            ?? User::first();
        $userId = $devUser?->id ?? 1;
    } catch (\Throwable $e) {
        $devUser = null;
        $userId = 1;
    }
}

echo "\n======================================================\n";
echo "   🧪 TESTING REALTIME & FCM PAYOUT (F Loafinwatch)\n";
echo "======================================================\n";
echo "Target ID       : {$id}\n";
echo "Target Developer: " . ($devUser ? "{$devUser->name} (ID #{$devUser->id}, {$devUser->email})" : "Default User (ID #1)") . "\n";
echo "FCM Token Status: " . (!empty($devUser?->fcm_token) ? "✅ Terdaftar (Aktif)" : "⚠️ KOSONG / LOGOUT (Silakan login di aplikasi HP)") . "\n";
echo "Reverb Channel  : dev-financial\n";
echo "FCM Push Notif  : " . ($sendFcm ? "AKTIF" : "NONAKTIF") . "\n";
if ($isRepay) {
    echo "Mode Skenario   : 🔄 BAYAR ULANG (REPAY) via QRIS\n";
}
echo "======================================================\n\n";

if ($isPing) {
    echo "▶ Mode: PING BROADCAST ONLY\n";
    try {
        broadcast(new DevFinancialUpdated(
            userId: $userId,
            action: 'payout_updated',
            payoutId: $id,
            message: "Ping realtime test payout pada " . date('Y-m-d H:i:s')
        ));
        echo "✅ Sinyal ping payout berhasil dikirim ke Reverb WebSocket (Channel: dev-financial)!\n";
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

    // Broadcast sinyal penghapusan ke Reverb
    if ($sendReverb) {
        echo "Mengirim sinyal broadcast pembersihan ke Reverb WebSocket...\n";
        try {
            broadcast(new DevFinancialUpdated(
                userId: $userId,
                action: 'payout_deleted',
                payoutId: $id,
                message: "TEST: Payout dummy #{$id} telah dihapus"
            ));
            echo "🚀 Sinyal Reverb terkirim! Item di aplikasi akan otomatis hilang.\n";
        } catch (\Throwable $e) {
            echo "⚠️  Broadcast Reverb gagal: " . $e->getMessage() . "\n";
        }
    }

    // Kirim sinyal silent sync ke FCM agar widget homescreen di HP langsung sinkron kembali tanpa pop-up mengganggu
    if ($sendFcm) {
        echo "Mengirim sinyal silent background sync FCM ke HP developer...\n";
        try {
            $fcm = app(FirebaseNotificationService::class);
            $fcm->notifyDeveloper(
                '',
                '',
                [
                    'type' => 'payout',
                    'action' => 'sync_widgets',
                    'payout_id' => (string) $id,
                ]
            );
            echo "📱 Sinyal silent sync FCM terkirim (Widget HP langsung sinkron tanpa pop-up banner).\n";
        } catch (\Throwable $e) {
            echo "⚠️  FCM silent sync gagal: " . $e->getMessage() . "\n";
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
$refNo = 'QRIS-TEST-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));

echo "Menyimpan ke database (Tabel: dev_payouts):\n";
echo "- ID          : {$id}\n";
echo "- User ID     : {$userId} (" . ($devUser?->name ?? 'System') . ")\n";
echo "- Payout No   : {$payoutNo}\n";
echo "- Reference No: {$refNo}\n";
echo "- Nominal     : Rp " . number_format($amount, 0, ',', '.') . "\n";
echo "- Status      : {$status}\n";

try {
    // DevPayout::forceCreate akan otomatis memicu static::saved di DevPayout.php
    // yang membroadcast DevFinancialUpdated ke Reverb
    $payout = DevPayout::forceCreate([
        'id' => $id,
        'payout_no' => $payoutNo,
        'user_id' => $userId,
        'amount' => $amount,
        'reference_no' => $refNo,
        'bank_name' => 'QRIS (SIMULASI PEMBAYARAN)',
        'account_no' => '9999999999',
        'notes' => 'Testing realtime payout & push notifikasi FCM',
        'status' => $status,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ Dummy record berhasil disimpan ke tabel dev_payouts!\n";
    echo "   (Event static::saved DevPayout telah memicu broadcast Reverb)\n";
} catch (\Throwable $e) {
    echo "❌ Gagal menyimpan ke DB: " . $e->getMessage() . "\n";
    exit(1);
}

// Kirim Push Notifikasi FCM & Sinyal Background Widget Sync
if ($sendFcm) {
    echo "Mengirim FCM Push Notification & Background Widget Sync ke HP developer...\n";
    try {
        $fcm = app(FirebaseNotificationService::class);
        $amountFormatted = number_format($amount, 0, ',', '.');

        if ($isRepay) {
            $title = '💸 Payout Telah Dibayar Ulang!';
            $body = "Dana Rp {$amountFormatted} telah ditransfer ulang via QRIS ({$payoutNo}). Silakan periksa rekening dan konfirmasi di F Loafinwatch.";
        } elseif ($status === 'waiting_confirmation') {
            $title = '💸 Payout Siap Dikonfirmasi!';
            $body = "Dana Rp {$amountFormatted} telah ditransfer via QRIS ({$payoutNo}). Silakan konfirmasi di F Loafinwatch.";
        } elseif ($status === 'waiting_payout') {
            // Untuk waiting_payout, static::saved di DevPayout sudah mengirim notifikasi antrean terbit.
            // Di sini kita infokan statusnya
            $title = '';
            $body = '';
            echo "ℹ️  Notifikasi 'Antrean Payout Terbit' telah dikirim otomatis oleh model event DevPayout.\n";
        } else {
            $title = '';
            $body = '';
        }

        if ($title !== '') {
            $sent = $fcm->notifyDeveloper($title, $body, [
                'type' => 'payout',
                'route' => '/dev/payouts',
                'action' => 'sync_widgets',
                'payout_id' => (string) $id,
                'payout_no' => $payoutNo,
                'status' => $status,
            ]);

            if ($sent) {
                echo "📲 Push Notification FCM BERHASIL terkirim ke HP!\n";
                echo "   Judul: {$title}\n";
                echo "   Pesan: {$body}\n";
                echo "   (Heads-up notif muncul & widget homescreen akan otomatis ter-update)\n";
            } else {
                echo "ℹ️  FCM: Belum ada developer dengan fcm_token terdaftar di database.\n";
                echo "   👉 Silakan login di aplikasi F Loafinwatch di HP terlebih dahulu.\n";
            }
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
