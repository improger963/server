<?php

namespace Tests\Unit;

use App\Events\PrivateMessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateMessageSentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_broadcasts_private_message_event_with_correct_channel()
    {
        // Create users
        $user1 = User::factory()->create(['id' => 1]);
        $user2 = User::factory()->create(['id' => 2]);
        
        // Create a private message
        $message = new ChatMessage([
            'user_id' => $user1->id,
            'recipient_id' => $user2->id,
            'message' => 'Hello, this is a private message!',
        ]);
        
        // Create the event
        $event = new PrivateMessageSent($message);
        
        // Assert the event is broadcast on the correct channel
        // Since user1 ID (1) < user2 ID (2), the channel should be private-chat.1.2
        $this->assertEquals('private-chat.1.2', $event->broadcastOn()->name);
        
        // Assert the broadcast data is correct
        $broadcastData = $event->broadcastWith();
        $this->assertEquals($message->user_id, $broadcastData['message']['user_id']);
        $this->assertEquals($message->recipient_id, $broadcastData['message']['recipient_id']);
        $this->assertEquals($message->message, $broadcastData['message']['message']);
    }
    
    /** @test */
    public function it_broadcasts_private_message_event_with_correct_channel_when_user_ids_are_reversed()
    {
        // Create users
        $user1 = User::factory()->create(['id' => 3]);
        $user2 = User::factory()->create(['id' => 2]);
        
        // Create a private message
        $message = new ChatMessage([
            'user_id' => $user1->id,
            'recipient_id' => $user2->id,
            'message' => 'Hello, this is a private message!',
        ]);
        
        // Create the event
        $event = new PrivateMessageSent($message);
        
        // Assert the event is broadcast on the correct channel
        // Since user2 ID (2) < user1 ID (3), the channel should be private-chat.2.3
        $this->assertEquals('private-chat.2.3', $event->broadcastOn()->name);
    }
}