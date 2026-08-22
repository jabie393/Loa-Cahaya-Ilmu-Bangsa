<?php

namespace App\Services;

use App\Models\Submission;

class RepositoryIdentifierService
{
    /**
     * Generate a unique Repository Identifier for the submission.
     *
     * @param Submission $submission
     * @return string
     */
    public function generate(Submission $submission): string
    {
        $prefix = env('REPOSITORY_IDENTIFIER_PREFIX', 'DOI');
        $year = now()->format('Y');
        
        $prefixPattern = "{$prefix}-{$year}-";
        
        // Pluck all existing identifiers for this prefix and year to find the true numeric maximum
        $identifiers = Submission::where('repository_identifier', 'like', "{$prefixPattern}%")
            ->pluck('repository_identifier')
            ->toArray();
            
        $maxNum = 0;
        foreach ($identifiers as $id) {
            $parts = explode('-', $id);
            $lastPart = end($parts);
            if (is_numeric($lastPart)) {
                $num = (int)$lastPart;
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        
        $nextNum = $maxNum + 1;
        
        return "{$prefix}-{$year}-{$nextNum}";
    }
}
