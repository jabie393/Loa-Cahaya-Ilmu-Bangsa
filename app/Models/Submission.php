<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

class Submission extends Model
{
    use HasFactory, HasRoles;

    protected static function booted()
    {
        static::creating(function ($submission) {
            if (empty($submission->date_of_loa)) {
                $submission->date_of_loa = now();
            }
        });

        static::saving(function ($submission) {
            if ($submission->isDirty('authors') && is_array($submission->authors)) {
                $names = [];
                foreach ($submission->authors as $author) {
                    if (!empty($author['name'])) {
                        $names[] = trim($author['name']);
                    }
                }
                $submission->author_name = implode(', ', $names);
            }
        });

        static::saved(function ($submission) {
            if ($submission->manuscript_file) {
                $oldPath = $submission->manuscript_file;
                $disk = 'public';

                // Get file extension
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFilename = "file-{$submission->id}" . ($extension ? ".{$extension}" : "");
                $newPath = "manuscripts/{$newFilename}";

                if ($oldPath !== $newPath) {
                    if (Storage::disk($disk)->exists($oldPath)) {
                        if (Storage::disk($disk)->exists($newPath)) {
                            Storage::disk($disk)->delete($newPath);
                        }
                        Storage::disk($disk)->move($oldPath, $newPath);

                        $submission->manuscript_file = $newPath;
                        $submission->saveQuietly();
                    }
                }
            }
        });
    }

    protected $fillable = [
        'user_id',
        'author_name',
        'authors',
        'title',
        'email',
        'journal_id',
        'volume',
        'publication_link',
        'date_of_loa',
        'proof_of_payment',
        'status',
        'rejection_reason',
        'submission_date',
        'approved_date',
        'rejected_date',
        'abstract',
        'keywords',
        'references',
        'manuscript_file',
        'ojs_submission_id',
        'ojs_username',
        'ojs_password',
        'ojs_status',
        'ojs_synced_at',
        'ojs_error_message',
        'review_status',
        'structure_review',
        'abstract_review',
        'introduction_review',
        'method_review',
        'results_review',
        'conclusion_review',
        'bibliography_review',
        'general_suggestions',
        'review_error_message',
        'review_email_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'journal_id' => 'integer',
            'date_of_loa' => 'date',
            'submission_date' => 'date',
            'approved_date' => 'date',
            'rejected_date' => 'date',
            'ojs_synced_at' => 'datetime',
            'review_email_sent_at' => 'datetime',
            'authors' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function getFormattedAuthorsAttribute(): string
    {
        if (!empty($this->authors) && is_array($this->authors)) {
            $names = [];
            foreach ($this->authors as $author) {
                $name = $author['name'] ?? '';
                $inst = $author['institution'] ?? '';
                if (!empty($name)) {
                    $names[] = $name . (!empty($inst) ? " ({$inst})" : "");
                }
            }
            return implode(', ', $names);
        }

        return $this->author_name ?: '';
    }

    public function getTemplateView(): string
    {
        $journal = $this->journal;
        if (!$journal) {
            return 'filament.loa_pdf.default';
        }

        $slug = $journal->slug;
        $mapping = [
            'medicnutricia' => 'Medic Nutricia',
        ];

        $folderName = $mapping[$slug] ?? \Illuminate\Support\Str::studly($slug);
        
        return "filament.loa_pdf.LOA_{$folderName}.LOA_{$folderName}";
    }

    public function processReview(): void
    {
        $this->refresh();

        $this->update(['review_status' => 'processing', 'review_error_message' => null]);

        try {
            // Resolve AI service through manager
            $aiService = app('ai-review')->driver();

            // Perform Review
            $results = $aiService->review($this);

            // Update Record
            $this->update([
                'structure_review' => $results['structure_review'] ?? null,
                'abstract_review' => $results['abstract_review'] ?? null,
                'introduction_review' => $results['introduction_review'] ?? null,
                'method_review' => $results['method_review'] ?? null,
                'results_review' => $results['results_review'] ?? null,
                'conclusion_review' => $results['conclusion_review'] ?? null,
                'bibliography_review' => $results['bibliography_review'] ?? null,
                'general_suggestions' => $results['general_suggestions'] ?? null,
                'review_status' => 'reviewed',
                'status' => 'Pending',
            ]);

            // Consume Quota
            app(\App\Services\QuotaService::class)->consumeQuota($this->user);

            // Send Email
            \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\PreSubmissionReviewMail($this));

            $this->update(['review_email_sent_at' => now()]);

            \Filament\Notifications\Notification::make()
                ->title('Request Review Berhasil Terkirim')
                ->success()
                ->send();

        } catch (\Exception $e) {
            $this->update([
                'review_status' => 'failed',
                'review_error_message' => $e->getMessage(),
            ]);

            $errorMessage = (config('app.env') === 'local' || env('APP_ENV') === 'local')
                ? $e->getMessage()
                : 'Mohon maaf reviewer sedang sibuk, coba request ulang naskah ini dalam beberapa menit dengan menekan tombol "Request Again"';

            \Filament\Notifications\Notification::make()
                ->title('Gagal Memproses Review')
                ->body($errorMessage)
                ->danger()
                ->send();
        }
    }
}
