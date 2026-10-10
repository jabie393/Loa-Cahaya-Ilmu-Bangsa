<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevPayout extends Model
{
    use HasFactory;

    protected $table = 'dev_payouts';

    protected $fillable = [
        'payout_no',
        'user_id',
        'amount',
        'bank_name',
        'account_no',
        'reference_no',
        'proof_file',
        'notes',
        'rejection_reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::saved(function (DevPayout $payout) {
            try {
                broadcast(new \App\Events\DevFinancialUpdated(
                    userId: (int) $payout->user_id,
                    action: 'payout_updated',
                    payoutId: (int) $payout->id,
                    message: "Payout #{$payout->payout_no} status: {$payout->status}"
                ));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Reverb broadcast failed for DevPayout #{$payout->id}: " . $e->getMessage());
            }

            try {
                $fcm = app(\App\Services\FirebaseNotificationService::class);
                $amountFormatted = number_format($payout->amount, 0, ',', '.');

                // 1. Gambar 1: Jika status waiting_payout (antrean payout baru dibuat), gunakan gaya penulisan elegan & informatif
                if ($payout->status === 'waiting_payout') {
                    $fcm->notifyDeveloper(
                        '💸 Antrean Payout Terbit!',
                        "Dana Rp {$amountFormatted} telah masuk antrean payout ({$payout->payout_no}). Menunggu pembayaran oleh admin.",
                        [
                            'type' => 'payout',
                            'action' => 'sync_widgets',
                            'payout_id' => (string) $payout->id,
                            'payout_no' => (string) $payout->payout_no,
                            'status' => (string) $payout->status,
                        ]
                    );
                }
                // 2. Gambar 2: Jika waiting_confirmation, notifikasi sudah dikirim langsung oleh DevPayoutsTable ("Payout Siap Dikonfirmasi!"),
                //    sehingga di sini TIDAK dikirim lagi agar tidak duplikat.
                //
                // 3. Gambar 3: Jika confirmed, completed, atau rejected, tidak perlu push notification sistem HP
                //    (cukup notifikasi approve/reject di dalam aplikasi yang sudah ada).
                //    Kirim silent payload kosong agar widget di background tetap sinkron tanpa menampilkan banner pop-up di HP.
                elseif (in_array($payout->status, ['confirmed', 'completed', 'rejected'])) {
                    $fcm->notifyDeveloper(
                        '',
                        '',
                        [
                            'action' => 'sync_widgets',
                            'payout_id' => (string) $payout->id,
                            'payout_no' => (string) $payout->payout_no,
                            'status' => (string) $payout->status,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("FCM notification failed for DevPayout #{$payout->id}: " . $e->getMessage());
            }
        });
    }
}
