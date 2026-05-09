<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFaq;
use App\Models\ChatbotHistory;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
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

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required|string',
            'context' => 'nullable|array'
        ]);

        $message = $request->input('message');
        $sessionId = $request->input('session_id');
        $context = $request->input('context', []);

        $user = auth()->user();
        if ($user) {
            $context['User Role'] = $user->getRoleNames()->first() ?? 'User';
            $context['User Name'] = $user->name;
        } else {
            $context['User Role'] = 'Guest';
        }

        // Save User Message
        ChatbotHistory::create([
            'session_id' => $sessionId,
            'user_id' => $user?->id,
            'message' => $message,
            'is_user' => true,
        ]);

        // Process via Service
        $response = $this->chatbotService->processMessage($message, $context);

        // Save Bot Message
        ChatbotHistory::create([
            'session_id' => $sessionId,
            'user_id' => $user?->id,
            'message' => $response['answer'],
            'is_user' => false,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => $response['answer'],
                'source' => $response['source']
            ]
        ]);
    }
}
