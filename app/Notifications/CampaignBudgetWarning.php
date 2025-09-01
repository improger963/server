<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Campaign;

class CampaignBudgetWarning extends Notification
{
    use Queueable;
    
    protected $campaign;
    
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }
    
    public function via($notifiable): array
    {
        return ['database'];
    }
    
    public function toArray($notifiable): array
    {
        return [
            'campaign_id' => $this->campaign->id,
            'campaign_name' => $this->campaign->name,
            'budget_used' => $this->campaign->spent,
            'budget_total' => $this->campaign->budget,
            'percentage_used' => $this->campaign->getSpentPercentage(),
        ];
    }
}