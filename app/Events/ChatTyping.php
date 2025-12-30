<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ChatTyping implements ShouldBroadcast
{
    use SerializesModels;

    public string $userName;
    public bool $typing;

    public function __construct(string $userName, bool $typing)
    {
        $this->userName = $userName;
        $this->typing = $typing;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('company-chat');
    }

    public function broadcastAs(): string
    {
        return 'chat.typing';
    }
}
