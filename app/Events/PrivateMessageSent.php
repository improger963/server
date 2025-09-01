<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PrivateMessageSent implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $message;
    
    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }
    
    public function broadcastOn()
    {
        // Generate consistent channel name (lower ID first)
        $userId1 = $this->message->user_id;
        $userId2 = $this->message->recipient_id;
        
        $channelId = $userId1 < $userId2 ? 
            "private-chat.{$userId1}.{$userId2}" : 
            "private-chat.{$userId2}.{$userId1}";
            
        return new Channel($channelId);
    }
    
    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'user_id' => $this->message->user_id,
                'recipient_id' => $this->message->recipient_id,
                'message' => $this->message->message,
                'created_at' => $this->message->created_at,
            ],
        ];
    }
}