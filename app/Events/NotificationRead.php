<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationRead implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $notificationId;
    public $user;
    
    public function __construct($notificationId, User $user)
    {
        $this->notificationId = $notificationId;
        $this->user = $user;
    }
    
    public function broadcastOn()
    {
        return new Channel('private-user.' . $this->user->id);
    }
    
    public function broadcastWith()
    {
        return [
            'notification_id' => $this->notificationId,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
        ];
    }
}