<?php

namespace App\Services;

use App\Models\AdSlot;
use App\Models\Campaign;
use App\Models\Creative;
use App\Models\TransactionLog;
use App\Models\AnalyticsEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     * Process an ad request for an ad slot
     *
     * @param AdSlot $adSlot
     * @return array
     */
    public function processAdRequest(AdSlot $adSlot)
    {
        // Check if ad slot can display ads
        if (!$this->canDisplayAds($adSlot)) {
            return [
                'success' => false,
                'error' => 'Ad slot is not active'
            ];
        }

        // Get active campaigns for this ad slot
        $campaigns = $adSlot->campaigns()
            ->active()
            ->running()
            ->where('spent', '<', DB::raw('budget'))
            ->get();

        if ($campaigns->isEmpty()) {
            return [
                'success' => false,
                'error' => 'No active campaigns available'
            ];
        }

        // Filter campaigns by compatible ad format
        $compatibleCampaigns = $campaigns->filter(function ($campaign) use ($adSlot) {
            // Get active creatives for this campaign
            $creatives = $campaign->creatives()->active()->get();
            
            // Check if any creative is compatible with the ad slot
            return $creatives->contains(function ($creative) use ($adSlot) {
                return $this->isCreativeCompatibleWithAdSlot($creative, $adSlot);
            });
        });

        if ($compatibleCampaigns->isEmpty()) {
            return [
                'success' => false,
                'error' => 'No campaigns with compatible ad formats available'
            ];
        }

        // Select a campaign (simple random selection for now)
        $campaign = $this->selectCampaign($compatibleCampaigns);

        if (!$campaign) {
            return [
                'success' => false,
                'error' => 'No valid campaign found'
            ];
        }

        // Select a creative from the campaign that is compatible with the ad slot
        $creative = $this->selectCompatibleCreative($campaign, $adSlot);

        if (!$creative) {
            return [
                'success' => false,
                'error' => 'No valid creative found'
            ];
        }

        // Charge impression fee
        $chargeResult = $this->chargeImpression($campaign, $adSlot->price_per_impression);

        if (!$chargeResult['success']) {
            return [
                'success' => false,
                'error' => $chargeResult['error']
            ];
        }

        // Log the ad request
        TransactionLog::create([
            'user_id' => $campaign->user_id,
            'amount' => $adSlot->price_per_impression,
            'type' => 'impression_charge',
            'reference' => 'AD_' . $adSlot->id . '_CAM_' . $campaign->id,
            'status' => 'completed',
            'description' => 'Impression charge for ad slot #' . $adSlot->id . ' and campaign #' . $campaign->id,
        ]);

        // Track impression analytics
        $this->trackImpression($campaign->user_id, $campaign->id, $adSlot->price_per_impression);

        return [
            'success' => true,
            'creative' => $creative,
            'campaign_id' => $campaign->id
        ];
    }

    /**
     * Check if a creative is compatible with an ad slot
     *
     * @param Creative $creative
     * @param AdSlot $adSlot
     * @return bool
     */
    public function isCreativeCompatibleWithAdSlot(Creative $creative, AdSlot $adSlot)
    {
        // Check if creative type matches ad slot type
        if ($creative->type !== $adSlot->type) {
            return false;
        }

        // For banner ads, check if dimensions match
        if ($creative->type === 'banner' && $adSlot->type === 'banner') {
            // If ad slot has dimensions specified, check if they match creative dimensions
            if (!empty($adSlot->dimensions)) {
                // For now, we'll assume the creative dimensions are stored in the content
                // In a real implementation, you might want to store dimensions separately
                $creativeDimensions = $this->getCreativeDimensions($creative);
                $slotDimensions = $adSlot->dimensions;
                
                if (isset($creativeDimensions['width']) && isset($creativeDimensions['height'])) {
                    if ($creativeDimensions['width'] != $slotDimensions['width'] || 
                        $creativeDimensions['height'] != $slotDimensions['height']) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get dimensions from creative content
     *
     * @param Creative $creative
     * @return array
     */
    private function getCreativeDimensions(Creative $creative)
    {
        // This is a simplified implementation
        // In a real system, you might extract dimensions from the image URL
        // or have a separate dimensions field in the creative model
        if (isset($creative->content['dimensions'])) {
            return $creative->content['dimensions'];
        }
        
        return [];
    }

    /**
     * Select a campaign from a collection of campaigns
     *
     * @param \Illuminate\Database\Eloquent\Collection $campaigns
     * @return Campaign|null
     */
    public function selectCampaign($campaigns)
    {
        if ($campaigns->isEmpty()) {
            return null;
        }

        // Simple random selection for now
        // In a real implementation, you might want to implement weighted selection
        // based on budget, bid price, or other factors
        return $campaigns->random();
    }

    /**
     * Select a creative from a campaign that is compatible with the ad slot
     *
     * @param Campaign $campaign
     * @param AdSlot $adSlot
     * @return \App\Models\Creative|null
     */
    public function selectCompatibleCreative(Campaign $campaign, AdSlot $adSlot)
    {
        $creatives = $campaign->creatives()->active()->get();

        // Filter creatives by compatibility with ad slot
        $compatibleCreatives = $creatives->filter(function ($creative) use ($adSlot) {
            return $this->isCreativeCompatibleWithAdSlot($creative, $adSlot);
        });

        if ($compatibleCreatives->isEmpty()) {
            return null;
        }

        // Simple random selection
        return $compatibleCreatives->random();
    }

    /**
     * Charge impression fee from campaign budget
     *
     * @param Campaign $campaign
     * @param float $amount
     * @return array
     */
    public function chargeImpression(Campaign $campaign, $amount)
    {
        // Check if campaign has enough budget
        if (!$campaign->hasBudget($amount)) {
            return [
                'success' => false,
                'error' => 'Insufficient campaign budget'
            ];
        }

        // Use database transaction for atomic operations
        try {
            DB::beginTransaction();

            // Deduct amount from campaign budget
            if (!$campaign->deductBudget($amount)) {
                throw new \Exception('Failed to deduct budget from campaign');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Impression charged successfully'
            ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Impression charge error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Impression charge failed'
            ];
        }
    }

    /**
     * Track impression analytics event
     *
     * @param int $userId
     * @param int $campaignId
     * @param float $cost
     * @return void
     */
    public function trackImpression($userId, $campaignId, $cost)
    {
        AnalyticsEvent::create([
            'user_id' => $userId,
            'type' => 'impression',
            'related_id' => $campaignId,
            'related_type' => 'campaign',
            'cost' => $cost,
        ]);
    }

    /**
     * Track click analytics event
     *
     * @param int $userId
     * @param int $campaignId
     * @param float $cost
     * @return void
     */
    public function trackClick($userId, $campaignId, $cost)
    {
        AnalyticsEvent::create([
            'user_id' => $userId,
            'type' => 'click',
            'related_id' => $campaignId,
            'related_type' => 'campaign',
            'cost' => $cost,
        ]);
    }
}