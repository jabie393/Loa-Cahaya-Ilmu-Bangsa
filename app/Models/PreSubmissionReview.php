<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreSubmissionReview extends Model
{
    protected $fillable = [
        'author_name',
        'email',
        'journal_id',
        'title',
        'file_path',
        'structure_review',
        'abstract_review',
        'introduction_review',
        'method_review',
        'results_review',
        'conclusion_review',
        'bibliography_review',
        'general_suggestions',
        'status',
        'email_sent_at',
        'error_message',
    ];

    protected $casts = [
        'email_sent_at' => 'datetime',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
