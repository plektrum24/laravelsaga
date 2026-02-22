<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants
     */
    public function index(Request $request)
    {
        $query = Tenant::with(['subscription.plan']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $status = $request->status;
            $query->whereHas('subscription', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // Filter by plan
        if ($request->has('plan_id')) {
            $query->whereHas('subscription', function ($q) use ($request) {
                $q->where('plan_id', $request->plan_id);
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tenants = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $tenants
        ]);
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        $tenant->load([
            'subscription.plan',
            'users',
            'branches'
        ]);

        // Get usage stats
        $usage = DB::table('tenant_usage')
            ->where('tenant_id', $tenant->id)
            ->where('period_end', '>=', Carbon::now())
            ->get();

        // Get invoice stats
        $invoiceStats = DB::table('invoices')
            ->where('tenant_id', $tenant->id)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN total ELSE 0 END) as paid_total'),
                DB::raw('SUM(CASE WHEN status IN ("draft", "sent", "overdue") THEN total ELSE 0 END) as unpaid_total')
            )
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => $tenant,
                'usage' => $usage,
                'invoice_stats' => $invoiceStats,
            ]
        ]);
    }

    /**
     * Update tenant status
     */
    public function updateStatus(Request $request, Tenant $tenant)
    {
        $request->validate([
            'status' => 'required|in:trial,active,suspended,cancelled,expired',
            'reason' => 'nullable|string|max:500',
        ]);

        $subscription = $tenant->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant has no subscription'
            ], 404);
        }

        $subscription->update([
            'status' => $request->status,
        ]);

        // Log the change (could be enhanced with activity log)
        \Log::info("Super Admin updated tenant {$tenant->name} status to {$request->status}. Reason: {$request->reason}");

        // If suspending, optionally send notification
        if ($request->status === 'suspended') {
            // TODO: Send suspension notification email
        }

        return response()->json([
            'success' => true,
            'message' => 'Tenant status updated successfully',
            'data' => [
                'subscription' => $subscription->fresh()
            ]
        ]);
    }

    /**
     * Extend tenant trial or subscription
     */
    public function extend(Tenant $tenant, Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $subscription = $tenant->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant has no subscription'
            ], 404);
        }

        $subscription->extend($request->days);

        return response()->json([
            'success' => true,
            'message' => "Subscription extended by {$request->days} days",
            'data' => [
                'subscription' => $subscription->fresh(),
                'new_expiry' => $subscription->expires_at,
            ]
        ]);
    }

    /**
     * Delete tenant (soft delete if available)
     */
    public function destroy(Tenant $tenant)
    {
        // Check if tenant has active subscription
        $subscription = $tenant->subscription;
        if ($subscription && $subscription->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tenant with active subscription. Cancel subscription first.'
            ], 400);
        }

        // Delete associated users
        User::where('tenant_id', $tenant->id)->delete();

        // Delete tenant
        $tenant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }

    /**
     * Get tenant statistics
     */
    public function stats()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::whereHas('subscription', function ($q) {
            $q->whereIn('status', ['trial', 'active']);
        })->count();
        
        $trialTenants = Tenant::whereHas('subscription', function ($q) {
            $q->where('status', 'trial');
        })->count();

        $suspendedTenants = Tenant::whereHas('subscription', function ($q) {
            $q->where('status', 'suspended');
        })->count();

        $expiringSoon = Tenant::whereHas('subscription', function ($q) {
            $q->where('expires_at', '<=', Carbon::now()->addDays(7))
              ->whereIn('status', ['trial', 'active']);
        })->count();

        // New tenants this month
        $newThisMonth = Tenant::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // New tenants last month
        $newLastMonth = Tenant::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $growthRate = $newLastMonth > 0 
            ? round((($newThisMonth - $newLastMonth) / $newLastMonth) * 100, 2) 
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalTenants,
                'active' => $activeTenants,
                'trial' => $trialTenants,
                'suspended' => $suspendedTenants,
                'expiring_soon' => $expiringSoon,
                'new_this_month' => $newThisMonth,
                'growth_rate' => $growthRate,
            ]
        ]);
    }
}
