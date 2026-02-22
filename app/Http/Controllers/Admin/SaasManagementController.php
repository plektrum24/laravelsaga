<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use App\Models\Invoice;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\BillingService;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaasManagementController extends Controller
{
    protected BillingService $billingService;
    protected InvoicePdfService $invoicePdfService;

    public function __construct(
        BillingService $billingService,
        InvoicePdfService $invoicePdfService
    ) {
        $this->billingService = $billingService;
        $this->invoicePdfService = $invoicePdfService;
    }

    /**
     * Super Admin Dashboard
     * GET /admin/saas/dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('subscription_status', 'active')->count(),
            'trial_tenants' => Tenant::where('subscription_status', 'trial')->count(),
            'suspended_tenants' => Tenant::where('subscription_status', 'suspended')->count(),
            'mrr' => $this->calculateMRR(),
            'arr' => $this->calculateMRR() * 12,
            'churn_rate' => $this->calculateChurnRate(),
            'growth_rate' => $this->calculateGrowthRate(),
        ];

        $recentTenants = Tenant::with('subscription')
            ->latest()
            ->limit(10)
            ->get();

        $overdueInvoices = Invoice::overdue()
            ->with('tenant')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_tenants' => $recentTenants,
                'overdue_invoices' => $overdueInvoices,
            ]
        ]);
    }

    /**
     * Get all tenants
     * GET /admin/saas/tenants
     */
    public function tenants(Request $request)
    {
        $query = Tenant::with('subscription');

        if ($request->has('status')) {
            $query->where('subscription_status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('owner_name', 'like', '%' . $request->search . '%');
            });
        }

        $tenants = $query->latest()->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $tenants,
        ]);
    }

    /**
     * Get tenant detail
     * GET /admin/saas/tenants/{id}
     */
    public function tenantDetail($id)
    {
        $tenant = Tenant::with(['subscription', 'invoices', 'supportTickets', 'users'])
            ->findOrFail($id);

        $usage = [];
        // Get usage metrics from tenant database
        // This would connect to tenant DB and get actual usage

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => $tenant,
                'usage' => $usage,
            ]
        ]);
    }

    /**
     * Create/Update tenant subscription
     * POST /admin/saas/tenants/{id}/subscription
     */
    public function updateSubscription(Request $request, $id)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscription_plans,id',
            'status' => 'in:trial,active,suspended,cancelled,expired',
            'trial_ends_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        $tenant = Tenant::findOrFail($id);

        $tenant->update([
            'subscription_id' => $validated['subscription_id'],
            'subscription_status' => $validated['status'] ?? 'active',
            'trial_ends_at' => $validated['trial_ends_at'] ?? null,
            'subscription_expires_at' => $validated['expires_at'] ?? null,
        ]);

        // Create invoice if upgrading
        if ($request->has('create_invoice') && $request->create_invoice) {
            $plan = SubscriptionPlan::find($validated['subscription_id']);
            $this->createInvoice($tenant, $plan);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'data' => $tenant->fresh(),
        ]);
    }

    /**
     * Suspend tenant
     * POST /admin/saas/tenants/{id}/suspend
     */
    public function suspendTenant(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'subscription_status' => 'suspended',
            'is_active' => false,
        ]);

        // Log suspension reason
        // Send notification to tenant

        return response()->json([
            'success' => true,
            'message' => 'Tenant suspended successfully',
        ]);
    }

    /**
     * Reactivate tenant
     * POST /admin/saas/tenants/{id}/reactivate
     */
    public function reactivateTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'subscription_status' => 'active',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant reactivated successfully',
        ]);
    }

    /**
     * Create subscription for tenant
     * POST /admin/saas/tenants/{id}/create-subscription
     */
    public function createSubscription(Request $request, $id)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'in:monthly,yearly',
        ]);

        $tenant = Tenant::findOrFail($id);
        $plan = SubscriptionPlan::find($validated['plan_id']);

        $result = $this->billingService->createSubscription(
            $tenant,
            $plan,
            $validated['billing_cycle'] ?? 'monthly'
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully',
                'data' => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }
    }

    /**
     * Process invoice payment
     * POST /admin/saas/invoices/{id}/pay
     */
    public function payInvoice(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:credit_card,gopay,bank_transfer',
        ]);

        $invoice = Invoice::findOrFail($id);

        $result = $this->billingService->processPayment(
            $invoice,
            $validated['payment_method']
        );

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
     * Download invoice PDF
     * GET /admin/saas/invoices/{id}/pdf
     */
    public function downloadInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);

        return $this->invoicePdfService->download($invoice);
    }

    /**
     * Process recurring billing (manual trigger)
     * POST /admin/saas/billing/process-recurring
     */
    public function processRecurring()
    {
        $result = $this->billingService->processRecurringBilling();

        return response()->json([
            'success' => true,
            'message' => 'Recurring billing processed',
            'data' => $result,
        ]);
    }

    /**
     * Check overdue invoices (manual trigger)
     * POST /admin/saas/billing/check-overdue
     */
    public function checkOverdue()
    {
        $count = $this->billingService->checkOverdueInvoices();

        return response()->json([
            'success' => true,
            'message' => 'Checked ' . $count . ' overdue invoices',
        ]);
    }

    /**
     * Get billing stats
     * GET /admin/saas/billing/stats
     */
    public function billingStats()
    {
        $stats = [
            'total_revenue' => Invoice::paid()->sum('total'),
            'pending_revenue' => Invoice::where('status', 'sent')->sum('total'),
            'overdue_revenue' => Invoice::overdue()->sum('total'),
            'mrr' => $this->calculateMRR(),
            'invoices_this_month' => Invoice::whereMonth('created_at', now()->month)->count(),
            'paid_this_month' => Invoice::whereMonth('paid_at', now()->month)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get all subscription plans
     * GET /admin/saas/plans
     */
    public function plans()
    {
        $plans = SubscriptionPlan::ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Create/Update subscription plan
     * POST /admin/saas/plans
     */
    public function savePlan(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:subscription_plans,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:subscription_plans,code,' . ($request->id ?? 'NULL'),
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
            'trial_days' => 'integer|min:0',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ]);

        if ($request->id) {
            $plan = SubscriptionPlan::find($request->id);
            $plan->update($validated);
        } else {
            $plan = SubscriptionPlan::create($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Plan saved successfully',
            'data' => $plan,
        ]);
    }

    /**
     * Get invoices
     * GET /admin/saas/invoices
     */
    public function invoices(Request $request)
    {
        $query = Invoice::with(['tenant', 'subscription']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $invoices = $query->latest()->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Get support tickets
     * GET /admin/saas/tickets
     */
    public function tickets(Request $request)
    {
        $query = SupportTicket::with(['tenant', 'user', 'assignee']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->latest()->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    /**
     * Calculate MRR (Monthly Recurring Revenue)
     */
    private function calculateMRR()
    {
        return Tenant::where('subscription_status', 'active')
            ->where('auto_renew', true)
            ->join('subscription_plans', 'tenants.subscription_id', '=', 'subscription_plans.id')
            ->sum('subscription_plans.price_monthly');
    }

    /**
     * Calculate churn rate
     */
    private function calculateChurnRate()
    {
        $lastMonth = Carbon::now()->subMonth();
        
        $cancelled = Tenant::where('subscription_status', 'cancelled')
            ->whereMonth('updated_at', $lastMonth->month)
            ->count();
        
        $total = Tenant::count();
        
        return $total > 0 ? round(($cancelled / $total) * 100, 2) : 0;
    }

    /**
     * Calculate growth rate
     */
    private function calculateGrowthRate()
    {
        $thisMonth = Tenant::whereMonth('created_at', Carbon::now()->month)->count();
        $lastMonth = Tenant::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        
        return $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2) : 0;
    }

    /**
     * Create invoice for tenant
     */
    private function createInvoice(Tenant $tenant, SubscriptionPlan $plan)
    {
        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $plan->id,
            'invoice_number' => (new Invoice)->generateInvoiceNumber(),
            'invoice_type' => 'subscription',
            'subtotal' => $plan->price_monthly,
            'tax' => $plan->price_monthly * 0.11, // 11% PPN
            'total' => $plan->price_monthly * 1.11,
            'status' => 'sent',
            'due_date' => Carbon::now()->addDays(30),
        ]);

        // Send invoice email
        // This would trigger email notification

        return $invoice;
    }
}
