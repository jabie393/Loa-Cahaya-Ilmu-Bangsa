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

    protected $fillable = [
        'user_id',
        'author_name',
        'title',
        'institution',
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
