<?php

/**
 * Real-time Broadcast & FCM Push Notification Testing Script (TRANSAKSI)
 * Server: loa.jurnalcib.com / Local Development
 *
 * Sinkron dengan arsitektur terbaru:
 * - App\Services\PaymentGateways\PaymentFulfillmentService (markAsPaid lifecycle)
 * - App\Events\DevFinancialUpdated (Reverb WebSockets)
 * - App\Services\FirebaseNotificationService (High-priority FCM data with snapshot)
 * - Role Authorization: ryu_dev
 *
 * Penggunaan via Terminal / SSH:
 *   php scripts/test_realtime_transaction.php                      # Buat data transaksi ID 99999 & broadcast
 *   php scripts/test_realtime_transaction.php --cleanup            # Hapus data transaksi dummy & silent sync widget
 *   php scripts/test_realtime_transaction.php --ping               # Cek broadcast saja tanpa menyentuh database
 *   php scripts/test_realtime_transaction.php --id=77777           # Menggunakan ID kustom
 *   php scripts/test_realtime_transaction.php --gross=200000 --dev-share=65000  # Nominal kustom
 *   php scripts/test_realtime_transaction.php --no-fcm             # Tanpa FCM
 *   php scripts/test_realtime_transaction.php --no-reverb          # Tanpa Reverb
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\DevFinancialUpdated;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\DB;

$options = getopt('', ['cleanup', 'ping', 'id::', 'gross::', 'dev-share::', 'no-fcm', 'no-reverb']);
$id = isset($options['id']) ? (int) $options['id'] : 99999;
$gross = isset($options['gross']) ? (float) $options['gross'] : 150000;
$devShare = isset($options['dev-share']) ? (float) $options['dev-share'] : 49000;
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
echo "   🧪 TESTING REALTIME TRANSAKSI (F Loafinwatch)\n";
echo "======================================================\n";
echo "Target TRX ID   : {$id}\n";
echo "Target Developer: " . ($devUser ? "{$devUser->name} (ID #{$devUser->id}, {$devUser->email})" : "Default User (ID #1)") . "\n";
echo "FCM Token Status: " . (!empty($devUser?->fcm_token) ? "✅ Terdaftar (Aktif)" : "⚠️ KOSONG / LOGOUT (Silakan login di aplikasi HP)") . "\n";
echo "Reverb Channel  : dev-financial\n";
echo "Event Name      : financial.updated\n";
echo "FCM Push Notif  : " . ($sendFcm ? "AKTIF" : "NONAKTIF") . "\n";
echo "======================================================\n\n";

if ($isPing) {
    echo "▶ Mode: PING BROADCAST ONLY\n";
    try {
        broadcast(new DevFinancialUpdated(
            userId: 0,
            action: 'payment_received',
            payoutId: null,
            message: "Transaksi baru masuk: Order #ORDER-TEST-PING"
        ));
        echo "✅ Sinyal ping broadcast transaksi berhasil dikirim ke Reverb WebSocket!\n";
    } catch (\Throwable $e) {
        echo "❌ Gagal kirim broadcast: " . $e->getMessage() . "\n";
    }
    echo "\n";
    exit(0);
}

if ($isCleanup) {
    echo "▶ Mode: CLEANUP DUMMY TRANSAKSI\n";

    // 1. Hapus Payment Item terlebih dahulu jika ada
    $itemCount = PaymentItem::where('payment_id', $id)->delete();
    if ($itemCount > 0) {
        echo "✅ {$itemCount} item pada payment_items ID #{$id} berhasil dihapus.\n";
    }

    // 2. Hapus Payment
    $payment = Payment::find($id);
    if (!$payment) {
        $payment = Payment::where('order_id', "ORDER-TEST-{$id}")->first();
    }

    if ($payment) {
        $orderId = $payment->order_id;
        $payment->delete();
        echo "✅ Record transaksi [ID #{$id} / {$orderId}] berhasil dihapus dari database.\n";
    } else {
        echo "ℹ️  Record transaksi ID #{$id} tidak ditemukan (mungkin sudah terhapus).\n";
    }

    // 3. Reset auto-increment kedua tabel agar tidak meloncat
    try {
        $maxPaymentId = (int) (Payment::max('id') ?? 0);
        $nextPaymentId = $maxPaymentId + 1;
        DB::statement("ALTER TABLE payments AUTO_INCREMENT = {$nextPaymentId}");
        echo "✅ AUTO_INCREMENT tabel payments di-reset ke {$nextPaymentId}.\n";

        $maxItemId = (int) (PaymentItem::max('id') ?? 0);
        $nextItemId = $maxItemId + 1;
        DB::statement("ALTER TABLE payment_items AUTO_INCREMENT = {$nextItemId}");
        echo "✅ AUTO_INCREMENT tabel payment_items di-reset ke {$nextItemId}.\n";
    } catch (\Throwable $e) {
        echo "⚠️  Catatan reset auto-increment: " . $e->getMessage() . "\n";
    }

    // 4. Broadcast event pembersihan ke Reverb
    if ($sendReverb) {
        echo "Mengirim sinyal broadcast pembersihan ke Reverb WebSocket...\n";
        try {
            broadcast(new DevFinancialUpdated(
                userId: 0,
                action: 'payment_deleted',
                payoutId: null,
                message: "TEST: Transaksi dummy #{$id} telah dihapus"
            ));
            echo "🚀 Sinyal broadcast pembersihan TERKIRIM! Aplikasi akan otomatis menghapus item dari layar.\n";
        } catch (\Throwable $e) {
            echo "⚠️  Broadcast gagal: " . $e->getMessage() . "\n";
        }
    }

    // 5. Update Widget Homescreen via FCM Background Silent Sync
    if ($sendFcm) {
        try {
            $fcm = app(FirebaseNotificationService::class);
            $fcm->notifyDeveloper(
                "",
                "",
                [
                    'type' => 'transaction',
                    'action' => 'sync_widgets',
                    'payment_id' => (string) $id,
                ]
            );
            echo "📱 Sinyal silent sync widget homescreen terkirim ke HP!\n";
        } catch (\Throwable $e) {
            echo "⚠️  FCM silent sync gagal: " . $e->getMessage() . "\n";
        }
    }

    echo "\n🎉 Selesai! Tabel payments & payment_items kembali bersih tanpa sisa.\n\n";
    exit(0);
}

// Mode: CREATE & BROADCAST
echo "▶ Mode: CREATE DUMMY TRANSAKSI & BROADCAST\n";

// Cek dan bersihkan jika ID dummy sudah pernah ada
$existing = Payment::find($id);
if ($existing) {
    echo "⚠️  Data transaksi ID #{$id} sudah ada. Menghapus data lama terlebih dahulu...\n";
    PaymentItem::where('payment_id', $id)->delete();
    $existing->delete();
}

$orderId = "ORDER-TEST-{$id}";
$invoiceNumber = "INV-TEST-{$id}";
$journalShare = max(0, $gross - $devShare - 1000);
$mdr = 1000.00;

echo "Menyimpan ke database (Tabel: payments):\n";
echo "- ID                  : {$id}\n";
echo "- User ID             : {$userId} (" . ($devUser?->name ?? 'System') . ")\n";
echo "- Order ID            : {$orderId}\n";
echo "- Invoice             : {$invoiceNumber}\n";
echo "- Payer Name          : Tester Realtime Production\n";
echo "- Gross Amount        : Rp " . number_format($gross, 0, ',', '.') . "\n";
echo "- Dev Net Share       : Rp " . number_format($devShare, 0, ',', '.') . "\n";
echo "- Status              : paid (settlement)\n";

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
        'gross_amount' => $gross,
        'journal_share' => $journalShare,
        'developer_gross_share' => $devShare + $mdr,
        'mdr_amount' => $mdr,
        'developer_net_share' => $devShare,
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
        'gross_amount' => $gross,
        'journal_share' => $journalShare,
        'developer_gross_share' => $devShare + $mdr,
        'mdr_amount' => $mdr,
        'developer_net_share' => $devShare,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "✅ Dummy record berhasil disimpan ke tabel payments & payment_items!\n";
} catch (\Throwable $e) {
    echo "❌ Gagal menyimpan ke DB: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Broadcast ke Reverb WebSocket
if ($sendReverb) {
    echo "Mengirim sinyal broadcast ke Reverb WebSocket...\n";
    try {
        broadcast(new DevFinancialUpdated(
            userId: 0,
            action: 'payment_received',
            payoutId: null,
            message: "Transaksi baru masuk: Order #{$orderId}"
        ));
        echo "🚀 Sinyal broadcast BERHASIL terkirim!\n\n";
    } catch (\Throwable $e) {
        echo "⚠️  Catatan Reverb WebSocket: " . $e->getMessage() . "\n";
    }
}

// 2. Kirim Notifikasi FCM & Background Sync Widget
if ($sendFcm) {
    echo "Mengirim notifikasi FCM & Sinyal Background Sync Widget...\n";
    try {
        $fcm = app(FirebaseNotificationService::class);
        $sent = $fcm->notifyDeveloper(
            "💰 Transaksi Masuk!",
            "Hak dev Rp " . number_format($devShare, 0, ',', '.') . " dari Order #{$orderId}",
            [
                'type' => 'transaction',
                'route' => '/dev/transactions',
                'action' => 'sync_widgets',
                'payment_id' => (string) $id,
                'order_id' => $orderId,
            ]
        );
        if ($sent) {
            echo "📲 Push Notification FCM & Widget Sync BERHASIL terkirim ke HP!\n";
            echo "   (Widget homescreen otomatis terupdate walaupun aplikasi ditutup!)\n\n";
        } else {
            echo "ℹ️  FCM: Belum ada developer dengan fcm_token terdaftar di database.\n";
            echo "   👉 Silakan login di aplikasi F Loafinwatch di HP terlebih dahulu.\n\n";
        }
    } catch (\Throwable $e) {
        echo "⚠️  Gagal kirim FCM: " . $e->getMessage() . "\n";
    }
}

echo "📱 CEK APLIKASI (floafinwatch):\n";
echo "1. Buka aplikasi di halaman Transaksi atau Dashboard.\n";
echo "2. Transaksi #{$orderId} (Developer Share: Rp " . number_format($devShare, 0, ',', '.') . ") akan langsung muncul seketika di posisi teratas.\n";
echo "3. Saldo Siap Cair / Pendapatan di Dashboard akan otomatis bertambah Rp " . number_format($devShare, 0, ',', '.') . " secara realtime!\n\n";
echo "🧹 CARA HAPUS SETELAH SELESAI TESTING:\n";
echo "Jalankan perintah:\n";
echo "   php scripts/test_realtime_transaction.php --cleanup --id={$id}\n\n";
