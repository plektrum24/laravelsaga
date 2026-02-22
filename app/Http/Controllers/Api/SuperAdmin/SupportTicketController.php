<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of support tickets
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['tenant', 'creator', 'assignedAdmin']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by assigned to me
        if ($request->get('assigned_to_me') && Auth::check()) {
            $query->where('assigned_to', Auth::id());
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', "%{$request->search}%")
                  ->orWhereHas('tenant', function ($q2) use ($request) {
                      $q2->where('name', 'like', "%{$request->search}%");
                  });
            });
        }

        $tickets = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Display the specified support ticket
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load(['tenant', 'creator', 'assignedAdmin', 'messages.user']);

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Assign ticket to admin
     */
    public function assign(SupportTicket $ticket, Request $request)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket->assignTo($request->assigned_to);

        // Add system message
        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => 'Ticket assigned to ' . $ticket->assignedAdmin->name,
            'is_admin_message' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket assigned successfully',
            'data' => $ticket->fresh()
        ]);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(SupportTicket $ticket, Request $request)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,waiting_customer,resolved,closed',
        ]);

        $ticket->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket status updated',
            'data' => $ticket->fresh()
        ]);
    }

    /**
     * Reply to ticket
     */
    public function reply(SupportTicket $ticket, Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_admin_message' => true,
        ]);

        // Update ticket status if it was waiting for customer
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
     * Resolve ticket
     */
    public function resolve(SupportTicket $ticket)
    {
        $ticket->resolve(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Ticket resolved',
            'data' => $ticket->fresh()
        ]);
    }

    /**
     * Get ticket statistics
     */
    public function stats()
    {
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::whereIn('status', ['open', 'in_progress', 'waiting_customer'])->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
            'urgent' => SupportTicket::where('priority', 'urgent')->count(),
            'high' => SupportTicket::where('priority', 'high')->count(),
        ];

        // Tickets by category
        $byCategory = SupportTicket::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        // Average resolution time (hours)
        $avgResolutionTime = SupportTicket::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'by_category' => $byCategory,
                'avg_resolution_time_hours' => round($avgResolutionTime ?? 0, 2),
            ]
        ]);
    }
}
