<?php

namespace App\Services;

use App\Models\ChatbotFaq;

class ChatbotService
{
    private GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function processMessage(string $message, array $context = [], ?string $summary = null, array $history = []): array
    {
        // 1. Try to find answer in FAQ
        $faq = $this->searchFaq($message);

        if ($faq) {
            // Instead of bypassing AI, we give the FAQ as context to Gemini
            // This ensures Kanda Putra maintains its personality and handles context properly
            $context['Data dari FAQ Database'] = "Pertanyaan: {$faq->question}\nJawaban: {$faq->answer}";
            $source = 'faq';
        } else {
            $source = 'gemini';
        }

        // 2. Always generate response via Gemini to keep the "Kanda Putra" persona
        $geminiAnswer = $this->geminiService->generateResponse($message, $context, $summary, $history);

        return [
            'source' => $source,
            'answer' => $geminiAnswer
        ];
    }

    private function searchFaq(string $message): ?ChatbotFaq
    {
        $message = strtolower(trim($message));
        // Simple Stopwords removal for better matching
        $stopwords = ['apa', 'itu', 'bagaimana', 'cara', 'mengapa', 'kenapa', 'kapan', 'dimana', 'berapa', 'siapa', 'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'dengan', 'saya', 'aku', 'kami', 'kita'];
        
        $words = array_filter(explode(' ', $message), function($word) use ($stopwords) {
            return !in_array($word, $stopwords) && strlen($word) > 2;
        });

        if (empty($words)) {
            return null;
        }

        $query = ChatbotFaq::where('is_active', true);

        // Build keyword matching query
        $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->orWhere('question', 'LIKE', "%{$word}%")
                  ->orWhere('keywords', 'LIKE', "%{$word}%");
            }
        });

        // Get the most relevant FAQ
        return $query->first();
    }
}
