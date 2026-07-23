<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sessionId;

    public $sender;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($sessionId, $sender, $message)
    {
        $this->sessionId = $sessionId;
        $this->sender = $sender;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('chat.session.'.$this->sessionId);
    }

    public function broadcastAs()
    {
        return 'chat.message';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'sender' => $this->sender,
        ];
    }
}
