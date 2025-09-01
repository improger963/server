<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'priority',
        'status',
        'category',
        'assigned_to',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }
    
    /**
     * Scope a query to only include tickets of a given status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope a query to only include tickets of a given priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
    
    /**
     * Scope a query to only include tickets of a given category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}