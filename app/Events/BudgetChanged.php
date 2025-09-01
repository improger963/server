<?php

namespace App\Events;

use App\Models\Campaign;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BudgetChanged implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $campaign;
    public $oldBudget;
    public $newBudget;
    
    public function __construct(Campaign $campaign, $oldBudget, $newBudget)
    {
        $this->campaign = $campaign;
        $this->oldBudget = $oldBudget;
        $this->newBudget = $newBudget;
    }
    
    public function broadcastOn()
    {
        return new Channel('private-campaign.' . $this->campaign->id);
    }
    
    public function broadcastWith()
    {
        return [
            'campaign' => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
            ],
            'old_budget' => $this->oldBudget,
            'new_budget' => $this->newBudget,
        ];
    }
}