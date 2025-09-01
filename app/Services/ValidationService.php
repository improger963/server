<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Campaign;
use App\Models\Creative;

class ValidationService
{
    /**
     * Validate site data
     *
     * @param array $data
     * @return array
     */
    public function validateSite(array $data)
    {
        $errors = [];
        
        // Check if URL is unique
        if (isset($data['url'])) {
            $existingSite = Site::where('url', $data['url'])->first();
            if ($existingSite) {
                $errors['url'] = 'The URL must be unique.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate campaign data
     *
     * @param array $data
     * @return array
     */
    public function validateCampaign(array $data)
    {
        $errors = [];
        
        // Check if start date is before end date
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = new \DateTime($data['start_date']);
            $endDate = new \DateTime($data['end_date']);
            
            if ($startDate >= $endDate) {
                $errors['dates'] = 'The start date must be before the end date.';
            }
        }
        
        // Check if budget is positive
        if (isset($data['budget']) && $data['budget'] <= 0) {
            $errors['budget'] = 'The budget must be a positive value.';
        }
        
        return $errors;
    }
    
    /**
     * Validate ad slot data
     *
     * @param array $data
     * @return array
     */
    public function validateAdSlot(array $data)
    {
        $errors = [];
        
        // Check if pricing is non-negative
        if (isset($data['price_per_click']) && $data['price_per_click'] < 0) {
            $errors['price_per_click'] = 'Price per click must be non-negative.';
        }
        
        if (isset($data['price_per_impression']) && $data['price_per_impression'] < 0) {
            $errors['price_per_impression'] = 'Price per impression must be non-negative.';
        }
        
        // Validate dimensions for banner type
        if (isset($data['type']) && $data['type'] === 'banner') {
            if (isset($data['dimensions'])) {
                if (!is_array($data['dimensions'])) {
                    $errors['dimensions'] = 'Dimensions must be an array.';
                } else {
                    if (!isset($data['dimensions']['width']) || !is_numeric($data['dimensions']['width'])) {
                        $errors['dimensions'] = 'Banner dimensions must include a valid width.';
                    }
                    
                    if (!isset($data['dimensions']['height']) || !is_numeric($data['dimensions']['height'])) {
                        $errors['dimensions'] = 'Banner dimensions must include a valid height.';
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate creative data
     *
     * @param array $data
     * @return array
     */
    public function validateCreative(array $data)
    {
        $errors = [];
        
        // Check content based on type
        if (isset($data['type']) && isset($data['content'])) {
            switch ($data['type']) {
                case 'banner':
                    if (empty($data['content']['image_url'])) {
                        $errors['content'] = 'Banner creatives must have an image URL.';
                    }
                    if (empty($data['content']['target_url'])) {
                        $errors['content'] = 'Banner creatives must have a target URL.';
                    }
                    break;
                    
                case 'link':
                    if (empty($data['content']['text'])) {
                        $errors['content'] = 'Link creatives must have text content.';
                    }
                    if (empty($data['content']['target_url'])) {
                        $errors['content'] = 'Link creatives must have a target URL.';
                    }
                    break;
                    
                case 'context':
                    if (empty($data['content']['title'])) {
                        $errors['content'] = 'Context creatives must have a title.';
                    }
                    if (empty($data['content']['description'])) {
                        $errors['content'] = 'Context creatives must have a description.';
                    }
                    if (empty($data['content']['target_url'])) {
                        $errors['content'] = 'Context creatives must have a target URL.';
                    }
                    break;
                    
                case 'creative_image_text':
                    if (empty($data['content']['image_url'])) {
                        $errors['content'] = 'Creative image text creatives must have an image URL.';
                    }
                    if (empty($data['content']['title'])) {
                        $errors['content'] = 'Creative image text creatives must have a title.';
                    }
                    if (empty($data['content']['description'])) {
                        $errors['content'] = 'Creative image text creatives must have a description.';
                    }
                    if (empty($data['content']['target_url'])) {
                        $errors['content'] = 'Creative image text creatives must have a target URL.';
                    }
                    break;
                    
                default:
                    $errors['type'] = 'Invalid creative type.';
                    break;
            }
        }
        
        return $errors;
    }
}