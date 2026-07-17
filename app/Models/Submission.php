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
            if (empty($submission->author_name)) {
                $submission->author_name = \Illuminate\Support\Facades\Auth::user()?->name ?? ($submission->user?->name ?? 'Author');
            }
            if (empty($submission->email)) {
                $submission->email = \Illuminate\Support\Facades\Auth::user()?->email ?? ($submission->user?->email ?? '');
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
            return 'filament.loa_pdf.LOA_Argopuro.LOA_Argopuro';
        }

        $slug = $journal->slug;
        $mapping = [
            'medicnutricia' => 'Medic Nutricia',
        ];

        $folderName = $mapping[$slug] ?? \Illuminate\Support\Str::studly($slug);
        $view = "filament.loa_pdf.LOA_{$folderName}.LOA_{$folderName}";
        
        return view()->exists($view) ? $view : 'filament.loa_pdf.LOA_Argopuro.LOA_Argopuro';
    }

    public function getAcTemplateView(): string
    {
        $journal = $this->journal;
        if (!$journal) {
            return 'filament.ac.ac_pdf';
        }

        $folderName = \Illuminate\Support\Str::studly($journal->slug);
        $customView = "filament.ac.AC_{$folderName}";
        
        return view()->exists($customView) ? $customView : 'filament.ac.ac_pdf';
    }

    public function getPfcTemplateView(): string
    {
        $journal = $this->journal;
        if (!$journal) {
            return 'filament.pfc.pfc_pdf';
        }

        $folderName = \Illuminate\Support\Str::studly($journal->slug);
        $customView = "filament.pfc.PFC_{$folderName}";
        
        return view()->exists($customView) ? $customView : 'filament.pfc.pfc_pdf';
    }

    public function processReview(): void
    {
        $this->refresh();

        $journal = $this->journal;
        $defaultUrl = config('ojs.base_url');

        $this->update(['review_status' => 'processing', 'review_error_message' => null]);

        try {
            // Resolve AI service through manager
            $aiService = app('ai-review')->driver();

            // Perform Review
            $results = $aiService->review($this);

            $isExternal = $this->isExternal();
            $reviewStatus = $isExternal ? 'N/A' : 'reviewed';

            // Update Record
            $updates = [
                'structure_review' => $results['structure_review'] ?? null,
                'abstract_review' => $results['abstract_review'] ?? null,
                'introduction_review' => $results['introduction_review'] ?? null,
                'method_review' => $results['method_review'] ?? null,
                'results_review' => $results['results_review'] ?? null,
                'conclusion_review' => $results['conclusion_review'] ?? null,
                'bibliography_review' => $results['bibliography_review'] ?? null,
                'general_suggestions' => $results['general_suggestions'] ?? null,
                'review_status' => $reviewStatus,
                'status' => 'Pending',
            ];

            // Metadata updates from automated extraction (only if currently empty, to prioritize manual input)
            if (empty($this->title) && !empty($results['detected_title'])) {
                $updates['title'] = $results['detected_title'];
            }
            if (empty($this->abstract) && !empty($results['detected_abstract'])) {
                $updates['abstract'] = $results['detected_abstract'];
            }
            if (empty($this->keywords) && !empty($results['detected_keywords'])) {
                $updates['keywords'] = is_array($results['detected_keywords']) ? implode(', ', $results['detected_keywords']) : $results['detected_keywords'];
            }
            if (empty($this->references) && !empty($results['detected_references'])) {
                $updates['references'] = $results['detected_references'];
            }

            // Check if the current authors list is the default one (1 author, matching user name or empty, with empty institution)
            $isDefaultAuthor = false;
            if (is_array($this->authors) && count($this->authors) === 1) {
                $firstAuthor = $this->authors[0];
                $defaultName = $this->user?->name ?? '';
                if (($firstAuthor['name'] === $defaultName || empty($firstAuthor['name'])) && empty($firstAuthor['institution'])) {
                    $isDefaultAuthor = true;
                }
            }

            // Fallback metadata updates (only if currently empty or default, to prioritize manual input)
            if ((empty($this->authors) || $isDefaultAuthor) && !empty($results['detected_authors']) && is_array($results['detected_authors'])) {
                $updates['authors'] = $results['detected_authors'];
            }

            // Fallback to user details if both automated and manual input are empty
            if (empty($this->authors) && empty($updates['authors'])) {
                $updates['authors'] = [
                    ['name' => $this->user?->name ?? 'Author', 'institution' => '']
                ];
            }
            if (empty($this->email) && empty($updates['email'])) {
                $updates['email'] = $this->user?->email;
            }

            $this->update($updates);

            // Consume Quota
            app(\App\Services\QuotaService::class)->consumeQuota($this->user);

            // Send Pre-Submission Review Email (Only for internal journals)
            if (!$isExternal) {
                \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\PreSubmissionReviewMail($this));
                $this->update(['review_email_sent_at' => now()]);
            }

            if (!app()->runningInConsole()) {
                \Filament\Notifications\Notification::make()
                    ->title($isExternal ? 'Ekstraksi Metadata Berhasil' : 'Request Review Berhasil Terkirim')
                    ->success()
                    ->send();
            }

        } catch (\Exception $e) {
            $this->update([
                'review_status' => 'failed',
                'review_error_message' => $e->getMessage(),
            ]);

            $errorMessage = (config('app.env') === 'local' || env('APP_ENV') === 'local')
                ? $e->getMessage()
                : 'Mohon maaf reviewer sedang sibuk, coba request ulang naskah ini dalam beberapa menit dengan menekan tombol "Request Again"';

            if (!app()->runningInConsole()) {
                \Filament\Notifications\Notification::make()
                    ->title('Gagal Memproses Review')
                    ->body($errorMessage)
                    ->danger()
                    ->send();
            }
        }
    }

    /**
     * Trigger submission review process in the background.
     *
     * @return void
     */
    public function processReviewInBackground(): void
    {
        $this->update([
            'review_status' => 'processing',
            'review_error_message' => null,
        ]);

        $id = (int) $this->id;
        $artisanPath = base_path('artisan');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows background execution
            pclose(popen("start /B php \"" . $artisanPath . "\" submission:process-review " . $id . " > NUL", "r"));
        } else {
            // Linux/Unix background execution - redirect stdin (< /dev/null) to completely detach from PHP-FPM
            exec("php \"" . $artisanPath . "\" submission:process-review " . $id . " < /dev/null > /dev/null 2>&1 &");
        }
    }

    public function isExternal(): bool
    {
        $ojsUrl = $this->journal?->ojs_base_url;
        if (empty($ojsUrl)) {
            return false;
        }

        $host = parse_url($ojsUrl, PHP_URL_HOST);
        if (empty($host)) {
            $host = str_replace(['https://', 'http://', '/'], '', $ojsUrl);
        }

        return in_array($host, ['pjlsedu.com', 'ijefijournal.com']);
    }

    public function sendApprovalEmail(): void
    {
        if ($this->isExternal()) {
            \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\ExternalSubmissionApproved($this));
        } else {
            \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\SubmissionApproved($this));
        }
    }
}
