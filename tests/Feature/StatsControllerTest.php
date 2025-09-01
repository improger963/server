<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AnalyticsEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_dashboard_statistics()
    {
        $user = User::factory()->create();
        
        // Create some analytics events
        AnalyticsEvent::create([
            'user_id' => $user->id,
            'type' => 'impression',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 0.01,
        ]);
        
        AnalyticsEvent::create([
            'user_id' => $user->id,
            'type' => 'click',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 0.02,
        ]);
        
        AnalyticsEvent::create([
            'user_id' => $user->id,
            'type' => 'spend',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 10.00,
        ]);
        
        AnalyticsEvent::create([
            'user_id' => $user->id,
            'type' => 'earning',
            'related_id' => 1,
            'related_type' => 'site',
            'cost' => 5.00,
        ]);
        
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/stats/dashboard');
        
        $response->assertStatus(200)
                 ->assertJson([
                     'revenue' => 5.00,
                     'spend' => 10.00,
                     'impressions' => 1,
                     'clicks' => 1,
                     'ctr' => 100.00, // 1 click / 1 impression * 100
                 ]);
    }
    
    /** @test */
    public function it_can_filter_dashboard_statistics_by_period()
    {
        $user = User::factory()->create();
        
        // Create an analytics event from last month
        AnalyticsEvent::create([
            'user_id' => $user->id,
            'type' => 'impression',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 0.01,
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ]);
        
        // Create an analytics event from this month
        AnalyticsEvent::create([
            'user_id' => $user->id,
            'type' => 'impression',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 0.01,
        ]);
        
        // Request stats for this month only
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/stats/dashboard?period=month');
        
        $response->assertStatus(200)
                 ->assertJson([
                     'impressions' => 1, // Only this month's impression
                 ]);
    }
    
    /** @test */
    public function it_requires_authentication_to_access_dashboard()
    {
        $response = $this->getJson('/api/stats/dashboard');
        
        $response->assertStatus(401);
    }
}