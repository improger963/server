<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserLeft implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    public function broadcastOn()
    {
        return new PresenceChannel('chat');
    }
    
    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
        ];
    }
}