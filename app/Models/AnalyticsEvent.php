<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'related_id',
        'related_type',
        'cost',
    ];
    
    protected $casts = [
        'cost' => 'decimal:4',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function related()
    {
        return $this->morphTo();
    }
    
    // Scope methods for filtering
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}