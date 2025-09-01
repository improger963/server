<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Creative;
use App\Models\Campaign;

class CreativesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all campaigns
        $campaigns = Campaign::all();
        
        // Create 3 creatives for each campaign
        foreach ($campaigns as $campaign) {
            Creative::factory()->count(3)->create([
                'campaign_id' => $campaign->id,
            ]);
        }
    }
}
