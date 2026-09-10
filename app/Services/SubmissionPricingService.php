<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\User;

class SubmissionPricingService
{
    /**
     * MDR rate for QRIS (0.7%)
     */
    public const MDR_RATE = 0.007;

    /**
     * Member discount per item / submission (Rp 10,000)
     */
    public const MEMBER_DISCOUNT = 10000.0;

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
        $isInternational = $submission->isExternal();
        $withDoi = (bool) $submission->want_doi;

        $pricing = $this->determinePricing($isInternational, $withDoi, $authorCount);

        $targetUser = $user ?? $submission->user ?? auth()->user();
        $isMember = $targetUser instanceof User ? $targetUser->isMember() : false;

        $originalGross = $pricing['gross_amount'];
        $discountAmount = $isMember ? self::MEMBER_DISCOUNT : 0.0;
        $grossAmount = max(0.0, $originalGross - $discountAmount);
        $devGross = $pricing['developer_gross_share'];

        // MDR is 0.7% of gross_amount, rounded
        $mdr = round($grossAmount * self::MDR_RATE);
        $devNet = $devGross - $mdr;
        // Discount is fully absorbed by the journal share
        $journalShare = $grossAmount - $devGross;

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
     * Map tiers based on Price List:
     *
     * ISSN:
     * - 1-5 author:
     *     without DOI: Rp 60,000 (Dev 5,000)
     *     with DOI   : Rp 80,000 (Dev 5,000)
     * - 6-10 author:
     *     without DOI: Rp 100,000 (Dev 10,000)
     *     with DOI   : Rp 120,000 (Dev 10,000)
     * - 11-15 author (+ DOI):
     *     Rp 150,000 (Dev 20,000)
     * - 16-20 author (+ DOI):
     *     Rp 200,000 (Dev 30,000)
     *
     * International (IJEFI / PJLS):
     * - 1-10 author (+ DOI):
     *     Rp 150,000 (Dev 20,000)
     * - 11-15 author (+ DOI, or >=11):
     *     Rp 200,000 (Dev 30,000)
     */
    protected function determinePricing(bool $isInternational, bool $withDoi, int $authorCount): array
    {
        if ($isInternational) {
            if ($authorCount <= 10) {
                return [
                    'tier_name' => 'International 1-10 Author + DOI',
                    'gross_amount' => 150000.0,
                    'developer_gross_share' => 20000.0,
                ];
            } else {
                return [
                    'tier_name' => 'International 11-15 Author + DOI',
                    'gross_amount' => 200000.0,
                    'developer_gross_share' => 30000.0,
                ];
            }
        }

        // ISSN Journals
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

        // 16-20 author or more
        return [
            'tier_name' => 'ISSN + DOI (16-20 Author)',
            'gross_amount' => 200000.0,
            'developer_gross_share' => 30000.0,
        ];
    }

    /**
     * Calculate pricing specifically for DOI Addon.
     * Price: Rp 20,000 | Dev: Rp 5,000 | MDR (0.7%) | Member discount: Rp 10,000
     *
     * @param User|null $user
     * @return array
     */
    public function calculateDoiAddon(?User $user = null): array
    {
        $targetUser = $user ?? auth()->user();
        $isMember = $targetUser instanceof User ? $targetUser->isMember() : false;

        $originalGross = 20000.0;
        $discountAmount = $isMember ? self::MEMBER_DISCOUNT : 0.0;
        $grossAmount = max(0.0, $originalGross - $discountAmount);
        $devGross = 5000.0;
        $mdr = round($grossAmount * self::MDR_RATE);
        $devNet = $devGross - $mdr;
        $journalShare = $grossAmount - $devGross;

        return [
            'tier_name' => 'Add-on DOI Repository Identifier',
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
     * Price: Rp 25,000 | Dev: Rp 5,000 | MDR (0.7%) | Member discount: Rp 10,000
     *
     * @param User|null $user
     * @return array
     */
    public function calculateReplacePdf(?User $user = null): array
    {
        $targetUser = $user ?? auth()->user();
        $isMember = $targetUser instanceof User ? $targetUser->isMember() : false;

        $originalGross = 25000.0;
        $discountAmount = $isMember ? self::MEMBER_DISCOUNT : 0.0;
        $grossAmount = max(0.0, $originalGross - $discountAmount);
        $devGross = 5000.0;
        $mdr = round($grossAmount * self::MDR_RATE);
        $devNet = $devGross - $mdr;
        $journalShare = $grossAmount - $devGross;

        return [
            'tier_name' => 'Ganti PDF Naskah',
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
