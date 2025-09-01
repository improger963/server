<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        
        // Use database transaction for atomic operations
        try {
            DB::beginTransaction();
            
            // Deduct from user balance
            if (!$campaign->user->deductBalance($amount)) {
                throw new \Exception('Failed to deduct balance from user');
            }
            
            // Add to campaign budget
            $campaign->budget += $amount;
            $campaign->save();
            
            // Log the transaction
            TransactionLog::create([
                'user_id' => $campaign->user_id,
                'amount' => $amount,
                'type' => 'budget_allocation',
                'reference' => 'CAM_' . $campaign->id,
                'status' => 'completed',
                'description' => 'Budget allocated to campaign #' . $campaign->id,
            ]);
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Campaign budget allocation error: ' . $e->getMessage());
            return false;
        }
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
        
        // Use database transaction for atomic operations
        try {
            DB::beginTransaction();
            
            // Add unused budget back to user balance
            $campaign->user->addBalance($unusedBudget);
            
            // Reset campaign budget
            $campaign->budget = $campaign->spent;
            $campaign->save();
            
            // Log the transaction
            TransactionLog::create([
                'user_id' => $campaign->user_id,
                'amount' => $unusedBudget,
                'type' => 'budget_return',
                'reference' => 'CAM_' . $campaign->id,
                'status' => 'completed',
                'description' => 'Unused budget returned from campaign #' . $campaign->id,
            ]);
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Campaign budget release error: ' . $e->getMessage());
            return false;
        }
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

    /**
     * Check campaign budget in real-time
     *
     * @param Campaign $campaign
     * @param float $amount
     * @return bool
     */
    public function checkBudget(Campaign $campaign, $amount = 0.00)
    {
        return $campaign->hasBudget($amount);
    }

    /**
     * Deactivate expired campaigns
     *
     * @return int Number of deactivated campaigns
     */
    public function deactivateExpired()
    {
        // Get campaigns that are either past end date or have exhausted budget
        $expiredCampaigns = Campaign::where('is_active', true)
            ->where(function ($query) {
                // Campaigns past end date
                $query->where('end_date', '<', now())
                    // OR campaigns with exhausted budget
                    ->orWhere('spent', '>=', DB::raw('budget'));
            })
            ->get();

        $deactivatedCount = 0;

        foreach ($expiredCampaigns as $campaign) {
            try {
                DB::beginTransaction();

                // Deactivate campaign
                $campaign->is_active = false;
                $campaign->save();

                // Return unused budget
                $this->releaseBudget($campaign);

                DB::commit();
                $deactivatedCount++;
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Campaign deactivation error for campaign #' . $campaign->id . ': ' . $e->getMessage());
            }
        }

        return $deactivatedCount;
    }

    /**
     * Calculate daily spend for a campaign
     *
     * @param Campaign $campaign
     * @return float
     */
    public function calculateDailySpend(Campaign $campaign)
    {
        // This is a simplified calculation
        // In a real implementation, you might want to track daily spends more precisely
        $daysRunning = max(1, $campaign->start_date->diffInDays(now()));
        return $campaign->spent / $daysRunning;
    }

    /**
     * Check campaign status and return detailed information
     *
     * @param Campaign $campaign
     * @return array
     */
    public function checkCampaignStatus(Campaign $campaign)
    {
        return [
            'is_active' => $campaign->is_active,
            'is_running' => $campaign->isRunning(),
            'has_budget' => $campaign->hasBudget(),
            'is_expired' => $campaign->checkIfExpired(),
            'remaining_budget' => $campaign->getRemainingBudget(),
            'spent_percentage' => $campaign->getSpentPercentage(),
            'daily_spend' => $this->calculateDailySpend($campaign),
        ];
    }
}