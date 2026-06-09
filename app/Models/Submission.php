<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Permission\Traits\HasRoles;

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
    }

    protected $fillable = [
        'user_id',
        'author_name',
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
}
