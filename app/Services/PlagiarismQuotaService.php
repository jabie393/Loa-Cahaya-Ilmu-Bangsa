<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPlagiarismQuota;

class PlagiarismQuotaService
{
    /**
     * Get or create user plagiarism quota record.
     */
    public function getQuota(User $user): UserPlagiarismQuota
    {
        return $user->userPlagiarismQuota()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'daily_limit' => config('quota.plagiarism_daily_limit', 2),
                'daily_used' => 0,
                'additional_credits' => 0,
                'total_used' => 0,
            ]
        );
    }

    /**
     * Check if user has enough quota for a plagiarism check.
     */
    public function canCheck(User $user): bool
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

        // Then check additional credits
        if ($quota->additional_credits > 0) {
            return true;
        }

        return false;
    }

    /**
     * Consume one quota unit.
     * Priorities: Daily Quota > Additional Credits.
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

        if ($quota->additional_credits > 0) {
            $quota->decrement('additional_credits');
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
            'additional_credits' => $quota->additional_credits,
            'total_used' => $quota->total_used,
            'total_remaining' => max(0, $quota->daily_limit - $quota->daily_used) + $quota->additional_credits,
        ];
    }

    /**
     * Reset all daily quotas for plagiarism.
     */
    public function resetAllDailyQuotas(): void
    {
        UserPlagiarismQuota::query()->update(['daily_used' => 0]);
    }
}
