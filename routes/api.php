<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Site management routes
Route::middleware(['auth:sanctum', 'site.owner'])->group(function () {
    Route::apiResource('sites', App\Http\Controllers\Api\SiteController::class);
    Route::apiResource('sites.ad-slots', App\Http\Controllers\Api\AdSlotController::class);
});

// Campaign management routes
Route::middleware(['auth:sanctum', 'campaign.owner'])->group(function () {
    Route::apiResource('campaigns', App\Http\Controllers\Api\CampaignController::class);
    Route::apiResource('campaigns.creatives', App\Http\Controllers\Api\CreativeController::class);
});

// Financial routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/deposit', [App\Http\Controllers\Api\FinancialController::class, 'deposit']);
    Route::post('/withdraw', [App\Http\Controllers\Api\FinancialController::class, 'withdraw']);
});