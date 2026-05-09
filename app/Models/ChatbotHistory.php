<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotHistory extends Model
{
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(ChatbotSession::class, 'session_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
