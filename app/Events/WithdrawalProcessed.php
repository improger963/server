<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WithdrawalProcessed implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $user;
    public $amount;
    
    public function __construct(User $user, $amount)
    {
        $this->user = $user;
        $this->amount = $amount;
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
            'amount' => $this->amount,
        ];
    }
}