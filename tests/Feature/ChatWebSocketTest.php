<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatWebSocketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_send_private_message()
    {
        // Create users
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        
        // Authenticate as sender
        $this->actingAs($sender);
        
        // Send a private message
        $response = $this->postJson('/api/chat/messages/private', [
            'recipient_id' => $recipient->id,
            'message' => 'This is a private message',
        ]);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'user_id',
            'recipient_id',
            'message',
            'created_at',
            'updated_at',
        ]);
        
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'message' => 'This is a private message',
        ]);
    }
    
    /** @test */
    public function user_can_mark_message_as_read()
    {
        // Create users
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        
        // Create a message
        $message = ChatMessage::create([
            'user_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);
        
        // Authenticate as recipient
        $this->actingAs($recipient);
        
        // Mark message as read
        $response = $this->postJson("/api/chat/messages/{$message->id}/read");
        
        $response->assertStatus(200);
        $response->assertJson(['status' => 'read']);
        
        $this->assertDatabaseHas('chat_messages', [
            'id' => $message->id,
            'is_read' => true,
        ]);
    }
    
    /** @test */
    public function user_cannot_mark_others_messages_as_read()
    {
        // Create users
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $otherUser = User::factory()->create();
        
        // Create a message
        $message = ChatMessage::create([
            'user_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);
        
        // Authenticate as other user
        $this->actingAs($otherUser);
        
        // Try to mark message as read
        $response = $this->postJson("/api/chat/messages/{$message->id}/read");
        
        $response->assertStatus(403);
    }
    
    /** @test */
    public function user_can_send_typing_indicator()
    {
        // Create user
        $user = User::factory()->create();
        
        // Authenticate as user
        $this->actingAs($user);
        
        // Send typing indicator
        $response = $this->postJson('/api/chat/typing');
        
        $response->assertStatus(200);
        $response->assertJson(['status' => 'typing']);
    }
}