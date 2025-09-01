<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatMessage;
use App\Models\UserPresence;
use App\Events\UserTyping;
use App\Events\MessageRead;
use App\Events\PrivateMessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $messages = ChatMessage::with('user')
            ->recent()
            ->get()
            ->sortBy('created_at');
            
        return response()->json($messages);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        
        $message = ChatMessage::create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);
        
        // Load the user relationship
        $message->load('user');
        
        // Broadcast the message
        broadcast(new \App\Events\MessageSent($message))->toOthers();
        
        return response()->json($message, 201);
    }
    
    public function sendPrivateMessage(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);
        
        $message = ChatMessage::create([
            'user_id' => auth()->id(),
            'recipient_id' => $validated['recipient_id'],
            'message' => $validated['message'],
        ]);
        
        // Load the user relationship
        $message->load('user');
        
        // Broadcast the private message
        broadcast(new PrivateMessageSent($message))->toOthers();
        
        return response()->json($message, 201);
    }
    
    public function typing(Request $request)
    {
        // Broadcast typing event
        broadcast(new UserTyping(auth()->user()))->toOthers();
        
        return response()->json(['status' => 'typing']);
    }
    
    public function markAsRead(Request $request, $messageId)
    {
        $message = ChatMessage::findOrFail($messageId);
        
        // Only the recipient can mark a message as read
        if ($message->recipient_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $message->update(['is_read' => true]);
        
        // Broadcast read event
        broadcast(new MessageRead($message, auth()->user()))->toOthers();
        
        return response()->json(['status' => 'read']);
    }
    
    public function getOnlineUsers(Request $request)
    {
        $onlineUsers = UserPresence::with('user')
            ->online()
            ->get()
            ->map(function ($presence) {
                return $presence->user;
            });
            
        return response()->json($onlineUsers);
    }
}