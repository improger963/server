<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creative extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campaign_id',
        'name',
        'type',
        'content',
        'url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'content' => 'array', // Changed to array for JSON handling
    ];

    // Updated type constants
    const TYPE_BANNER = 'banner';
    const TYPE_LINK = 'link';
    const TYPE_CONTEXT = 'context';
    const TYPE_CREATIVE_IMAGE_TEXT = 'creative_image_text';

    public static function getAvailableTypes()
    {
        return [
            self::TYPE_BANNER,
            self::TYPE_LINK,
            self::TYPE_CONTEXT,
            self::TYPE_CREATIVE_IMAGE_TEXT,
        ];
    }

    /**
     * Get the campaign that owns the creative.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}