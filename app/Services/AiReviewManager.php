<?php

namespace App\Services;

use App\Contracts\AiReviewContract;
use Illuminate\Support\Manager;

class AiReviewManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('services.ai_review.driver', 'gemini');
    }

    public function createGeminiDriver(): AiReviewContract
    {
        return new GeminiReviewService();
    }
    
    // In the future, you can add createOpenAiDriver() here.
}
