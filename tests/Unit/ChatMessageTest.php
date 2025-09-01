<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_chat_message()
    {
        $user = User::factory()->create();
        
        $chatMessage = ChatMessage::create([
            'user_id' => $user->id,
            'message' => 'Hello, world!',
        ]);
        
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'message' => 'Hello, world!',
        ]);
        
        $this->assertEquals('Hello, world!', $chatMessage->message);
    }
    
    /** @test */
    public function it_can_relate_to_a_user()
    {
        $user = User::factory()->create();
        
        $chatMessage = ChatMessage::create([
            'user_id' => $user->id,
            'message' => 'Hello, world!',
        ]);
        
        $this->assertInstanceOf(User::class, $chatMessage->user);
        $this->assertEquals($user->id, $chatMessage->user->id);
    }
    
    /** @test */
    public function it_can_scope_recent_messages()
    {
        $user = User::factory()->create();
        
        // Create messages at different times
        $oldMessage = ChatMessage::create([
            'user_id' => $user->id,
            'message' => 'Old message',
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);
        
        $newMessage = ChatMessage::create([
            'user_id' => $user->id,
            'message' => 'New message',
        ]);
        
        $recentMessages = ChatMessage::recent(1)->get();
        
        $this->assertCount(1, $recentMessages);
        $this->assertEquals('New message', $recentMessages->first()->message);
    }
}