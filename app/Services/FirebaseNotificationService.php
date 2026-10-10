<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected ?string $projectId = null;
    protected ?string $clientEmail = null;
    protected ?string $privateKey = null;

    public function __construct()
    {
        $credentialsPath = config('services.firebase.credentials') 
            ?? storage_path('app/firebase/service-account.json');

        if (file_exists($credentialsPath)) {
            $data = json_decode(file_get_contents($credentialsPath), true);
            $this->projectId = $data['project_id'] ?? 'floafinwatch';
            $this->clientEmail = $data['client_email'] ?? null;
            $this->privateKey = $data['private_key'] ?? null;
        } else {
            $this->projectId = 'floafinwatch';
        }
    }

    /**
     * Kirim push notifikasi sekaligus sinyal sync widget ke semua developer atau user tertentu
     */
    public function notifyDeveloper(string $title, string $body, array $dataPayload = []): bool
    {
        if (empty($dataPayload['payload'])) {
            $dashboardPayload = $this->getDashboardPayload();
            if (!empty($dashboardPayload)) {
                $dataPayload['payload'] = $dashboardPayload;
            }
        }

        $developers = User::role('ryu_dev')
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get();

        if ($developers->isEmpty()) {
            Log::info('[FCM] Tidak ada developer dengan fcm_token terdaftar.');
            return false;
        }

        $success = false;
        foreach ($developers as $dev) {
            if ($this->sendToToken($dev->fcm_token, $title, $body, $dataPayload)) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Generate fresh DeveloperDashboardData payload JSON for instant widget update without extra HTTP request.
     */
    public function getDashboardPayload(): string
    {
        try {
            $totalEarned = (int) \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
            $totalTransferred = (int) \App\Models\DevPayout::whereIn('status', ['confirmed', 'completed'])->sum('amount');
            $totalCommitted = (int) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed', 'rejected'])->sum('amount');

            $pendingPayout = (int) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation'])->sum('amount');
            $unpaidPayoutCount = (int) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation'])->count();
            $waitingPayoutCount = (int) \App\Models\DevPayout::where('status', 'waiting_payout')->count();
            $waitingConfirmationCount = (int) \App\Models\DevPayout::where('status', 'waiting_confirmation')->count();
            $unpaidBalance = max(0, $totalEarned - $totalCommitted);

            $payoutSuccessful = (int) \App\Models\DevPayout::whereIn('status', ['confirmed', 'completed'])->count();
            $payoutPending = (int) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation'])->count();
            $payoutFailed = (int) \App\Models\DevPayout::where('status', 'rejected')->count();

            // 7 Days Chart
            $labels = [];
            $values = [];
            $period = \Carbon\CarbonPeriod::create(now()->subDays(6)->startOfDay(), now()->endOfDay());
            foreach ($period as $date) {
                $labels[] = $date->format('d M');
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();
                $amount = (float) \App\Models\DevPayout::whereIn('status', ['confirmed', 'completed'])
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount');
                $values[] = $amount;
            }

            return json_encode([
                'summary' => [
                    'total_earned' => $totalEarned,
                    'total_transferred' => $totalTransferred,
                    'pending_payout' => $pendingPayout,
                    'today_earned' => $unpaidBalance,
                    'unpaid_payout_count' => $unpaidPayoutCount,
                    'waiting_payout_count' => $waitingPayoutCount,
                    'waiting_confirmation_count' => $waitingConfirmationCount,
                ],
                'payout_statistics' => [
                    'successful' => $payoutSuccessful,
                    'pending' => $payoutPending,
                    'failed' => $payoutFailed,
                ],
                'chart' => [
                    'period' => '7d',
                    'labels' => $labels,
                    'values' => $values,
                ],
                'last_updated' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[FCM] Error generating dashboard payload: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Kirim notifikasi ke token perangkat tertentu via FCM HTTP v1 (High-Priority Data Message)
     */
    public function sendToToken(string $fcmToken, string $title, string $body, array $dataPayload = []): bool
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::warning('[FCM] Tidak dapat memperoleh OAuth2 Access Token. Pastikan service-account.json sudah ada.');
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        // Pastikan action sync_widgets, title, body, dan route tujuan selalu terlampir dalam data payload
        $dataPayload = array_merge([
            'action' => 'sync_widgets',
            'route' => '/dev/payouts',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'title' => $title,
            'body' => $body,
        ], array_map('strval', $dataPayload));

        // Mengirimkan high-priority data message murni tanpa top-level notification
        // agar OS Android selalu memanggil onBackgroundMessage handler saat aplikasi mati total
        $payload = [
            'message' => [
                'token' => $fcmToken,
                'data' => $dataPayload,
                'android' => [
                    'priority' => 'HIGH',
                ],
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("[FCM] Notifikasi data berhasil dikirim ke token: " . substr($fcmToken, 0, 15) . '...');
                return true;
            } else {
                Log::error("[FCM] Gagal mengirim pesan: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("[FCM Exception] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buat Google OAuth2 Access Token menggunakan Service Account JSON (Google HTTP v1 API)
     */
    protected function getAccessToken(): ?string
    {
        if (!$this->clientEmail || !$this->privateKey) {
            return null;
        }

        return Cache::remember('fcm_google_access_token', 3300, function () {
            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claim = json_encode([
                'iss' => $this->clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]);

            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlClaim = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claim));

            $signature = '';
            $success = openssl_sign(
                $base64UrlHeader . "." . $base64UrlClaim,
                $signature,
                $this->privateKey,
                OPENSSL_ALGO_SHA256
            );

            if (!$success) {
                Log::error('[FCM] Gagal membuat signature OpenSSL untuk Google Service Account.');
                return null;
            }

            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            $jwt = $base64UrlHeader . "." . $base64UrlClaim . "." . $base64UrlSignature;

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('[FCM OAuth2 Error] ' . $response->body());
            return null;
        });
    }
}
