<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\CampaignService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    protected $campaignService;
    protected $validationService;

    public function __construct(CampaignService $campaignService, ValidationService $validationService)
    {
        $this->campaignService = $campaignService;
        $this->validationService = $validationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $campaigns = $user->campaigns;
        
        return response()->json($campaigns);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $errors = $this->validationService->validateCampaign($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        // Create campaign
        $user = Auth::user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $campaign = Campaign::create($data);
        
        return response()->json($campaign, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        return response()->json($campaign);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campaign $campaign)
    {
        // Validate input
        $errors = $this->validationService->validateCampaign($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        // Update campaign
        $campaign->update($request->all());
        
        return response()->json($campaign);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        // Release any remaining budget
        $this->campaignService->releaseBudget($campaign);
        
        $campaign->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Allocate budget to campaign.
     */
    public function allocateBudget(Request $request, Campaign $campaign)
    {
        $amount = $request->input('amount');
        
        if (!is_numeric($amount) || $amount <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }
        
        $result = $this->campaignService->allocateBudget($campaign, $amount);
        
        if ($result) {
            return response()->json(['message' => 'Budget allocated successfully', 'campaign' => $campaign]);
        }
        
        return response()->json(['error' => 'Insufficient funds'], 400);
    }

    /**
     * Activate campaign.
     */
    public function activate(Campaign $campaign)
    {
        if ($this->campaignService->canActivate($campaign)) {
            $campaign->is_active = true;
            $campaign->save();
            
            return response()->json(['message' => 'Campaign activated successfully', 'campaign' => $campaign]);
        }
        
        return response()->json(['error' => 'Cannot activate campaign. Check budget and dates.'], 400);
    }
}