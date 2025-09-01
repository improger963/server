<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $message;
    
    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }
    
    public function broadcastOn()
    {
        return new Channel('chat');
    }
}