<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Creative;
use App\Services\ValidationService;
use Illuminate\Http\Request;

class CreativeController extends Controller
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $campaignId = $request->route('campaign');
        $creatives = Creative::where('campaign_id', $campaignId)->get();
        
        return response()->json($creatives);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $campaignId = $request->route('campaign');
        
        // Validate input
        $errors = $this->validationService->validateCreative($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        // Create creative
        $data = $request->all();
        $data['campaign_id'] = $campaignId;
        $creative = Creative::create($data);
        
        return response()->json($creative, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Creative $creative)
    {
        return response()->json($creative);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Creative $creative)
    {
        // Validate input
        $errors = $this->validationService->validateCreative($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        // Update creative
        $creative->update($request->all());
        
        return response()->json($creative);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Creative $creative)
    {
        $creative->delete();
        
        return response()->json(null, 204);
    }
}