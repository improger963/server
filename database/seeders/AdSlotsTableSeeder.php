<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdSlot;
use App\Models\Site;

class AdSlotsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all sites
        $sites = Site::all();
        
        // Create 3 ad slots for each site
        foreach ($sites as $site) {
            AdSlot::factory()->count(3)->create([
                'site_id' => $site->id,
            ]);
        }
    }
}
