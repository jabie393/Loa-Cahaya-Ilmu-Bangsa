<?php

namespace App\Services;

use App\Models\DevPayout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramService
{
    protected ?string $botToken;
    /** @var array<int, string> */
    protected array $chatIds = [];

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $rawChatIds = config('services.telegram.chat_ids', '');

        if (!empty($rawChatIds)) {
            $parsed = preg_split('/[,\s]+/', (string) $rawChatIds, -1, PREG_SPLIT_NO_EMPTY);
            $this->chatIds = array_values(array_unique(array_filter($parsed)));
        }
    }

    /**
     * Get configured chat IDs.
     *
     * @return array<int, string>
     */
    public function getChatIds(): array
    {
        return $this->chatIds;
    }

    /**
     * Check if Telegram notifications are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->chatIds);
    }

    /**
     * Send payout notification to all configured Telegram chat IDs.
     *
     * @param DevPayout $payout
     * @param float $remainingBalance
     * @param string|null $senderName
     * @return array{success: bool, sent: int, failed: int, errors: array<string>}
     */
    public function sendDevPayoutNotification(DevPayout $payout, float $remainingBalance, ?string $senderName = null): array
    {
        if (!$this->isConfigured()) {
            Log::info('Telegram notification skipped: TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_IDS not configured.');
            return [
                'success' => false,
                'sent' => 0,
                'failed' => 0,
                'errors' => ['TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_IDS not configured'],
            ];
        }

        $message = $this->buildDevPayoutMessage($payout, $remainingBalance, $senderName);

        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($this->chatIds as $chatId) {
            try {
                $response = Http::timeout(8)
                    ->asJson()
                    ->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $message,
                        'parse_mode' => 'HTML',
                        'disable_web_page_preview' => true,
                    ]);

                if ($response->successful()) {
                    $sentCount++;
                } else {
                    $failedCount++;
                    $errorMsg = "Telegram API error for chat {$chatId}: " . $response->body();
                    $errors[] = $errorMsg;
                    Log::warning($errorMsg);
                }
            } catch (Throwable $e) {
                $failedCount++;
                $errorMsg = "Telegram send exception for chat {$chatId}: " . $e->getMessage();
                $errors[] = $errorMsg;
                Log::error($errorMsg);
            }
        }

        return [
            'success' => $sentCount > 0,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Build formatted HTML message for developer payout.
     */
    protected function buildDevPayoutMessage(DevPayout $payout, float $remainingBalance, ?string $senderName = null): string
    {
        $amountFormatted = number_format((float) $payout->amount, 0, ',', '.');
        $remainingFormatted = number_format($remainingBalance, 0, ',', '.');
        $dateFormatted = Carbon::parse($payout->created_at ?? now())->translatedFormat('d M Y, H:i');
        $sender = $senderName ?: ($payout->user?->name ?? 'Admin');
        $reference = $payout->reference_no ?: '-';
        $notes = $payout->notes ?: 'Pencairan Hak Developer';

        return "💸 <b>PEMBAYARAN DEVELOPER (PAYOUT BARU)</b> 💸\n\n"
            . "📋 <b>No. Payout:</b> <code>{$payout->payout_no}</code>\n"
            . "💰 <b>Nominal Ditransfer:</b> <b>Rp {$amountFormatted}</b>\n"
            . "💳 <b>Sisa Hak Dev:</b> Rp {$remainingFormatted}\n"
            . "🔖 <b>No. Referensi:</b> <code>{$reference}</code>\n"
            . "📝 <b>Keterangan:</b> {$notes}\n"
            . "👤 <b>Diproses Oleh:</b> {$sender}\n"
            . "⏰ <b>Waktu:</b> {$dateFormatted} WIB\n\n"
            . "<i>Notifikasi otomatis sistem Loa-Cahaya-Ilmu-Bangsa</i>";
    }
}
