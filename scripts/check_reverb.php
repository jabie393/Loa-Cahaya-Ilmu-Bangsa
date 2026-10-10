<?php

/**
 * Script Diagnostik Komprehensif Real-time Reverb WebSocket & FCM Push Notification
 * Server: loa.jurnalcib.com / Local Development
 *
 * Jalankan via Terminal / SSH:
 *   php scripts/check_reverb.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "\n======================================================\n";
echo "   🔍 DIAGNOSTIK KONEKSI REALTIME & NOTIFIKASI\n";
echo "======================================================\n\n";

// 1. Cek .env & Config Reverb
$broadcastDriver = config('broadcasting.default');
$reverbHost = config('broadcasting.connections.reverb.options.host', '127.0.0.1');
$reverbPort = config('broadcasting.connections.reverb.options.port', 8080);
$reverbAppKey = config('broadcasting.connections.reverb.key');

echo "1. KONFIGURASI LARAVEL REVERB (.env):\n";
echo "   - BROADCAST_CONNECTION : {$broadcastDriver} " . ($broadcastDriver === 'reverb' ? '✅' : '❌ (Harus reverb!)') . "\n";
echo "   - REVERB_SERVER_HOST   : {$reverbHost}\n";
echo "   - REVERB_SERVER_PORT   : {$reverbPort}\n";
echo "   - REVERB_APP_KEY       : " . ($reverbAppKey ? substr($reverbAppKey, 0, 8) . '...' : '❌ (KOSONG)') . "\n\n";

// 2. Cek apakah Port Reverb Lokal Aktif / Listening
echo "2. PENGECEKAN DAEMON REVERB ({$reverbHost}:{$reverbPort}):\n";
$connection = @fsockopen($reverbHost, (int) $reverbPort, $errno, $errstr, 2);

if (is_resource($connection)) {
    fclose($connection);
    echo "   ✅ Port {$reverbPort} TERBUKA! Daemon Reverb aktif dan mendengarkan koneksi.\n\n";
} else {
    echo "   ⚠️  TIDAK BISA KONEK ke {$reverbHost}:{$reverbPort} ($errstr)!\n";
    echo "   👉 Pastikan service Reverb dijalankan dengan: php artisan reverb:start\n\n";
}

// 3. Test Broadcast Internal
echo "3. PENGECEKAN BROADCAST EVENT INTERNAL:\n";
try {
    broadcast(new \App\Events\DevFinancialUpdated(
        userId: 0,
        action: 'diagnostic_ping',
        payoutId: null,
        message: 'Ping diagnostik pada ' . date('Y-m-d H:i:s')
    ));
    echo "   ✅ Broadcast internal ke Reverb BERHASIL dikirim tanpa error!\n\n";
} catch (\Throwable $e) {
    echo "   ❌ Gagal broadcast: " . $e->getMessage() . "\n\n";
}

// 4. Pengecekan Firebase Cloud Messaging (FCM) & Kredensial Service Account
echo "4. PENGECEKAN FIREBASE CLOUD MESSAGING (FCM):\n";
$credentialsPath = config('services.firebase.credentials') 
    ?? storage_path('app/firebase/service-account.json');

if (file_exists($credentialsPath)) {
    $serviceAccount = json_decode(file_get_contents($credentialsPath), true);
    $projectId = $serviceAccount['project_id'] ?? 'Tidak ditemukan';
    $clientEmail = $serviceAccount['client_email'] ?? 'Tidak ditemukan';
    echo "   - Status File Kredensial : ✅ Ditemukan ({$credentialsPath})\n";
    echo "   - Project ID             : {$projectId}\n";
    echo "   - Client Email           : {$clientEmail}\n";
} else {
    echo "   - Status File Kredensial : ❌ Tidak ditemukan di {$credentialsPath}\n";
    echo "   👉 Letakkan file service-account.json di storage/app/firebase/service-account.json\n";
}

// 5. Pengecekan User Developer (Role: ryu_dev) & Token FCM
echo "\n5. STATUS PENGGUNA DEVELOPER (Role: ryu_dev):\n";
try {
    $devUsers = User::role('ryu_dev')->get();
    if ($devUsers->isEmpty()) {
        echo "   ⚠️  Belum ada user dengan role 'ryu_dev' di database!\n";
    } else {
        foreach ($devUsers as $dev) {
            $hasToken = !empty($dev->fcm_token);
            $tokenSnippet = $hasToken ? substr($dev->fcm_token, 0, 15) . '...' . substr($dev->fcm_token, -10) : 'KOSONG (LOGOUT)';
            echo "   - User ID #{$dev->id} [{$dev->name} - {$dev->email}]:\n";
            echo "     FCM Token: " . ($hasToken ? "✅ {$tokenSnippet}" : "⚠️  {$tokenSnippet}") . "\n";
            if (!$hasToken) {
                echo "     👉 Harap login di aplikasi F Loafinwatch di HP agar token didaftarkan.\n";
            }
        }
    }
} catch (\Throwable $e) {
    echo "   ⚠️  Gagal memeriksa user: " . $e->getMessage() . "\n";
}

// 6. Panduan Nginx Reverse Proxy
echo "\n6. PANDUAN REVERSE PROXY NGINX (Domain: loa.jurnalcib.com):\n";
echo "   Pastikan di vhost Nginx domain terdapat konfigurasi ini:\n";
echo "   ------------------------------------------------------------\n";
echo "   location /app {\n";
echo "       proxy_pass http://{$reverbHost}:{$reverbPort};\n";
echo "       proxy_http_version 1.1;\n";
echo "       proxy_set_header Upgrade \$http_upgrade;\n";
echo "       proxy_set_header Connection \"Upgrade\";\n";
echo "       proxy_set_header Host \$host;\n";
echo "       proxy_set_header X-Real-IP \$remote_addr;\n";
echo "       proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n";
echo "       proxy_read_timeout 60s;\n";
echo "       proxy_send_timeout 60s;\n";
echo "   }\n";
echo "   ------------------------------------------------------------\n\n";
