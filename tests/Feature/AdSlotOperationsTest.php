<?php

namespace Tests\Feature;

use App\Models\AdSlot;
use App\Models\Campaign;
use App\Models\Creative;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdSlotOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $site;
    protected $adSlot;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user
        $this->user = User::factory()->create();
        
        // Create a site
        $this->site = Site::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true
        ]);
        
        // Create an ad slot
        $this->adSlot = AdSlot::factory()->create([
            'site_id' => $this->site->id,
            'is_active' => true,
            'pricing' => 0.10
        ]);
    }

    /** @test */
    public function user_can_request_ad_from_ad_slot()
    {
        // Create a campaign with budget
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'budget' => 500.00,
            'spent' => 0.00,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay()
        ]);

        // Create a creative
        $creative = Creative::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => true
        ]);

        // Associate campaign with ad slot
        $this->adSlot->campaigns()->attach($campaign->id);

        $response = $this->getJson("/api/ad-slots/{$this->adSlot->id}/request");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'creative' => [
                'id',
                'campaign_id',
                'type',
                'data',
                'is_active',
                'created_at',
                'updated_at'
            ],
            'campaign_id'
        ]);

        // Refresh campaign from database
        $campaign->refresh();

        // Check that campaign budget was reduced
        $this->assertEquals(0.10, $campaign->spent);
    }

    /** @test */
    public function ad_request_fails_when_ad_slot_is_inactive()
    {
        // Make ad slot inactive
        $this->adSlot->update(['is_active' => false]);

        $response = $this->getJson("/api/ad-slots/{$this->adSlot->id}/request");

        $response->assertStatus(410);
        $response->assertJson([
            'error' => 'Ad slot is not active'
        ]);
    }

    /** @test */
    public function ad_request_fails_when_no_active_campaigns()
    {
        $response = $this->getJson("/api/ad-slots/{$this->adSlot->id}/request");

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'No active campaigns available'
        ]);
    }

    /** @test */
    public function user_can_associate_campaign_with_ad_slot()
    {
        $this->actingAs($this->user);

        // Create a campaign
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->postJson("/api/sites/{$this->site->id}/ad-slots/{$this->adSlot->id}/campaigns", [
            'campaign_id' => $campaign->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Campaign associated successfully'
        ]);

        // Check that campaign is associated with ad slot
        $this->assertTrue($this->adSlot->hasCampaign($campaign));
    }

    /** @test */
    public function user_cannot_associate_nonexistent_campaign()
    {
        $this->actingAs($this->user);

        $response = $this->postJson("/api/sites/{$this->site->id}/ad-slots/{$this->adSlot->id}/campaigns", [
            'campaign_id' => 999999
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Campaign not found'
        ]);
    }
}