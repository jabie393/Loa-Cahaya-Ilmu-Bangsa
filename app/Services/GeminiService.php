<?php

namespace App\Services;

use App\Traits\HandlesGeminiFallback;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    use HandlesGeminiFallback;

    private KnowledgeLoaderService $knowledgeLoader;

    public function __construct(KnowledgeLoaderService $knowledgeLoader)
    {
        $this->knowledgeLoader = $knowledgeLoader;
    }

    public function generateDirectResponse(string $prompt): string
    {
        $apiKey = config('services.gemini.chatbot_key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        if (!$apiKey) return "";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
        ];

        try {
            $response = $this->callGemini($apiKey, $model, $payload);
            
            $data = $response->json();
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            }
        } catch (\Exception $e) {
            Log::error('Gemini Service Direct Response Exception', ['message' => $e->getMessage()]);
        }

        return "";
    }

    public function generateResponse(string $userMessage, array $userContext = [], ?string $summary = null, array $history = []): string
    {
        $apiKey = config('services.gemini.chatbot_key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

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
        
        // 4. Add Summary Memory
        if (!empty($summary)) {
            $systemPrompt .= "\n\nMEMORY PERCAKAPAN SEBELUMNYA:\n{$summary}\n";
        }

        $contents = [];

        // 5. Build History (format for Gemini contents)
        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [
                    ['text' => $msg['message']]
                ]
            ];
        }

        // Add the current user message
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $userMessage]
            ]
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ]
        ];

        try {
            $response = $this->callGemini($apiKey, $model, $payload);
            
            $data = $response->json();
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            }
        } catch (\Exception $e) {
            Log::error('Gemini Service Response Exception', ['message' => $e->getMessage()]);
        }

        return "Maaf, saya sedang kesulitan memahami permintaan Anda. Silakan coba beberapa saat lagi atau hubungi admin.";
    }
}
