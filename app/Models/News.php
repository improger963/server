<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'title',
        'content',
        'author',
        'is_published',
        'published_at',
    ];
    
    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];
    
    // Scope for published news
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                    ->where('published_at', '<=', now());
    }
    
    // Automatically set published_at when publishing
    public function setPublishedAttribute($value)
    {
        $this->attributes['is_published'] = $value;
        if ($value && !$this->published_at) {
            $this->attributes['published_at'] = now();
        }
    }
}