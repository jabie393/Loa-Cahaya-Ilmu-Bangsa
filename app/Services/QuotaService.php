<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserQuota;

class QuotaService
{
    /**
     * Get or create user quota record.
     */
    public function getQuota(User $user): UserQuota
    {
        return $user->userQuota()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'daily_limit' => config('quota.default_daily_limit', 2),
                'daily_used' => 0,
                'review_credits' => 0,
                'total_used' => 0,
            ]
        );
    }

    /**
     * Check if user has enough quota for a review.
     */
    public function canRequestReview(User $user): bool
    {
        // Super admin has unlimited quota
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $quota = $this->getQuota($user);

        // Check daily quota first
        if ($quota->daily_used < $quota->daily_limit) {
            return true;
        }

        // Then check review credits
        if ($quota->review_credits > 0) {
            return true;
        }

        return false;
    }

    /**
     * Consume one quota unit.
     * Priorities: Daily Quota > Bonus Tokens.
     */
    public function consumeQuota(User $user): bool
    {
        // Super admin doesn't consume quota
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $quota = $this->getQuota($user);

        if ($quota->daily_used < $quota->daily_limit) {
            $quota->increment('daily_used');
            $quota->increment('total_used');
            return true;
        }

        if ($quota->review_credits > 0) {
            $quota->decrement('review_credits');
            $quota->increment('total_used');
            return true;
        }

        return false;
    }

    /**
     * Get quota summary for display.
     */
    public function getQuotaSummary(User $user): array
    {
        $quota = $this->getQuota($user);

        return [
            'daily_limit' => $quota->daily_limit,
            'daily_used' => $quota->daily_used,
            'daily_remaining' => max(0, $quota->daily_limit - $quota->daily_used),
            'review_credits' => $quota->review_credits,
            'total_used' => $quota->total_used,
            'total_remaining' => max(0, $quota->daily_limit - $quota->daily_used) + $quota->review_credits,
        ];
    }

    /**
     * Reset all daily quotas.
     */
    public function resetAllDailyQuotas(): void
    {
        UserQuota::query()->update(['daily_used' => 0]);
    }
}
