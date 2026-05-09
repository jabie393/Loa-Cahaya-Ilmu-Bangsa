<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFaq;
use App\Models\ChatbotHistory;
use App\Models\ChatbotSession;
use App\Services\ChatbotService;
use App\Services\ChatSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    protected ChatbotService $chatbotService;
    protected ChatSessionService $sessionService;

    public function __construct(ChatbotService $chatbotService, ChatSessionService $sessionService)
    {
        $this->chatbotService = $chatbotService;
        $this->sessionService = $sessionService;
    }

    public function getFaqs()
    {
        $faqs = ChatbotFaq::where('is_active', true)
            ->where('is_popular', true)
            ->select('id', 'question', 'answer')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $faqs
        ]);
    }
    
    public function getSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);
        
        $session = ChatbotSession::with(['histories' => function($q) {
            $q->orderBy('created_at', 'asc');
        }])->find($request->session_id);
        
        if (!$session) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $session->histories
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required|string',
            'guest_token' => 'nullable|string',
            'context' => 'nullable|array'
        ]);

        $message = $request->input('message');
        $sessionId = $request->input('session_id');
        $guestToken = $request->input('guest_token');
        $context = $request->input('context', []);

        $user = auth()->user();
        if ($user) {
            $context['User Role'] = $user->getRoleNames()->first() ?? 'User';
            $context['User Name'] = $user->name;
        } else {
            $context['User Role'] = 'Guest';
        }

        if (!$this->sessionService->checkGuestLimit($guestToken, $user?->id)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Anda telah mencapai batas pertanyaan sebagai Guest (10 pertanyaan). Silakan login untuk melanjutkan percakapan dengan Kanda Putra 👋',
                    'source' => 'system'
                ]
            ]);
        }

        $session = $this->sessionService->getOrCreateSession($sessionId, $guestToken, $user?->id);

        // Fetch past history and summary before adding the new message
        // so we don't pass the new message twice (once in history, once in userMessage)
        $historyData = $session->histories()->orderBy('created_at', 'asc')->get()->toArray();
        $summary = $session->summary;

        // Save new user message
        ChatbotHistory::create([
            'session_id' => $session->id,
            'user_id' => $user?->id,
            'guest_token' => $guestToken,
            'message' => $message,
            'role' => 'user',
        ]);
        
        // Process via Service
        $response = $this->chatbotService->processMessage($message, $context, $summary, $historyData);

        // Save bot response
        ChatbotHistory::create([
            'session_id' => $session->id,
            'user_id' => $user?->id,
            'guest_token' => $guestToken,
            'message' => $response['answer'],
            'role' => 'bot',
        ]);
        
        $this->sessionService->handleSummarization($session);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => $response['answer'],
                'source' => $response['source']
            ]
        ]);
    }
}
