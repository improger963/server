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
            if ($data['type'] === 'banner' && empty($data['content'])) {
                $errors['content'] = 'Banner creatives must have content.';
            }
            
            if ($data['type'] === 'text' && empty($data['content'])) {
                $errors['content'] = 'Text creatives must have content.';
            }
        }
        
        return $errors;
    }
}