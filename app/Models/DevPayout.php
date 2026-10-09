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
                $statusText = match ($payout->status) {
                    'waiting_confirmation' => 'Siap dikonfirmasi',
                    'confirmed', 'completed' => 'Berhasil ditransfer',
                    'rejected' => 'Ditolak',
                    default => $payout->status,
                };
                $amountFormatted = number_format($payout->amount, 0, ',', '.');
                $fcm->notifyDeveloper(
                    "💸 Update Payout #{$payout->payout_no}",
                    "Payout Rp {$amountFormatted} status: {$statusText}",
                    [
                        'action' => 'sync_widgets',
                        'payout_id' => (string) $payout->id,
                        'payout_no' => (string) $payout->payout_no,
                        'status' => (string) $payout->status,
                    ]
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("FCM notification failed for DevPayout #{$payout->id}: " . $e->getMessage());
            }
        });
    }
}
