<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\User;

class SitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        // Create 5 sites for each user
        foreach ($users as $user) {
            Site::factory()->count(5)->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
