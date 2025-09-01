<?php

namespace App\Events;

use App\Models\Campaign;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CampaignUpdated implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $campaign;
    
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
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
                'status' => $this->campaign->status,
                'budget' => $this->campaign->budget,
            ],
        ];
    }
}