<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\User;

class CampaignsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        // Create 2 campaigns for each user
        foreach ($users as $user) {
            Campaign::factory()->count(2)->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
