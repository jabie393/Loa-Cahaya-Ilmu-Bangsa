<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\ChatbotSession;
use App\Models\ChatbotHistory;

#[Signature('chatbot:cleanup')]
#[Description('Clean up expired chatbot sessions (6h for guests, 24h for users)')]
class ChatbotCleanup extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up old chatbot sessions...');

        // 1. Cleanup Guest Sessions (user_id is null) older than 6 hours
        $guestExpiry = now()->subHours(6);
        $expiredGuestSessions = ChatbotSession::whereNull('user_id')
            ->where('last_activity', '<', $guestExpiry)
            ->pluck('id');

        if ($expiredGuestSessions->isNotEmpty()) {
            ChatbotHistory::whereIn('session_id', $expiredGuestSessions)->delete();
            ChatbotSession::whereIn('id', $expiredGuestSessions)->delete();
            $this->info('Deleted ' . $expiredGuestSessions->count() . ' expired guest sessions.');
        }

        // 2. Cleanup User Sessions (user_id is not null) older than 24 hours
        $userExpiry = now()->subHours(24);
        $expiredUserSessions = ChatbotSession::whereNotNull('user_id')
            ->where('last_activity', '<', $userExpiry)
            ->pluck('id');

        if ($expiredUserSessions->isNotEmpty()) {
            ChatbotHistory::whereIn('session_id', $expiredUserSessions)->delete();
            ChatbotSession::whereIn('id', $expiredUserSessions)->delete();
            $this->info('Deleted ' . $expiredUserSessions->count() . ' expired user sessions.');
        }

        $this->info('Cleanup completed.');
    }
}
