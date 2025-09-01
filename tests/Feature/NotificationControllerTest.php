<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_user_notifications()
    {
        $user = User::factory()->create();
        
        // Create some notifications for the user
        $user->notify(new \App\Notifications\BalanceTopUpSuccess(50.00, 'TXN_12345'));
        $user->notify(new \App\Notifications\BalanceTopUpSuccess(100.00, 'TXN_67890'));
        
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/notifications');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at']
                     ],
                     'links',
                     'meta'
                 ]);
        
        $this->assertCount(2, $response->json('data'));
    }
    
    /** @test */
    public function it_can_mark_a_notification_as_read()
    {
        $user = User::factory()->create();
        
        // Create a notification for the user
        $user->notify(new \App\Notifications\BalanceTopUpSuccess(50.00, 'TXN_12345'));
        $notification = $user->notifications()->first();
        
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/notifications/{$notification->id}/read");
        
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Notification marked as read']);
        
        $this->assertNotNull($notification->fresh()->read_at);
    }
    
    /** @test */
    public function it_cannot_mark_nonexistent_notification_as_read()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/notifications/99999/read');
        
        $response->assertStatus(404);
    }
    
    /** @test */
    public function it_requires_authentication_to_access_notifications()
    {
        $response = $this->getJson('/api/notifications');
        
        $response->assertStatus(401);
    }
}