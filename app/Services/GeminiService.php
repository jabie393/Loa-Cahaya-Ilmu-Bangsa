<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private KnowledgeLoaderService $knowledgeLoader;

    public function __construct(KnowledgeLoaderService $knowledgeLoader)
    {
        $this->knowledgeLoader = $knowledgeLoader;
    }

    public function generateResponse(string $userMessage, array $userContext = []): string
    {
        $apiKey = env('GEMINI_API_KEY');
        $model = env('GEMINI_MODEL', 'gemini-1.5-flash');

        if (!$apiKey) {
            Log::error('Gemini API Key is missing.');
            return "Maaf, saat ini saya sedang mengalami gangguan sistem (API Key missing). Silakan hubungi admin via WhatsApp.";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // 1. Build Final System Prompt
        $systemPrompt = $this->knowledgeLoader->loadSystemPrompt();
        
        // 2. Add Knowledge Base Context
        $knowledge = $this->knowledgeLoader->loadKnowledgeBase();
        if (!empty($knowledge)) {
            $systemPrompt .= "\n\nKNOWLEDGE BASE WEBSITE:\n" . $knowledge;
        }

        // 3. Add User Context
        $systemPrompt .= "\n\nKONTEKS USER SAAT INI:\n";
        foreach ($userContext as $key => $value) {
            $systemPrompt .= "- {$key}: {$value}\n";
        }

        try {
            $response = Http::post($url, [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemPrompt]
                    ]
                ],
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $userMessage]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 800,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }
            }

            Log::error('Gemini API Error', ['response' => $response->body()]);
            return "Maaf, saya sedang kesulitan memahami permintaan Anda. Silakan coba beberapa saat lagi atau hubungi admin.";

        } catch (\Exception $e) {
            Log::error('Gemini API Exception', ['message' => $e->getMessage()]);
            return "Terjadi kesalahan pada sistem kami. Silakan hubungi admin.";
        }
    }
}
