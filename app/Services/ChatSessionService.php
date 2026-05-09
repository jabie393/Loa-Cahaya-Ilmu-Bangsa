<?php

namespace App\Services;

use App\Models\ChatbotSession;
use App\Models\ChatbotHistory;

class ChatSessionService
{
    private GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function getOrCreateSession(string $sessionId, ?string $guestToken, ?int $userId): ChatbotSession
    {
        $session = ChatbotSession::find($sessionId);
        
        if (!$session) {
            $session = ChatbotSession::create([
                'id' => $sessionId,
                'guest_token' => $guestToken,
                'user_id' => $userId,
                'last_activity' => now(),
            ]);
        } else {
            $session->update(['last_activity' => now()]);
            
            if ($userId && !$session->user_id) {
                $session->update(['user_id' => $userId]);
                ChatbotHistory::where('session_id', $sessionId)->update(['user_id' => $userId]);
            }
        }

        return $session;
    }

    public function checkGuestLimit(?string $guestToken, ?int $userId): bool
    {
        if ($userId) return true; // Logged in users have no strict limit or rely on other systems
        if (!$guestToken) return false;

        $count = ChatbotHistory::where('guest_token', $guestToken)
                    ->where('role', 'user')
                    ->count();
                    
        return $count < 10;
    }

    public function handleSummarization(ChatbotSession $session)
    {
        $histories = $session->histories()->orderBy('created_at', 'asc')->get();
        
        // 15 is the limit requested by user
        if ($histories->count() > 15) {
            $chatLog = "";
            foreach ($histories as $h) {
                $role = $h->role === 'user' ? 'User' : 'Kanda Putra (AI)';
                $chatLog .= "{$role}: {$h->message}\n";
            }
            
            $prompt = "Buatlah ringkasan singkat dari percakapan berikut untuk dijadikan memory context AI. Fokus pada topik utama, status pengajuan, dan hal penting yang sedang dibahas. Jangan bertele-tele. Percakapan:\n" . $chatLog;
            
            $summary = $this->geminiService->generateDirectResponse($prompt);
            
            if ($summary) {
                $oldSummary = $session->summary ? "Ringkasan Sebelumnya: " . $session->summary . "\n" : "";
                $session->update(['summary' => $oldSummary . "Ringkasan Terbaru: " . $summary]);
                
                // Keep the last 4 messages (2 QA pairs)
                $keepIds = $histories->sortByDesc('created_at')->take(4)->pluck('id')->toArray();
                $session->histories()->whereNotIn('id', $keepIds)->delete();
            }
        }
    }
}
