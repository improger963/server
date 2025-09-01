<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'recipient_id',
        'message',
        'is_read',
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
    
    // Scope for getting recent messages
    public function scopeRecent($query, $limit = 50)
    {
        return $query->latest()->limit($limit);
    }
    
    // Scope for getting unread messages
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    // Scope for getting private messages between two users
    public function scopeBetweenUsers($query, $userId1, $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('user_id', $userId1)
              ->where('recipient_id', $userId2);
        })->orWhere(function ($q) use ($userId1, $userId2) {
            $q->where('user_id', $userId2)
              ->where('recipient_id', $userId1);
        });
    }
}