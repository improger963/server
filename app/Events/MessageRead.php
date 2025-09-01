<?php

namespace App\Events;

use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageRead implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $message;
    public $user;
    
    public function __construct(ChatMessage $message, User $user)
    {
        $this->message = $message;
        $this->user = $user;
    }
    
    public function broadcastOn()
    {
        return new Channel('private-user.' . $this->message->user_id);
    }
    
    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
        ];
    }
}