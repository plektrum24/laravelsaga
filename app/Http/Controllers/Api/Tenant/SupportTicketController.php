<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of tenant tickets
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $query = SupportTicket::forTenant($tenant->id)
            ->with(['assignedAdmin']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Store a newly created support ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:100',
        ]);

        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $ticket = SupportTicket::create([
            'tenant_id' => $tenant->id,
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority ?? 'medium',
            'category' => $request->category,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => $ticket
        ], 201);
    }

    /**
     * Display the specified support ticket
     */
    public function show(SupportTicket $ticket)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant || $ticket->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        $ticket->load(['messages.user', 'assignedAdmin']);

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Reply to a support ticket
     */
    public function reply(SupportTicket $ticket, Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant || $ticket->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        $message = SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_admin_message' => false,
        ]);

        // Update ticket status if needed
        if ($ticket->status === 'waiting_customer') {
            $ticket->update(['status' => 'in_progress']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully',
            'data' => [
                'message' => $message,
                'ticket' => $ticket->fresh()
            ]
        ]);
    }

    /**
     * Close a support ticket
     */
    public function close(SupportTicket $ticket)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant || $ticket->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        if (!$ticket->isResolved()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot close ticket that is not resolved'
            ], 400);
        }

        $ticket->close();

        return response()->json([
            'success' => true,
            'message' => 'Ticket closed',
            'data' => $ticket->fresh()
        ]);
    }

    /**
     * Reopen a resolved ticket
     */
    public function reopen(SupportTicket $ticket)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant || $ticket->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        if (!$ticket->isResolved() && !$ticket->isClosed()) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is not resolved or closed'
            ], 400);
        }

        $ticket->reopen();

        return response()->json([
            'success' => true,
            'message' => 'Ticket reopened',
            'data' => $ticket->fresh()
        ]);
    }
}
