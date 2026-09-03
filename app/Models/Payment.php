<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'submission_id',
        'submission_ids',
        'invoice_number',
        'order_id',
        'transaction_id',
        'payment_method',
        'type',
        'payer_name',
        'payer_email',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function submissions(): BelongsToMany
    {
        return $this->belongsToMany(Submission::class, 'payment_items');
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

    /**
     * Generate and save permanent invoice number if not already generated.
     */
    public function ensureInvoiceNumber(): string
    {
        if (!empty($this->invoice_number)) {
            return $this->invoice_number;
        }

        $datePart = ($this->paid_at ?? now())->format('Ymd');
        $prefix = ($this->type === 'bulk_submission') ? 'INV/BULK' : 'INV/CIB';
        $number = sprintf('%s/%s/%04d', $prefix, $datePart, $this->id);

        $this->update(['invoice_number' => $number]);

        return $number;
    }
}
