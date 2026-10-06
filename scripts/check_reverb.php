<?php

/**
 * Script Diagnostik Reverb WebSocket untuk Server Production
 * Server: loa.jurnalcib.com
 *
 * Jalankan di SSH:
 *   php scripts/check_reverb.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n======================================================\n";
echo "   🔍 DIAGNOSTIK KONEKSI REVERB WEBSOCKET\n";
echo "======================================================\n\n";

// 1. Cek .env & Config
$broadcastDriver = config('broadcasting.default');
$reverbHost = config('broadcasting.connections.reverb.options.host', '127.0.0.1');
$reverbPort = config('broadcasting.connections.reverb.options.port', 8080);
$reverbAppKey = config('broadcasting.connections.reverb.key');

echo "1. KONFIGURASI LARAVEL (.env):\n";
echo "   - BROADCAST_CONNECTION : {$broadcastDriver} " . ($broadcastDriver === 'reverb' ? '✅' : '❌ (Harus reverb!)') . "\n";
echo "   - REVERB_SERVER_HOST   : {$reverbHost}\n";
echo "   - REVERB_SERVER_PORT   : {$reverbPort}\n";
echo "   - REVERB_APP_KEY       : " . ($reverbAppKey ? substr($reverbAppKey, 0, 8) . '...' : '❌ (KOSONG)') . "\n\n";

// 2. Cek apakah Port Reverb Lokal Aktif / Listening
echo "2. PENGECEKAN DAEMON REVERB LOKAL ({$reverbHost}:{$reverbPort}):\n";
$connection = @fsockopen($reverbHost, (int) $reverbPort, $errno, $errstr, 2);

if (is_resource($connection)) {
    fclose($connection);
    echo "   ✅ Port {$reverbPort} TERBUKA! Daemon Reverb sedang berjalan di background.\n\n";
} else {
    echo "   ❌ TIDAK BISA KONEK ke {$reverbHost}:{$reverbPort} ($errstr)!\n";
    echo "   👉 PENYEBAB UTAMA: Service Reverb belum dijalankan di server!\n";
    echo "   👉 SOLUSI: Jalankan 'php artisan reverb:start' di server.\n\n";
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

// 4. Panduan Nginx
echo "4. PANDUAN REVERSE PROXY NGINX (Domain: loa.jurnalcib.com):\n";
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
