<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;

class CampaignService
{
    /**
     * Allocate budget from user balance to campaign
     *
     * @param Campaign $campaign
     * @param float $amount
     * @return bool
     */
    public function allocateBudget(Campaign $campaign, $amount)
    {
        // Check if user has enough balance
        if (!$campaign->user->hasBalance($amount)) {
            return false;
        }
        
        // Deduct from user balance
        if (!$campaign->user->deductBalance($amount)) {
            return false;
        }
        
        // Add to campaign budget
        $campaign->budget += $amount;
        return $campaign->save();
    }
    
    /**
     * Release unused budget back to user balance
     *
     * @param Campaign $campaign
     * @return bool
     */
    public function releaseBudget(Campaign $campaign)
    {
        $unusedBudget = $campaign->budget - $campaign->spent;
        
        if ($unusedBudget <= 0) {
            return true;
        }
        
        // Add unused budget back to user balance
        $campaign->user->addBalance($unusedBudget);
        
        // Reset campaign budget
        $campaign->budget = $campaign->spent;
        return $campaign->save();
    }
    
    /**
     * Check if campaign can be activated
     *
     * @param Campaign $campaign
     * @return bool
     */
    public function canActivate(Campaign $campaign)
    {
        return $campaign->isRunning() && $campaign->hasBudget();
    }
}