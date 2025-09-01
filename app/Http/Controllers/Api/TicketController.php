<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddTicketReplyRequest;
use App\Http\Requests\CreateTicketRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected $ticketService;
    
    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = [];
            
            if ($request->has('status')) {
                $filters['status'] = $request->get('status');
            }
            
            if ($request->has('priority')) {
                $filters['priority'] = $request->get('priority');
            }
            
            if ($request->has('category')) {
                $filters['category'] = $request->get('category');
            }
            
            // If user is admin, they can see all tickets, otherwise only their own
            if (auth()->user()->hasRole('admin')) {
                $tickets = $this->ticketService->getAllTickets($filters);
            } else {
                $filters['user_id'] = auth()->id();
                $tickets = $this->ticketService->getAllTickets($filters);
            }
            
            return response()->json([
                'success' => true,
                'data' => $tickets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tickets: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTicketRequest $request)
    {
        try {
            $ticket = $this->ticketService->createTicket(auth()->user(), $request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Ticket created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        try {
            // Check if user can view this ticket (owner or admin)
            if (!auth()->user()->hasRole('admin') && $ticket->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to ticket'
                ], 403);
            }
            
            // Load relationships
            $ticket->load(['user', 'replies.user', 'assignee']);
            
            return response()->json([
                'success' => true,
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ticket: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Add a reply to the specified ticket.
     */
    public function reply(Ticket $ticket, AddTicketReplyRequest $request)
    {
        try {
            // Check if user can reply to this ticket (owner or admin)
            if (!auth()->user()->hasRole('admin') && $ticket->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to reply to this ticket'
                ], 403);
            }
            
            $reply = $this->ticketService->addReply($ticket, auth()->user(), $request->message);
            
            return response()->json([
                'success' => true,
                'data' => $reply,
                'message' => 'Reply added successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add reply: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the status of the specified ticket.
     */
    public function updateStatus(Ticket $ticket, Request $request)
    {
        try {
            // Only admin or assigned user can update status
            if (!auth()->user()->hasRole('admin') && $ticket->assigned_to !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update ticket status'
                ], 403);
            }
            
            $request->validate([
                'status' => 'required|in:open,in_progress,resolved,closed'
            ]);
            
            $ticket = $this->ticketService->updateStatus($ticket, $request->status);
            
            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Ticket status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ticket status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Assign the specified ticket to a user.
     */
    public function assign(Ticket $ticket, Request $request)
    {
        try {
            // Only admin can assign tickets
            if (!auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to assign tickets'
                ], 403);
            }
            
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);
            
            $user = \App\Models\User::find($request->user_id);
            $ticket = $this->ticketService->assignTicket($ticket, $user);
            
            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Ticket assigned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign ticket: ' . $e->getMessage()
            ], 500);
        }
    }
}