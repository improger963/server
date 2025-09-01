<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'budget',
        'spent',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the campaign.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the creatives for the campaign.
     */
    public function creatives()
    {
        return $this->hasMany(Creative::class);
    }

    /**
     * Get the ad slots that are running this campaign.
     */
    public function adSlots()
    {
        return $this->belongsToMany(AdSlot::class);
    }

    /**
     * Scope a query to only include active campaigns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include campaigns within their date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRunning($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    /**
     * Check if the campaign has enough budget
     *
     * @param float $amount
     * @return bool
     */
    public function hasBudget($amount = 0.00)
    {
        return ($this->budget - $this->spent) >= $amount;
    }

    /**
     * Deduct amount from campaign budget
     *
     * @param float $amount
     * @return bool
     */
    public function deductBudget($amount)
    {
        if (!$this->hasBudget($amount)) {
            return false;
        }

        $this->spent += $amount;
        
        // If budget is exhausted, deactivate campaign
        if ($this->spent >= $this->budget) {
            $this->is_active = false;
        }
        
        return $this->save();
    }

    /**
     * Check if campaign is within date range
     *
     * @return bool
     */
    public function isRunning()
    {
        $now = now();
        return $this->start_date <= $now && 
               ($this->end_date === null || $this->end_date >= $now);
    }

    /**
     * Check if campaign can be activated
     *
     * @return bool
     */
    public function canActivate()
    {
        return $this->isRunning() && $this->hasBudget();
    }
}