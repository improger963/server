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

// Authentication routes
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/forgot-password', [App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [App\Http\Controllers\Api\AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    // Site management routes
    Route::apiResource('sites', App\Http\Controllers\Api\SiteController::class);
    Route::apiResource('sites.ad-slots', App\Http\Controllers\Api\AdSlotController::class);
    
    // Campaign management routes
    Route::apiResource('campaigns', App\Http\Controllers\Api\CampaignController::class);
    Route::apiResource('campaigns.creatives', App\Http\Controllers\Api\CreativeController::class);
    
    // Additional campaign routes
    Route::post('/campaigns/{campaign}/allocate-budget', [App\Http\Controllers\Api\CampaignController::class, 'allocateBudget']);
    Route::post('/campaigns/{campaign}/activate', [App\Http\Controllers\Api\CampaignController::class, 'activate']);
    Route::post('/campaigns/{campaign}/deactivate', [App\Http\Controllers\Api\CampaignController::class, 'deactivate']);
    
    // Financial routes
    Route::post('/deposit', [App\Http\Controllers\Api\FinancialController::class, 'deposit']);
    Route::post('/withdraw', [App\Http\Controllers\Api\FinancialController::class, 'withdraw']);
    Route::get('/profile/referral-stats', [App\Http\Controllers\Api\AuthController::class, 'referralStats']);
    
    // Notification routes
    Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    
    // Stats routes
    Route::get('/stats/dashboard', [App\Http\Controllers\Api\StatsController::class, 'dashboard']);
    
    // Chat routes
    Route::get('/chat/messages', [App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::post('/chat/messages', [App\Http\Controllers\Api\ChatController::class, 'store']);
    Route::post('/chat/messages/private', [App\Http\Controllers\Api\ChatController::class, 'sendPrivateMessage']);
    Route::post('/chat/typing', [App\Http\Controllers\Api\ChatController::class, 'typing']);
    Route::post('/chat/messages/{id}/read', [App\Http\Controllers\Api\ChatController::class, 'markAsRead']);
    Route::get('/chat/users/online', [App\Http\Controllers\Api\ChatController::class, 'getOnlineUsers']);
    
    // News routes
    Route::get('/news', [App\Http\Controllers\Api\NewsController::class, 'index']);
    

    // Ticket routes
    Route::apiResource('tickets', App\Http\Controllers\Api\TicketController::class);
    Route::post('/tickets/{ticket}/reply', [App\Http\Controllers\Api\TicketController::class, 'reply']);
    Route::post('/tickets/{ticket}/status', [App\Http\Controllers\Api\TicketController::class, 'updateStatus']);
    Route::post('/tickets/{ticket}/assign', [App\Http\Controllers\Api\TicketController::class, 'assign']);
    
    // Admin news routes
    Route::prefix('admin')->group(function () {
        Route::apiResource('news', App\Http\Controllers\Api\Admin\NewsController::class);
    });
});

// Payeer webhook endpoint (no authentication required)
Route::post('/deposit/payeer-webhook', [App\Http\Controllers\Api\FinancialController::class, 'payeerWebhook']);

// Public ad request endpoint (no authentication required)
Route::get('/ad-slots/{adSlot}/request', [App\Http\Controllers\Api\AdSlotController::class, 'requestAd']);