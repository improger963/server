<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_referrals()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        $referredUser = User::factory()->create();
        
        $referrals = Referral::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'referrer_id' => $user->id,
            'referred_user_id' => $referredUser->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/referrals');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_a_referral()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        $referredUser = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/referrals', [
                'campaign_id' => $campaign->id,
                'referred_user_id' => $referredUser->id,
                'commission_rate' => 10.50,
                'lifetime_commission' => true,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'campaign_id', 'referrer_id', 'referred_user_id', 'commission_rate', 'lifetime_commission']);

        $this->assertDatabaseHas('referrals', [
            'campaign_id' => $campaign->id,
            'referrer_id' => $user->id,
            'referred_user_id' => $referredUser->id,
            'commission_rate' => 10.50,
            'lifetime_commission' => true,
        ]);
    }
}