<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\CampaignService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
     * Store a newly created resource in storage with enhanced validation.
     */
    public function store(Request $request)
    {
        // Validate input using Laravel validator
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Additional validation using ValidationService
        $errors = $this->validationService->validateCampaign($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        try {
            // Create campaign
            $user = Auth::user();
            $data = $request->all();
            $data['user_id'] = $user->id;
            $campaign = Campaign::create($data);
            
            return response()->json($campaign, 201);
        } catch (\Exception $e) {
            \Log::error('Campaign creation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $request->all(),
                'exception' => $e
            ]);
            
            return response()->json(['error' => 'Campaign creation failed'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        return response()->json($campaign);
    }

    /**
     * Update the specified resource in storage with enhanced validation.
     */
    public function update(Request $request, Campaign $campaign)
    {
        // Validate input using Laravel validator
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'budget' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Additional validation using ValidationService
        $errors = $this->validationService->validateCampaign($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        try {
            // Update campaign
            $campaign->update($request->all());
            
            return response()->json($campaign);
        } catch (\Exception $e) {
            \Log::error('Campaign update failed: ' . $e->getMessage(), [
                'campaign_id' => $campaign->id,
                'data' => $request->all(),
                'exception' => $e
            ]);
            
            return response()->json(['error' => 'Campaign update failed'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        try {
            // Release any remaining budget
            $this->campaignService->releaseBudget($campaign);
            
            $campaign->delete();
            
            return response()->json(null, 204);
        } catch (\Exception $e) {
            \Log::error('Campaign deletion failed: ' . $e->getMessage(), [
                'campaign_id' => $campaign->id,
                'exception' => $e
            ]);
            
            return response()->json(['error' => 'Campaign deletion failed'], 500);
        }
    }

    /**
     * Allocate budget to campaign with enhanced validation.
     */
    public function allocateBudget(Request $request, Campaign $campaign)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $amount = $request->input('amount');
        
        try {
            $result = $this->campaignService->allocateBudget($campaign, $amount);
            
            if ($result) {
                return response()->json([
                    'message' => 'Budget allocated successfully', 
                    'campaign' => $campaign,
                    'allocated_amount' => $amount
                ]);
            }
            
            return response()->json(['error' => 'Insufficient funds'], 400);
        } catch (\Exception $e) {
            \Log::error('Budget allocation failed: ' . $e->getMessage(), [
                'campaign_id' => $campaign->id,
                'amount' => $amount,
                'exception' => $e
            ]);
            
            return response()->json(['error' => 'Budget allocation failed'], 500);
        }
    }

    /**
     * Activate campaign with enhanced validation.
     */
    public function activate(Campaign $campaign)
    {
        try {
            if ($this->campaignService->canActivate($campaign)) {
                $campaign->is_active = true;
                $campaign->save();
                
                return response()->json([
                    'message' => 'Campaign activated successfully', 
                    'campaign' => $campaign,
                    'is_active' => true
                ]);
            }
            
            return response()->json(['error' => 'Cannot activate campaign. Check budget and dates.'], 400);
        } catch (\Exception $e) {
            \Log::error('Campaign activation failed: ' . $e->getMessage(), [
                'campaign_id' => $campaign->id,
                'exception' => $e
            ]);
            
            return response()->json(['error' => 'Campaign activation failed'], 500);
        }
    }

    /**
     * Deactivate campaign.
     */
    public function deactivate(Campaign $campaign)
    {
        try {
            $campaign->is_active = false;
            $campaign->save();
            
            // Release unused budget
            $this->campaignService->releaseBudget($campaign);
            
            return response()->json([
                'message' => 'Campaign deactivated successfully', 
                'campaign' => $campaign,
                'is_active' => false
            ]);
        } catch (\Exception $e) {
            \Log::error('Campaign deactivation failed: ' . $e->getMessage(), [
                'campaign_id' => $campaign->id,
                'exception' => $e
            ]);
            
            return response()->json(['error' => 'Campaign deactivation failed'], 500);
        }
    }
}