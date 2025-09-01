<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSlot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'site_id',
        'name',
        'size',
        'price_per_click',
        'price_per_impression',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_per_click' => 'decimal:4',
        'price_per_impression' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    /**
     * Get the site that owns the ad slot.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the campaigns that are running in this ad slot.
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class);
    }

    /**
     * Scope a query to only include active ad slots.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if the ad slot is associated with a campaign
     *
     * @param Campaign $campaign
     * @return bool
     */
    public function hasCampaign(Campaign $campaign)
    {
        return $this->campaigns()->where('campaign_id', $campaign->id)->exists();
    }
}