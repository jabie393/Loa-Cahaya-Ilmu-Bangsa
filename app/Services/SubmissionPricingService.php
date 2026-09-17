<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\SubmissionPricing;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SubmissionPricingService
{
    /**
     * Default fallback MDR rate for QRIS (0.7%)
     */
    public const MDR_RATE = 0.007;

    /**
     * Default fallback member discount per item / submission (Rp 10,000)
     */
    public const MEMBER_DISCOUNT = 10000.0;

    protected static ?\Illuminate\Database\Eloquent\Collection $memoizedTiers = null;
    protected static ?\Illuminate\Database\Eloquent\Collection $memoizedSettings = null;

    public static function clearMemoized(): void
    {
        static::$memoizedTiers = null;
        static::$memoizedSettings = null;
    }

    public function getActivePricingTiers()
    {
        if (static::$memoizedTiers === null) {
            static::$memoizedTiers = SubmissionPricing::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }
        return static::$memoizedTiers;
    }

    public function getPricingSettings()
    {
        if (static::$memoizedSettings === null) {
            static::$memoizedSettings = SubmissionPricing::where('category', 'setting')
                ->get()
                ->keyBy('key');
        }
        return static::$memoizedSettings;
    }

    /**
     * Get active MDR rate (from database or default fallback).
     */
    public function getMdrRate(): float
    {
        try {
            $settings = $this->getPricingSettings();
            if (isset($settings['setting_mdr_rate']) && $settings['setting_mdr_rate']->is_active) {
                return (float) $settings['setting_mdr_rate']->gross_amount;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return self::MDR_RATE;
    }

    /**
     * Get active Member Discount (from database or default fallback).
     */
    public function getMemberDiscount(): float
    {
        try {
            $settings = $this->getPricingSettings();
            if (isset($settings['setting_member_discount']) && $settings['setting_member_discount']->is_active) {
                return (float) $settings['setting_member_discount']->gross_amount;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return self::MEMBER_DISCOUNT;
    }

    /**
     * Calculate exact pricing and revenue sharing for a submission.
     *
     * @param Submission $submission
     * @param User|null $user
     * @return array{
     *     tier_name: string,
     *     author_count: int,
     *     is_international: bool,
     *     with_doi: bool,
     *     original_amount: float,
     *     discount_amount: float,
     *     gross_amount: float,
     *     journal_share: float,
     *     developer_gross_share: float,
     *     mdr_amount: float,
     *     developer_net_share: float,
     *     is_member: bool
     * }
     */
    public function calculate(Submission $submission, ?User $user = null): array
    {
        $authorCount = $this->getAuthorCount($submission);

        if ($authorCount > 30) {
            throw new \DomainException("Jumlah penulis ({$authorCount} author) melebihi batas maksimal yang diizinkan (maksimal 30 author). Silakan hubungi admin atau sesuaikan kembali naskah Anda.");
        }

        $isInternational = $submission->isExternal();
        $withDoi = (bool) $submission->want_doi;

        // Untuk 11 author ke atas, otomatis include DOI
        if ($authorCount >= 11) {
            $withDoi = true;
        }

        $pricing = $this->determinePricing($isInternational, $withDoi, $authorCount);

        $targetUser = $user ?? $submission->user ?? auth()->user();
        $isMember = $targetUser instanceof User ? $targetUser->isMember() : false;

        $mdrRate = $this->getMdrRate();
        $memberDiscount = $this->getMemberDiscount();

        $originalGross = $pricing['gross_amount'];
        $discountAmount = $isMember ? $memberDiscount : 0.0;
        $grossAmount = max(0.0, $originalGross - $discountAmount);
        $devGross = $pricing['developer_gross_share'];

        // MDR is calculated on gross_amount, rounded
        $mdr = round($grossAmount * $mdrRate);
        // MDR dibebankan ke jurnal, developer mendapatkan porsi penuh (tanpa potongan MDR)
        $devNet = $devGross;
        // Discount dan MDR dibebankan sepenuhnya ke porsi jurnal
        $journalShare = $grossAmount - $devGross - $mdr;

        return [
            'tier_name' => $pricing['tier_name'],
            'author_count' => $authorCount,
            'is_international' => $isInternational,
            'with_doi' => $withDoi,
            'original_amount' => $originalGross,
            'discount_amount' => $discountAmount,
            'gross_amount' => $grossAmount,
            'journal_share' => $journalShare,
            'developer_gross_share' => $devGross,
            'mdr_amount' => $mdr,
            'developer_net_share' => $devNet,
            'is_member' => $isMember,
        ];
    }

    /**
     * Determine author count from submission authors array or fallback to 1.
     */
    public function getAuthorCount(Submission $submission): int
    {
        if (is_array($submission->authors) && count($submission->authors) > 0) {
            $validAuthors = 0;
            foreach ($submission->authors as $author) {
                if (is_array($author) && !empty(trim($author['name'] ?? ''))) {
                    $validAuthors++;
                }
            }
            if ($validAuthors > 0) {
                return $validAuthors;
            }
        }

        if (!empty($submission->author_name)) {
            // Check if comma or semicolon separated
            $names = preg_split('/[,;]+/', $submission->author_name);
            $count = count(array_filter(array_map('trim', $names)));
            if ($count > 0) {
                return $count;
            }
        }

        return 1;
    }

    /**
     * Map tiers based on database records, or fallback to default pricing matrix.
     */
    protected function determinePricing(bool $isInternational, bool $withDoi, int $authorCount): array
    {
        if ($authorCount >= 11) {
            $withDoi = true;
        }

        try {
            $tiers = $this->getActivePricingTiers();

            $targetCategory = $isInternational ? 'international' : 'issn';

            $matched = $tiers->first(function ($t) use ($targetCategory, $withDoi, $authorCount) {
                if ($t->category !== $targetCategory) {
                    return false;
                }

                if ($t->min_authors !== null && $authorCount < $t->min_authors) {
                    return false;
                }
                if ($t->max_authors !== null && $authorCount > $t->max_authors) {
                    return false;
                }

                if ($t->with_doi !== null && (bool) $t->with_doi !== (bool) $withDoi) {
                    // Tiers for 11+ authors include DOI by default
                    if ($t->min_authors >= 11 && $t->with_doi === true) {
                        return true;
                    }
                    return false;
                }

                return true;
            });

            if ($matched) {
                return [
                    'tier_name' => $matched->tier_name,
                    'gross_amount' => (float) $matched->gross_amount,
                    'developer_gross_share' => (float) $matched->developer_gross_share,
                ];
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        // Hardcoded Fallback Matrix (Kelipatan 5, Maks 30 Author)
        if ($isInternational) {
            if ($authorCount <= 5) {
                return [
                    'tier_name' => 'International 1-5 Author + DOI',
                    'gross_amount' => 130000.0,
                    'developer_gross_share' => 15000.0,
                ];
            } elseif ($authorCount <= 10) {
                return [
                    'tier_name' => 'International 6-10 Author + DOI',
                    'gross_amount' => 170000.0,
                    'developer_gross_share' => 20000.0,
                ];
            } elseif ($authorCount <= 15) {
                return [
                    'tier_name' => 'International 11-15 Author + DOI',
                    'gross_amount' => 200000.0,
                    'developer_gross_share' => 30000.0,
                ];
            } elseif ($authorCount <= 20) {
                return [
                    'tier_name' => 'International 16-20 Author + DOI',
                    'gross_amount' => 250000.0,
                    'developer_gross_share' => 40000.0,
                ];
            } elseif ($authorCount <= 25) {
                return [
                    'tier_name' => 'International 21-25 Author + DOI',
                    'gross_amount' => 300000.0,
                    'developer_gross_share' => 50000.0,
                ];
            } else {
                return [
                    'tier_name' => 'International 26-30 Author + DOI',
                    'gross_amount' => 350000.0,
                    'developer_gross_share' => 60000.0,
                ];
            }
        }

        // Jurnal Nasional (ISSN)
        if ($authorCount <= 5) {
            if ($withDoi) {
                return [
                    'tier_name' => 'ISSN + DOI (1-5 Author)',
                    'gross_amount' => 80000.0,
                    'developer_gross_share' => 5000.0,
                ];
            }
            return [
                'tier_name' => 'ISSN (1-5 Author)',
                'gross_amount' => 60000.0,
                'developer_gross_share' => 5000.0,
            ];
        }

        if ($authorCount <= 10) {
            if ($withDoi) {
                return [
                    'tier_name' => 'ISSN + DOI (6-10 Author)',
                    'gross_amount' => 120000.0,
                    'developer_gross_share' => 10000.0,
                ];
            }
            return [
                'tier_name' => 'ISSN (6-10 Author)',
                'gross_amount' => 100000.0,
                'developer_gross_share' => 10000.0,
            ];
        }

        if ($authorCount <= 15) {
            return [
                'tier_name' => 'ISSN + DOI (11-15 Author)',
                'gross_amount' => 150000.0,
                'developer_gross_share' => 20000.0,
            ];
        }

        if ($authorCount <= 20) {
            return [
                'tier_name' => 'ISSN + DOI (16-20 Author)',
                'gross_amount' => 200000.0,
                'developer_gross_share' => 30000.0,
            ];
        }

        if ($authorCount <= 25) {
            return [
                'tier_name' => 'ISSN + DOI (21-25 Author)',
                'gross_amount' => 250000.0,
                'developer_gross_share' => 40000.0,
            ];
        }

        // 26-30 author
        return [
            'tier_name' => 'ISSN + DOI (26-30 Author)',
            'gross_amount' => 300000.0,
            'developer_gross_share' => 50000.0,
        ];
    }

    /**
     * Calculate pricing specifically for DOI Addon.
     *
     * @param User|null $user
     * @return array
     */
    public function calculateDoiAddon(?User $user = null): array
    {
        $originalGross = 20000.0;
        $devGross = 5000.0;
        $tierName = 'Add-on DOI Repository Identifier';

        try {
            $tiers = $this->getActivePricingTiers();
            $addon = $tiers->firstWhere('key', 'addon_doi')
                ?: $tiers->first(fn($t) => $t->category === 'addon' && (bool) $t->with_doi === true);

            if ($addon) {
                $originalGross = (float) $addon->gross_amount;
                $devGross = (float) $addon->developer_gross_share;
                $tierName = $addon->tier_name;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $targetUser = $user ?? auth()->user();
        $isMember = $targetUser instanceof User ? $targetUser->isMember() : false;
        $mdrRate = $this->getMdrRate();

        $discountAmount = 0.0;
        $grossAmount = $originalGross;
        $mdr = round($grossAmount * $mdrRate);
        $devNet = $devGross;
        $journalShare = $grossAmount - $devGross - $mdr;

        return [
            'tier_name' => $tierName,
            'author_count' => 0,
            'is_international' => false,
            'with_doi' => true,
            'original_amount' => $originalGross,
            'discount_amount' => $discountAmount,
            'gross_amount' => $grossAmount,
            'journal_share' => $journalShare,
            'developer_gross_share' => $devGross,
            'mdr_amount' => $mdr,
            'developer_net_share' => $devNet,
            'is_member' => $isMember,
        ];
    }

    /**
     * Calculate pricing specifically for Replace PDF service.
     *
     * @param User|null $user
     * @return array
     */
    public function calculateReplacePdf(?User $user = null): array
    {
        $originalGross = 25000.0;
        $devGross = 5000.0;
        $tierName = 'Ganti PDF Naskah';

        try {
            $tiers = $this->getActivePricingTiers();
            $addon = $tiers->firstWhere('key', 'service_replace_pdf')
                ?: $tiers->first(fn($t) => $t->category === 'addon' && !(bool) $t->with_doi);

            if ($addon) {
                $originalGross = (float) $addon->gross_amount;
                $devGross = (float) $addon->developer_gross_share;
                $tierName = $addon->tier_name;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $targetUser = $user ?? auth()->user();
        $isMember = $targetUser instanceof User ? $targetUser->isMember() : false;
        $mdrRate = $this->getMdrRate();

        $discountAmount = 0.0;
        $grossAmount = $originalGross;
        $mdr = round($grossAmount * $mdrRate);
        $devNet = $devGross;
        $journalShare = $grossAmount - $devGross - $mdr;

        return [
            'tier_name' => $tierName,
            'author_count' => 0,
            'is_international' => false,
            'with_doi' => false,
            'original_amount' => $originalGross,
            'discount_amount' => $discountAmount,
            'gross_amount' => $grossAmount,
            'journal_share' => $journalShare,
            'developer_gross_share' => $devGross,
            'mdr_amount' => $mdr,
            'developer_net_share' => $devNet,
            'is_member' => $isMember,
        ];
    }

    /**
     * Calculate cumulative pricing breakdown for multiple submissions.
     *
     * @param iterable $submissions
     * @param User|null $user
     * @return array
     */
    public function calculateBulk($submissions, ?User $user = null): array
    {
        $targetUser = $user ?? auth()->user();
        $isMember = $targetUser instanceof User ? $targetUser->isMember() : false;

        $items = [];
        $totalOriginal = 0;
        $totalDiscount = 0;
        $totalGross = 0;
        $totalJournal = 0;
        $totalDevGross = 0;
        $totalMdr = 0;
        $totalDevNet = 0;

        foreach ($submissions as $submission) {
            $subUser = $targetUser ?? $submission->user;
            $pricing = $this->calculate($submission, $subUser);
            $items[] = [
                'submission' => $submission,
                'pricing' => $pricing,
            ];

            $totalOriginal += $pricing['original_amount'];
            $totalDiscount += $pricing['discount_amount'];
            $totalGross += $pricing['gross_amount'];
            $totalJournal += $pricing['journal_share'];
            $totalDevGross += $pricing['developer_gross_share'];
            $totalMdr += $pricing['mdr_amount'];
            $totalDevNet += $pricing['developer_net_share'];
        }

        return [
            'items' => $items,
            'count' => count($items),
            'original_amount' => $totalOriginal,
            'discount_amount' => $totalDiscount,
            'gross_amount' => $totalGross,
            'journal_share' => $totalJournal,
            'developer_gross_share' => $totalDevGross,
            'mdr_amount' => $totalMdr,
            'developer_net_share' => $totalDevNet,
            'is_member' => $isMember,
        ];
    }
}
