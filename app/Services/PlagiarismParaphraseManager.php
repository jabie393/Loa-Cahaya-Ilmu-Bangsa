<?php

namespace App\Services;

use App\Contracts\PlagiarismParaphraseContract;
use Illuminate\Support\Manager;

class PlagiarismParaphraseManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('services.plagiarism_paraphrase.driver', 'gemini');
    }

    public function createGeminiDriver(): PlagiarismParaphraseContract
    {
        return new GeminiPlagiarismParaphraseService();
    }
}
