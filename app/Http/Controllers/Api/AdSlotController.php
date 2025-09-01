<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdSlot;
use App\Services\AdSlotService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdSlotController extends Controller
{
    protected $adSlotService;
    protected $validationService;

    public function __construct(AdSlotService $adSlotService, ValidationService $validationService)
    {
        $this->adSlotService = $adSlotService;
        $this->validationService = $validationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $siteId = $request->route('site');
        $adSlots = AdSlot::where('site_id', $siteId)->get();
        
        return response()->json($adSlots);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $siteId = $request->route('site');
        
        // Validate input
        $errors = $this->validationService->validateAdSlot($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        // Create ad slot
        $data = $request->all();
        $data['site_id'] = $siteId;
        $adSlot = AdSlot::create($data);
        
        return response()->json($adSlot, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AdSlot $adSlot)
    {
        return response()->json($adSlot);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdSlot $adSlot)
    {
        // Validate input
        $errors = $this->validationService->validateAdSlot($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        // Update ad slot
        $adSlot->update($request->all());
        
        return response()->json($adSlot);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdSlot $adSlot)
    {
        $adSlot->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Associate a campaign with an ad slot.
     */
    public function associateCampaign(Request $request, AdSlot $adSlot)
    {
        $campaignId = $request->input('campaign_id');
        $campaign = \App\Models\Campaign::find($campaignId);
        
        if (!$campaign) {
            return response()->json(['error' => 'Campaign not found'], 404);
        }
        
        $result = $this->adSlotService->associateCampaign($adSlot, $campaign);
        
        if ($result) {
            return response()->json(['message' => 'Campaign associated successfully']);
        }
        
        return response()->json(['error' => 'Failed to associate campaign'], 500);
    }

    /**
     * Dissociate a campaign from an ad slot.
     */
    public function dissociateCampaign(Request $request, AdSlot $adSlot)
    {
        $campaignId = $request->input('campaign_id');
        $campaign = \App\Models\Campaign::find($campaignId);
        
        if (!$campaign) {
            return response()->json(['error' => 'Campaign not found'], 404);
        }
        
        $result = $this->adSlotService->dissociateCampaign($adSlot, $campaign);
        
        if ($result) {
            return response()->json(['message' => 'Campaign dissociated successfully']);
        }
        
        return response()->json(['error' => 'Failed to dissociate campaign'], 500);
    }

    /**
     * Process an ad request for an ad slot.
     */
    public function requestAd(AdSlot $adSlot)
    {
        // Process ad request
        $result = $this->adSlotService->processAdRequest($adSlot);
        
        if ($result['success']) {
            return response()->json([
                'creative' => $result['creative'],
                'campaign_id' => $result['campaign_id']
            ]);
        }
        
        // Return appropriate error response
        if ($result['error'] === 'Ad slot is not active') {
            return response()->json(['error' => $result['error']], 410); // Gone
        }
        
        return response()->json(['error' => $result['error']], 404);
    }
}