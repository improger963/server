<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\AnalyticsEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsEventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_an_analytics_event()
    {
        $user = User::factory()->create();
        
        $analyticsEvent = AnalyticsEvent::create([
            'user_id' => $user->id,
            'type' => 'impression',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 0.01,
        ]);
        
        $this->assertDatabaseHas('analytics_events', [
            'user_id' => $user->id,
            'type' => 'impression',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 0.01,
        ]);
        
        $this->assertEquals(0.01, $analyticsEvent->cost);
    }
    
    /** @test */
    public function it_can_relate_to_a_user()
    {
        $user = User::factory()->create();
        
        $analyticsEvent = AnalyticsEvent::create([
            'user_id' => $user->id,
            'type' => 'click',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 0.02,
        ]);
        
        $this->assertInstanceOf(User::class, $analyticsEvent->user);
        $this->assertEquals($user->id, $analyticsEvent->user->id);
    }
    
    /** @test */
    public function it_can_scope_events_for_a_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        AnalyticsEvent::create([
            'user_id' => $user1->id,
            'type' => 'impression',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 0.01,
        ]);
        
        AnalyticsEvent::create([
            'user_id' => $user2->id,
            'type' => 'impression',
            'related_id' => 1,
            'related_type' => 'campaign',
            'cost' => 0.01,
        ]);
        
        $user1Events = AnalyticsEvent::forUser($user1->id)->get();
        $user2Events = AnalyticsEvent::forUser($user2->id)->get();
        
        $this->assertCount(1, $user1Events);
        $this->assertCount(1, $user2Events);
        $this->assertEquals($user1->id, $user1Events->first()->user_id);
        $this->assertEquals($user2->id, $user2Events->first()->user_id);
    }
    
    /** @test */
    public function it_can_scope_events_by_type()
    {
        $user = User::factory()->create();
        
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
        
        $impressions = AnalyticsEvent::ofType('impression')->get();
        $clicks = AnalyticsEvent::ofType('click')->get();
        
        $this->assertCount(1, $impressions);
        $this->assertCount(1, $clicks);
        $this->assertEquals('impression', $impressions->first()->type);
        $this->assertEquals('click', $clicks->first()->type);
    }
}