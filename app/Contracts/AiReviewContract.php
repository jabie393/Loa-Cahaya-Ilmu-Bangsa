<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AiReviewContract
{
    /**
     * Perform an AI review on the given record.
     *
     * @param Model $record
     * @return array The review results
     */
    public function review(Model $record): array;
}
