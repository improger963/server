<?php

namespace App\Services;

use App\Models\AdSlot;
use App\Models\Campaign;

class AdSlotService
{
    /**
     * Get active creatives for an ad slot
     *
     * @param AdSlot $adSlot
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveCreatives(AdSlot $adSlot)
    {
        return $adSlot->campaigns()
            ->active()
            ->running()
            ->whereHas('creatives', function ($query) {
                $query->active();
            })
            ->with('creatives')
            ->get()
            ->pluck('creatives')
            ->flatten()
            ->filter(function ($creative) {
                return $creative->is_active;
            });
    }
    
    /**
     * Check if ad slot can display ads
     *
     * @param AdSlot $adSlot
     * @return bool
     */
    public function canDisplayAds(AdSlot $adSlot)
    {
        return $adSlot->is_active && $adSlot->site->is_active;
    }
    
    /**
     * Associate a campaign with an ad slot
     *
     * @param AdSlot $adSlot
     * @param Campaign $campaign
     * @return bool
     */
    public function associateCampaign(AdSlot $adSlot, Campaign $campaign)
    {
        // Check if already associated
        if ($adSlot->hasCampaign($campaign)) {
            return true;
        }
        
        // Associate campaign with ad slot
        return $adSlot->campaigns()->attach($campaign->id);
    }
    
    /**
     * Dissociate a campaign from an ad slot
     *
     * @param AdSlot $adSlot
     * @param Campaign $campaign
     * @return bool
     */
    public function dissociateCampaign(AdSlot $adSlot, Campaign $campaign)
    {
        // Check if associated
        if (!$adSlot->hasCampaign($campaign)) {
            return true;
        }
        
        // Dissociate campaign from ad slot
        return $adSlot->campaigns()->detach($campaign->id);
    }
}