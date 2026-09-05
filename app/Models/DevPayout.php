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
}
