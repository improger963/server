<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user with some balance
        $this->user = User::factory()->create([
            'balance' => 1000.00
        ]);
    }

    /** @test */
    public function user_can_create_campaign()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/campaigns', [
            'name' => 'Test Campaign',
            'description' => 'A test campaign',
            'budget' => 500.00,
            'start_date' => '2025-09-01 00:00:00',
            'end_date' => '2025-12-31 23:59:59'
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'name' => 'Test Campaign',
            'budget' => 500.00,
            'is_active' => true
        ]);

        // Check that campaign was created in database
        $this->assertDatabaseHas('campaigns', [
            'user_id' => $this->user->id,
            'name' => 'Test Campaign',
            'budget' => 500.00
        ]);
    }

    /** @test */
    public function user_can_allocate_budget_to_campaign()
    {
        $this->actingAs($this->user);

        // Create a campaign
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'budget' => 0.00
        ]);

        $response = $this->postJson("/api/campaigns/{$campaign->id}/allocate-budget", [
            'amount' => 300.00
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Budget allocated successfully',
            'allocated_amount' => 300.00
        ]);

        // Refresh campaign from database
        $campaign->refresh();

        // Check that campaign budget was updated
        $this->assertEquals(300.00, $campaign->budget);

        // Refresh user from database
        $this->user->refresh();

        // Check that user balance was reduced
        $this->assertEquals(700.00, $this->user->balance);
    }

    /** @test */
    public function user_can_activate_campaign()
    {
        $this->actingAs($this->user);

        // Create a campaign with budget
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'budget' => 500.00,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => false
        ]);

        $response = $this->postJson("/api/campaigns/{$campaign->id}/activate");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Campaign activated successfully',
            'is_active' => true
        ]);

        // Refresh campaign from database
        $campaign->refresh();

        // Check that campaign is active
        $this->assertTrue($campaign->is_active);
    }

    /** @test */
    public function user_can_deactivate_campaign()
    {
        $this->actingAs($this->user);

        // Create an active campaign with budget
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'budget' => 500.00,
            'spent' => 100.00,
            'is_active' => true
        ]);

        $response = $this->postJson("/api/campaigns/{$campaign->id}/deactivate");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Campaign deactivated successfully',
            'is_active' => false
        ]);

        // Refresh campaign from database
        $campaign->refresh();

        // Check that campaign is inactive
        $this->assertFalse($campaign->is_active);

        // Check that unused budget was returned to user
        $this->user->refresh();
        $this->assertEquals(600.00, $this->user->balance); // 1000 - 500 + 400 (unused)
    }

    /** @test */
    public function user_cannot_allocate_more_than_balance()
    {
        $this->actingAs($this->user);

        // Create a campaign
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'budget' => 0.00
        ]);

        $response = $this->postJson("/api/campaigns/{$campaign->id}/allocate-budget", [
            'amount' => 1500.00
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Insufficient funds'
        ]);
    }
}