<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketService
{
    /**
     * Create a new ticket
     *
     * @param User $user
     * @param array $data
     * @return Ticket
     */
    public function createTicket(User $user, array $data)
    {
        try {
            DB::beginTransaction();
            
            $ticket = Ticket::create([
                'user_id' => $user->id,
                'subject' => $data['subject'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? 'medium',
                'category' => $data['category'] ?? null,
            ]);
            
            DB::commit();
            
            return $ticket;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating ticket: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Add a reply to a ticket
     *
     * @param Ticket $ticket
     * @param User $user
     * @param string $message
     * @return TicketReply
     */
    public function addReply(Ticket $ticket, User $user, string $message)
    {
        try {
            DB::beginTransaction();
            
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $message,
            ]);
            
            // Update ticket updated_at timestamp
            $ticket->touch();
            
            DB::commit();
            
            return $reply;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error adding ticket reply: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update ticket status
     *
     * @param Ticket $ticket
     * @param string $status
     * @return Ticket
     */
    public function updateStatus(Ticket $ticket, string $status)
    {
        $ticket->status = $status;
        $ticket->save();
        
        return $ticket;
    }
    
    /**
     * Assign ticket to a user
     *
     * @param Ticket $ticket
     * @param User $user
     * @return Ticket
     */
    public function assignTicket(Ticket $ticket, User $user)
    {
        $ticket->assigned_to = $user->id;
        $ticket->save();
        
        return $ticket;
    }
    
    /**
     * Get tickets for a user
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTickets(User $user)
    {
        return Ticket::where('user_id', $user->id)
            ->with(['replies', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get all tickets with optional filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTickets(array $filters = [])
    {
        $query = Ticket::with(['user', 'replies', 'assignee']);
        
        if (isset($filters['status'])) {
            $query->status($filters['status']);
        }
        
        if (isset($filters['priority'])) {
            $query->priority($filters['priority']);
        }
        
        if (isset($filters['category'])) {
            $query->category($filters['category']);
        }
        
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }
}