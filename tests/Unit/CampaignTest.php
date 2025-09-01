<?php

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_campaign()
    {
        $user = User::factory()->create();
        
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Campaign',
            'budget' => 1000.00,
            'daily_budget' => 100.00,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('campaigns', [
            'user_id' => $user->id,
            'name' => 'Test Campaign',
            'budget' => 1000.00,
            'daily_budget' => 100.00,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $campaign->user);
        $this->assertEquals($user->id, $campaign->user->id);
    }
}