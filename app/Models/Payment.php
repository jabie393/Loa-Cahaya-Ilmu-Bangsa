<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'order_id',
        'transaction_id',
        'payment_method',
        'type',
        'submission_ids',
        'gross_amount',
        'journal_share',
        'developer_gross_share',
        'mdr_amount',
        'developer_net_share',
        'transaction_status',
        'payment_status',
        'qris_url',
        'qr_string',
        'expired_at',
        'paid_at',
        'raw_response',
    ];

    protected $casts = [
        'submission_ids' => 'array',
        'gross_amount' => 'decimal:2',
        'journal_share' => 'decimal:2',
        'developer_gross_share' => 'decimal:2',
        'mdr_amount' => 'decimal:2',
        'developer_net_share' => 'decimal:2',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'raw_response' => 'array',
    ];

    
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function submissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Submission::class, 'payment_items');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isExpired(): bool
    {
        if ($this->payment_status === 'expired') {
            return true;
        }

        if ($this->isPending() && $this->expired_at && now()->greaterThanOrEqualTo($this->expired_at)) {
            return true;
        }

        return false;
    }
}
