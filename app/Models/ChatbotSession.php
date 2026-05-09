<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected $casts = [
        'current_context' => 'array',
        'last_activity' => 'datetime',
    ];

    public function histories()
    {
        return $this->hasMany(ChatbotHistory::class, 'session_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
