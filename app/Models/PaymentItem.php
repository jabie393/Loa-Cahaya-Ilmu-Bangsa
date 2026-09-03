<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'submission_id',
        'item_type',
        'item_name',
        'gross_amount',
        'journal_share',
        'developer_gross_share',
        'mdr_amount',
        'developer_net_share',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'journal_share' => 'decimal:2',
        'developer_gross_share' => 'decimal:2',
        'mdr_amount' => 'decimal:2',
        'developer_net_share' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
