<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BalanceUpdated implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $user;
    public $newBalance;
    
    public function __construct(User $user, $newBalance)
    {
        $this->user = $user;
        $this->newBalance = $newBalance;
    }
    
    public function broadcastOn()
    {
        return new Channel('private-financial.' . $this->user->id);
    }
    
    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'balance' => $this->newBalance,
        ];
    }
}