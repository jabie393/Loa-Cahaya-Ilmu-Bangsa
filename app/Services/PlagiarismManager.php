<?php

namespace App\Services;

use App\Contracts\PlagiarismContract;
use Illuminate\Support\Manager;

class PlagiarismManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('services.plagiarism.driver', 'gemini');
    }

    public function createGeminiDriver(): PlagiarismContract
    {
        return new GeminiPlagiarismService();
    }
}
