<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use App\Models\Invoice;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TenantPortalController extends Controller
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Tenant Dashboard
     * GET /api/tenant/portal/dashboard
     */
    public function dashboard()
    {
        $tenant = Auth::user()->tenant;

        $stats = [
            'subscription' => $tenant->subscription,
            'status' => $tenant->subscription_status,
            'expires_at' => $tenant->subscription_expires_at,
            'trial_ends_at' => $tenant->trial_ends_at,
            'invoices_unpaid' => Invoice::where('tenant_id', $tenant->id)
                ->whereIn('status', ['sent', 'overdue'])
                ->count(),
            'invoices_overdue' => Invoice::where('tenant_id', $tenant->id)
                ->where('status', 'overdue')
                ->count(),
            'tickets_open' => SupportTicket::where('tenant_id', $tenant->id)
                ->whereIn('status', ['open', 'in_progress', 'waiting_customer'])
                ->count(),
            'usage' => $this->getUsageStats($tenant),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get available plans
     * GET /api/tenant/portal/plans
     */
    public function availablePlans()
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(function($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'code' => $plan->code,
                    'price_monthly' => $plan->price_monthly,
                    'price_yearly' => $plan->price_yearly,
                    'features' => $plan->features,
                    'limits' => $plan->limits,
                    'trial_days' => $plan->trial_days,
                    'is_current' => Auth::user()->tenant->subscription_id === $plan->id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Upgrade/Downgrade plan
     * POST /api/tenant/portal/plan/change
     */
    public function changePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'in:monthly,yearly',
        ]);

        $tenant = Auth::user()->tenant;
        $newPlan = SubscriptionPlan::find($validated['plan_id']);
        $currentPlan = $tenant->subscription;

        // Check if plan is different
        if ($currentPlan && $currentPlan->id === $newPlan->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are already on this plan',
            ], 400);
        }

        // Create invoice for plan change
        $invoice = $this->billingService->createInvoice($tenant, $newPlan, $validated['billing_cycle'] ?? 'monthly');

        // Update tenant subscription (effective after payment)
        $tenant->update([
            'subscription_id' => $newPlan->id,
            'subscription_status' => $newPlan->trial_days > 0 ? 'trial' : 'active',
            'trial_ends_at' => $newPlan->trial_days > 0 ? now()->addDays($newPlan->trial_days) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan change initiated. Please complete payment.',
            'data' => [
                'invoice' => $invoice,
                'new_plan' => $newPlan,
            ],
        ]);
    }

    /**
     * Get billing history
     * GET /api/tenant/portal/billing/history
     */
    public function billingHistory(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $invoices = Invoice::where('tenant_id', $tenant->id)
            ->with('subscription')
            ->latest()
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Get invoice detail
     * GET /api/tenant/portal/billing/invoice/{id}
     */
    public function invoiceDetail($id)
    {
        $tenant = Auth::user()->tenant;
        
        $invoice = Invoice::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->with('subscription')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Pay invoice
     * POST /api/tenant/portal/billing/invoice/{id}/pay
     */
    public function payInvoice(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:credit_card,gopay,bank_transfer',
        ]);

        $tenant = Auth::user()->tenant;
        $invoice = Invoice::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->firstOrFail();

        $result = $this->billingService->processPayment($invoice, $validated['payment_method']);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Payment failed',
            ], 400);
        }
    }

    /**
     * Get payment methods
     * GET /api/tenant/portal/billing/payment-methods
     */
    public function paymentMethods()
    {
        $tenant = Auth::user()->tenant;

        $methods = $tenant->paymentMethods;

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    /**
     * Get usage statistics
     * GET /api/tenant/portal/usage
     */
    public function usage()
    {
        $tenant = Auth::user()->tenant;
        $usage = $this->getUsageStats($tenant);

        return response()->json([
            'success' => true,
            'data' => $usage,
        ]);
    }

    /**
     * Get support tickets
     * GET /api/tenant/portal/support/tickets
     */
    public function supportTickets(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $tickets = SupportTicket::where('tenant_id', $tenant->id)
            ->with('assignee')
            ->latest()
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    /**
     * Create support ticket
     * POST /api/tenant/portal/support/tickets
     */
    public function createTicket(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'in:low,normal,high,urgent',
        ]);

        $tenant = Auth::user()->tenant;

        $ticket = SupportTicket::create([
            'tenant_id' => $tenant->id,
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => $validated['priority'] ?? 'normal',
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created',
            'data' => $ticket,
        ]);
    }

    /**
     * Get ticket detail with messages
     * GET /api/tenant/portal/support/tickets/{id}
     */
    public function ticketDetail($id)
    {
        $tenant = Auth::user()->tenant;
        
        $ticket = SupportTicket::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->with(['messages.user', 'assignee'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }

    /**
     * Reply to ticket
     * POST /api/tenant/portal/support/tickets/{id}/reply
     */
    public function replyToTicket(Request $request, $id)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $tenant = Auth::user()->tenant;
        
        $ticket = SupportTicket::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->firstOrFail();

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_internal' => false,
        ]);

        // Update ticket status if it was waiting for customer
        if ($ticket->status === 'waiting_customer') {
            $ticket->update(['status' => 'in_progress']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully',
        ]);
    }

    /**
     * Get usage statistics helper
     */
    private function getUsageStats(Tenant $tenant)
    {
        // This would query tenant database for actual usage
        // For now, return mock data
        return [
            'users' => [
                'current' => $tenant->users()->count(),
                'limit' => $tenant->getUsageLimit('users'),
                'unlimited' => $tenant->isUnlimited('users'),
            ],
            'products' => [
                'current' => $tenant->products()->count(),
                'limit' => $tenant->getUsageLimit('products'),
                'unlimited' => $tenant->isUnlimited('products'),
            ],
            'branches' => [
                'current' => $tenant->branches()->count(),
                'limit' => $tenant->getUsageLimit('branches'),
                'unlimited' => $tenant->isUnlimited('branches'),
            ],
            'storage_mb' => [
                'current' => 0, // Would need to calculate actual storage
                'limit' => $tenant->getUsageLimit('storage_mb'),
                'unlimited' => $tenant->isUnlimited('storage_mb'),
            ],
        ];
    }
}
