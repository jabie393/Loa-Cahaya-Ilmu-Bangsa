<?php

namespace App\Contracts;

use App\Models\PlagiarismParaphrase;

interface PlagiarismParaphraseContract
{
    /**
     * Perform professional academic paraphrase on plagiarized parts of a manuscript.
     */
    public function paraphrase(PlagiarismParaphrase $paraphraseRecord): array;
}
