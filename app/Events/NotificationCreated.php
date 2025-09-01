<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationCreated implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $notification;
    public $user;
    
    public function __construct($notification, User $user)
    {
        $this->notification = $notification;
        $this->user = $user;
    }
    
    public function broadcastOn()
    {
        return new Channel('private-user.' . $this->user->id);
    }
    
    public function broadcastWith()
    {
        return [
            'notification' => $this->notification,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
        ];
    }
}