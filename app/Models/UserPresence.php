<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPresence extends Model
{
    protected $fillable = [
        'user_id',
        'is_online',
        'last_seen',
    ];
    
    protected $casts = [
        'is_online' => 'boolean',
        'last_seen' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Scope for getting online users
    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }
}