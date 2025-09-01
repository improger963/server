<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BudgetMonitoringJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CampaignService $campaignService)
    {
        try {
            Log::info('BudgetMonitoringJob started');
            
            // Get active campaigns with low budget (less than 10% remaining)
            $lowBudgetCampaigns = Campaign::where('is_active', true)
                ->where('spent', '>', DB::raw('budget * 0.9'))
                ->get();

            $notifiedCount = 0;

            foreach ($lowBudgetCampaigns as $campaign) {
                // Check if campaign can still run
                if (!$campaignService->checkBudget($campaign)) {
                    // Deactivate campaign if budget is exhausted
                    $campaign->is_active = false;
                    $campaign->save();
                    
                    // Return unused budget
                    $campaignService->releaseBudget($campaign);
                    
                    Log::info('Campaign deactivated due to budget exhaustion', [
                        'campaign_id' => $campaign->id
                    ]);
                }
                
                $notifiedCount++;
            }
            
            Log::info('BudgetMonitoringJob completed', [
                'low_budget_campaigns' => $notifiedCount
            ]);
        } catch (\Exception $e) {
            Log::error('BudgetMonitoringJob failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }
}