<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only create users if there are none
        if (User::count() == 0) {
            User::factory(10)->create();

            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $this->call([
            SitesTableSeeder::class,
            AdSlotsTableSeeder::class,
            CampaignsTableSeeder::class,
            CreativesTableSeeder::class,
        ]);
    }
}