<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_chat_messages()
    {
        $user = User::factory()->create();
        
        // Create some chat messages
        ChatMessage::create([
            'user_id' => $user->id,
            'message' => 'Hello, world!',
        ]);
        
        ChatMessage::create([
            'user_id' => $user->id,
            'message' => 'How are you?',
        ]);
        
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/chat/messages');
        
        $response->assertStatus(200)
                 ->assertJsonCount(2);
        
        $response->assertJsonFragment(['message' => 'Hello, world!']);
        $response->assertJsonFragment(['message' => 'How are you?']);
    }
    
    /** @test */
    public function it_can_send_a_chat_message()
    {
        Broadcast::fake();
        
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/chat/messages', [
                             'message' => 'Hello, world!',
                         ]);
        
        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Hello, world!',
                     'user_id' => $user->id,
                 ]);
        
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'message' => 'Hello, world!',
        ]);
        
        // Assert that the message was broadcast
        Broadcast::assertBroadcasted(\App\Events\MessageSent::class);
    }
    
    /** @test */
    public function it_validates_chat_message_content()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/chat/messages', [
                             'message' => '', // Empty message
                         ]);
        
        $response->assertStatus(422);
        
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/chat/messages', [
                             'message' => str_repeat('a', 1001), // Too long message
                         ]);
        
        $response->assertStatus(422);
    }
    
    /** @test */
    public function it_requires_authentication_to_send_chat_messages()
    {
        $response = $this->postJson('/api/chat/messages', [
            'message' => 'Hello, world!',
        ]);
        
        $response->assertStatus(401);
    }
    
    /** @test */
    public function it_requires_authentication_to_get_chat_messages()
    {
        $response = $this->getJson('/api/chat/messages');
        
        $response->assertStatus(401);
    }
}