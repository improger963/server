<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $sites = $user->sites;
        
        return response()->json($sites);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $errors = $this->validationService->validateSite($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        // Create site
        $user = Auth::user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $site = Site::create($data);
        
        return response()->json($site, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        return response()->json($site);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site)
    {
        // Validate input
        $errors = $this->validationService->validateSite($request->all());
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        // Update site
        $site->update($request->all());
        
        return response()->json($site);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site)
    {
        $site->delete();
        
        return response()->json(null, 204);
    }
}