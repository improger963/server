<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $admin;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user first to ensure ID 1
        $this->admin = User::factory()->create();
        $this->admin->id = 1;
        $this->admin->save();
        
        // Create regular user
        $this->user = User::factory()->create();
    }
    
    /** @test */
    public function user_can_create_ticket()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tickets', [
                'subject' => 'Test Ticket',
                'description' => 'This is a test ticket',
                'priority' => 'high',
                'category' => 'technical'
            ]);
        
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Ticket created successfully'
            ]);
        
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Test Ticket',
            'description' => 'This is a test ticket',
            'priority' => 'high',
            'category' => 'technical',
            'user_id' => $this->user->id
        ]);
    }
    
    /** @test */
    public function user_can_view_their_own_tickets()
    {
        // Create a ticket for the user
        $ticket = Ticket::factory()->create([
            'user_id' => $this->user->id,
            'subject' => 'Test Ticket'
        ]);
        
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tickets');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'subject' => 'Test Ticket',
                        'user_id' => $this->user->id
                    ]
                ]
            ]);
    }
    
    /** @test */
    public function user_cannot_view_other_users_tickets()
    {
        // Create a ticket for another user
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $otherUser->id,
            'subject' => 'Other User Ticket'
        ]);
        
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/tickets/{$ticket->id}");
        
        $response->assertStatus(403);
    }
    
    /** @test */
    public function admin_can_view_all_tickets()
    {
        // Create a ticket for another user
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $otherUser->id,
            'subject' => 'Other User Ticket'
        ]);
        
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/tickets/{$ticket->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'subject' => 'Other User Ticket'
                ]
            ]);
    }
    
    /** @test */
    public function user_can_add_reply_to_their_ticket()
    {
        // Create a ticket for the user
        $ticket = Ticket::factory()->create([
            'user_id' => $this->user->id,
            'subject' => 'Test Ticket'
        ]);
        
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/reply", [
                'message' => 'This is a test reply'
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Reply added successfully'
            ]);
        
        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->user->id,
            'message' => 'This is a test reply'
        ]);
    }
    
    /** @test */
    public function validation_error_when_creating_ticket_without_subject()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tickets', [
                'description' => 'This is a test ticket'
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    }
}