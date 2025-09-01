<?php

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_referral()
    {
        $user = User::factory()->create();
        $referredUser = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        
        $referral = Referral::factory()->create([
            'campaign_id' => $campaign->id,
            'referrer_id' => $user->id,
            'referred_user_id' => $referredUser->id,
            'commission_rate' => 10.50,
            'lifetime_commission' => true,
        ]);

        $this->assertDatabaseHas('referrals', [
            'campaign_id' => $campaign->id,
            'referrer_id' => $user->id,
            'referred_user_id' => $referredUser->id,
            'commission_rate' => 10.50,
            'lifetime_commission' => true,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_campaign()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        $referral = Referral::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertInstanceOf(Campaign::class, $referral->campaign);
        $this->assertEquals($campaign->id, $referral->campaign->id);
    }

    /** @test */
    public function it_has_a_referrer()
    {
        $user = User::factory()->create();
        $referral = Referral::factory()->create(['referrer_id' => $user->id]);

        $this->assertInstanceOf(User::class, $referral->referrer);
        $this->assertEquals($user->id, $referral->referrer->id);
    }

    /** @test */
    public function it_has_a_referred_user()
    {
        $user = User::factory()->create();
        $referral = Referral::factory()->create(['referred_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $referral->referredUser);
        $this->assertEquals($user->id, $referral->referredUser->id);
    }
}