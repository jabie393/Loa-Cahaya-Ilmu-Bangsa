<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DevFinancialUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $action = 'updated',
        public ?int $payoutId = null,
        public ?string $message = null
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('dev-financial'),
        ];

        if ($this->userId > 0) {
            $channels[] = new PrivateChannel('developer.' . $this->userId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'financial.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'action' => $this->action,
            'payout_id' => $this->payoutId,
            'message' => $this->message,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
