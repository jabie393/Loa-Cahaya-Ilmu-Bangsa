<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Mail\PreSubmissionReviewMail;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use App\Services\QuotaService;
use Exception;

class PreSubmissionReview extends Model
{
    protected $fillable = [
        'user_id',
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
        'user_id' => 'integer',
        'email_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function processReview(): void
    {
        $this->update(['status' => 'processing', 'error_message' => null]);

        try {
            // resolve AI service through manager
            $aiService = app('ai-review')->driver();

            // Perform Review
            $results = $aiService->review($this);

            // Update Record
            $this->update([
                'title' => $results['detected_title'] ?? null,
                'structure_review' => $results['structure_review'] ?? null,
                'abstract_review' => $results['abstract_review'] ?? null,
                'introduction_review' => $results['introduction_review'] ?? null,
                'method_review' => $results['method_review'] ?? null,
                'results_review' => $results['results_review'] ?? null,
                'conclusion_review' => $results['conclusion_review'] ?? null,
                'bibliography_review' => $results['bibliography_review'] ?? null,
                'general_suggestions' => $results['general_suggestions'] ?? null,
                'status' => 'reviewed',
            ]);

            // Consume Quota
            app(QuotaService::class)->consumeQuota($this->user);

            // Send Email
            Mail::to($this->email)->send(new PreSubmissionReviewMail($this));

            $this->update(['email_sent_at' => now()]);

            Notification::make()
                ->title('Request Review Berhasil Terkirim')
                ->success()
                ->send();

        } catch (Exception $e) {
            $this->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $errorMessage = (config('app.env') === 'local' || env('APP_ENV') === 'local')
                ? $e->getMessage() 
                : 'mohon maaf reviewer sedang sibuk, coba request ulang dalam beberapa menit';

            Notification::make()
                ->title('Gagal Memproses Review')
                ->body($errorMessage)
                ->danger()
                ->send();
        }
    }
}
