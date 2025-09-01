<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $perPage = $request->get('per_page', 20);
        $notifications = Auth::user()->notifications()->paginate($perPage);
        
        return response()->json($notifications);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }
        
        if ($notification->read_at === null) {
            $notification->markAsRead();
        }
        
        return response()->json([
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
}