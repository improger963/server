<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_campaigns()
    {
        $user = User::factory()->create();
        $campaigns = Campaign::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/campaigns');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_a_campaign()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/campaigns', [
                'name' => 'Test Campaign',
                'budget' => 1000.00,
                'daily_budget' => 100.00,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'name', 'budget', 'daily_budget', 'start_date', 'end_date', 'status']);

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Test Campaign',
            'budget' => 1000.00,
            'daily_budget' => 100.00,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_can_simulate_a_campaign()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/campaigns/simulate', [
                'campaign_id' => $campaign->id,
                'simulation_parameters' => [
                    'click_rate' => 0.05,
                    'conversion_rate' => 0.02,
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['results']);
    }
}