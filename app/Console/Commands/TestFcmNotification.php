<?php

namespace App\Console\Commands;

use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

class TestFcmNotification extends Command
{
    protected $signature = 'dev:test-fcm {--title=Tes Notifikasi FCM} {--body=Uji coba sinkronisasi widget dan push notification ke F Loafinwatch}';
    protected $description = 'Kirim tes notifikasi FCM ke developer terdaftar dan uji background widget sync';

    public function handle(FirebaseNotificationService $fcmService): int
    {
        $title = $this->option('title');
        $body = $this->option('body');

        $this->info("Mengirim push notifikasi FCM: '{$title}' - '{$body}'...");

        $success = $fcmService->notifyDeveloper($title, $body, [
            'action' => 'sync_widgets',
        ]);

        if ($success) {
            $this->info('✅ Notifikasi FCM berhasil dikirim ke perangkat developer!');
            return self::SUCCESS;
        }

        $this->error('❌ Gagal mengirim notifikasi. Pastikan:');
        $this->warn('1. Developer sudah login di aplikasi F Loafinwatch sehingga fcm_token tersimpan di database.');
        $this->warn('2. File service-account.json dari Firebase Console sudah ditaruh di storage/app/firebase/service-account.json.');
        return self::FAILURE;
    }
}
