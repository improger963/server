<?php

namespace Tests\Unit;

use App\Events\UserTyping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTypingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_broadcasts_user_typing_event()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create the event
        $event = new UserTyping($user);
        
        // Assert the event is broadcast on the correct channel
        $this->assertEquals('chat', $event->broadcastOn()->name);
        
        // Assert the broadcast data is correct
        $broadcastData = $event->broadcastWith();
        $this->assertEquals($user->id, $broadcastData['user']['id']);
        $this->assertEquals($user->name, $broadcastData['user']['name']);
    }
}