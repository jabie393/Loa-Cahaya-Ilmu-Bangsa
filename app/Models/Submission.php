<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'author_name',
    'title',
    'institution',
    'email',
    'journal_id',
    'volume',
    'date_of_loa',
    'proof_of_payment',
    'status',
    'submission_date',
    'approved_date',
])]
class Submission extends Model
{
    use HasFactory, HasRoles;

    protected function casts(): array
    {
        return [
            'journal_id' => 'integer',
            'date_of_loa' => 'date',
            'submission_date' => 'date',
            'approved_date' => 'date',
        ];
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
