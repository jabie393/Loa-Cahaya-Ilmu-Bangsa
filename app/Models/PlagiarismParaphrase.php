<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlagiarismParaphrase extends Model
{
    protected $fillable = [
        'plagiarism_check_id',
        'status',
        'original_score',
        'estimated_new_score',
        'improvements',
        'error_message',
    ];

    protected $casts = [
        'original_score' => 'decimal:2',
        'estimated_new_score' => 'decimal:2',
        'improvements' => 'array',
    ];

    public function plagiarismCheck(): BelongsTo
    {
        return $this->belongsTo(PlagiarismCheck::class);
    }

    /**
     * Process the academic paraphrase.
     */
    public function processParaphrase(): void
    {
        $this->update(['status' => 'processing', 'error_message' => null]);

        try {
            $paraphraseService = app('plagiarism-paraphrase')->driver();
            $results = $paraphraseService->paraphrase($this);

            $this->update([
                'estimated_new_score' => $results['estimated_new_score'] ?? 0,
                'improvements' => $results['improvements'] ?? [],
                'status' => 'completed',
            ]);

            // Send Email
            \Illuminate\Support\Facades\Mail::to($this->plagiarismCheck->email ?? $this->plagiarismCheck->user->email)
                ->send(new \App\Mail\PlagiarismParaphraseMail($this));

        } catch (\Exception $e) {
            $this->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
