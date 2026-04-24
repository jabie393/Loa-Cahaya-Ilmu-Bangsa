<?php

namespace App\Contracts;

use App\Models\PreSubmissionReview;

interface AiReviewContract
{
    /**
     * Perform an AI review on the given pre-submission review record.
     *
     * @param PreSubmissionReview $reviewRecord
     * @return array The review results
     */
    public function review(PreSubmissionReview $reviewRecord): array;
}
