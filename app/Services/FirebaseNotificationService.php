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
        // Deduplication guard: cegah pengiriman ganda untuk event yang sama dalam selang 10 detik
        $dedupId = $dataPayload['order_id'] ?? $dataPayload['payment_id'] ?? $dataPayload['payout_id'] ?? $body;
        $dedupKey = 'fcm_notif_dedup_' . md5($title . '|' . $dedupId);
        if (!Cache::add($dedupKey, true, 10)) {
            Log::info("[FCM] Duplicate notification suppressed within 10s: {$title} ({$dedupKey})");
            return true;
        }

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

        $tokens = $developers->pluck('fcm_token')->unique()->filter()->values();

        if ($tokens->isEmpty()) {
            Log::info('[FCM] Tidak ada developer dengan fcm_token terdaftar.');
            return false;
        }

        $success = false;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $dataPayload)) {
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

            $waitingPayoutAmount = (int) \App\Models\DevPayout::where('status', 'waiting_payout')->sum('amount');
            $waitingConfirmationAmount = (int) \App\Models\DevPayout::where('status', 'waiting_confirmation')->sum('amount');
            $rejectedPayoutAmount = (int) \App\Models\DevPayout::where('status', 'rejected')->sum('amount');
            $pendingPayout = $waitingPayoutAmount + $waitingConfirmationAmount + $rejectedPayoutAmount;

            $waitingPayoutCount = (int) \App\Models\DevPayout::where('status', 'waiting_payout')->count();
            $waitingConfirmationCount = (int) \App\Models\DevPayout::where('status', 'waiting_confirmation')->count();
            $rejectedPayoutCount = (int) \App\Models\DevPayout::where('status', 'rejected')->count();
            $unpaidPayoutCount = $waitingPayoutCount + $waitingConfirmationCount + $rejectedPayoutCount;
            $unpaidBalance = max(0, $totalEarned - $totalCommitted);

            $payoutSuccessful = (int) \App\Models\DevPayout::whereIn('status', ['confirmed', 'completed'])->count();
            $payoutPending = $unpaidPayoutCount;
            $payoutFailed = $rejectedPayoutCount;

            // 7 Days Chart (Synchronized with DeveloperFinancialController & DevPayoutsChartWidget)
            $labels = [];
            $values = [];

            $now = \Carbon\Carbon::now();
            $todayStart = $now->copy()->startOfDay();
            $todayEnd = $now->copy()->endOfDay();
            $hasConfirmedPayoutToday = \App\Models\DevPayout::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->where(function ($q) use ($todayStart, $todayEnd) {
                    $q->whereBetween('updated_at', [$todayStart, $todayEnd])
                      ->orWhere(function ($q2) use ($todayStart, $todayEnd) {
                          $q2->whereNull('updated_at')
                             ->whereBetween('created_at', [$todayStart, $todayEnd]);
                      });
                })
                ->where('amount', '>', 0)
                ->exists();

            if ($hasConfirmedPayoutToday) {
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $endDate = $now->copy()->endOfDay();
            } else {
                $startDate = $now->copy()->subDays(7)->startOfDay();
                $endDate = $now->copy()->subDays(1)->endOfDay();
            }
            $period = \Carbon\CarbonPeriod::create($startDate, '1 day', $endDate);

            $payouts = \App\Models\DevPayout::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('updated_at', [$startDate, $endDate])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          $q2->whereNull('updated_at')
                             ->whereBetween('created_at', [$startDate, $endDate]);
                      });
                })
                ->get(['amount', 'updated_at', 'created_at'])
                ->groupBy(fn($item) => ($item->updated_at ?? $item->created_at)->format('Y-m-d'))
                ->map(fn($group) => (float) $group->sum('amount'));

            foreach ($period as $date) {
                if ($date->isFuture()) {
                    continue;
                }
                $key = $date->format('Y-m-d');
                $labels[] = $date->translatedFormat('d M');
                $values[] = (float) ($payouts->get($key) ?? 0);
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
                    'rejected_payout_count' => $rejectedPayoutCount,
                    'rejected_payout_amount' => $rejectedPayoutAmount,
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

        $tag = (string) ($dataPayload['order_id'] ?? $dataPayload['payment_id'] ?? $dataPayload['payout_id'] ?? ('floafin_' . substr(md5($title . $body), 0, 10)));

        $message = [
            'token' => $fcmToken,
            'data' => $dataPayload,
            'android' => [
                'priority' => 'HIGH',
                'collapse_key' => $tag,
            ],
        ];

        if (!empty($title)) {
            $message['notification'] = [
                'title' => $title,
                'body' => $body,
            ];
            $message['android']['notification'] = [
                'channel_id' => 'floafinwatch_v2_channel',
                'sound' => 'floafinwatch',
                'notification_priority' => 'PRIORITY_MAX',
                'icon' => 'ic_notification',
                'color' => '#0284C7',
                'tag' => $tag,
            ];
        }

        $payload = [
            'message' => $message,
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
