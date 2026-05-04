<?php

namespace App\Contracts;

use App\Models\PlagiarismCheck;

interface PlagiarismContract
{
    /**
     * Perform plagiarism check and return the results.
     */
    public function check(PlagiarismCheck $record): array;
}
