<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SubmissionPricing extends Model
{
    use HasFactory;

    protected $table = 'submission_pricings';

    protected $fillable = [
        'key',
        'category',
        'tier_name',
        'min_authors',
        'max_authors',
        'with_doi',
        'gross_amount',
        'developer_gross_share',
        'notes',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_authors' => 'integer',
            'max_authors' => 'integer',
            'with_doi' => 'boolean',
            'gross_amount' => 'float',
            'developer_gross_share' => 'float',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('submission_pricing_tiers');
            Cache::forget('submission_pricing_settings');
        });

        static::deleted(function () {
            Cache::forget('submission_pricing_tiers');
            Cache::forget('submission_pricing_settings');
        });
    }

    /**
     * Calculated journal gross share: gross_amount - developer_gross_share
     */
    public function getJournalShareAttribute(): float
    {
        return max(0.0, (float) $this->gross_amount - (float) $this->developer_gross_share);
    }

    /**
     * Get human-readable author range string.
     */
    public function getAuthorRangeLabelAttribute(): string
    {
        if ($this->category === 'addon' || ($this->min_authors === 0 && $this->max_authors === 0)) {
            return '-';
        }

        if ($this->min_authors && $this->max_authors) {
            return "{$this->min_authors} - {$this->max_authors} Penulis";
        }

        if ($this->min_authors && !$this->max_authors) {
            return "≥ {$this->min_authors} Penulis";
        }

        return '-';
    }
}
