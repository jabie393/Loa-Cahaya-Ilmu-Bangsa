<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPlagiarismQuota extends Model
{
    protected $fillable = [
        'user_id',
        'daily_limit',
        'daily_used',
        'additional_credits',
        'total_used',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'daily_limit' => 'integer',
        'daily_used' => 'integer',
        'additional_credits' => 'integer',
        'total_used' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
