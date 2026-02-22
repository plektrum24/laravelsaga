<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\Invoice;
use App\Models\User;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::whereHas('subscription', function ($q) {
                $q->whereIn('status', ['trial', 'active']);
            })->count(),
            'suspended_tenants' => Tenant::whereHas('subscription', function ($q) {
                $q->where('status', 'suspended');
            })->count(),
            'total_revenue' => Invoice::where('status', 'paid')->sum('total'),
            'monthly_revenue' => Invoice::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total'),
            'total_invoices' => Invoice::count(),
            'unpaid_invoices' => Invoice::whereIn('status', ['draft', 'sent', 'overdue'])->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
            'open_tickets' => SupportTicket::whereIn('status', ['open', 'in_progress', 'waiting_customer'])->count(),
            'urgent_tickets' => SupportTicket::where('priority', 'urgent')->count(),
        ];

        // Revenue trend (last 6 months)
        $revenueTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthRevenue = Invoice::where('status', 'paid')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('total');
            
            $revenueTrend[] = [
                'month' => $date->format('M Y'),
                'revenue' => $monthRevenue,
            ];
        }

        // Tenant growth (last 6 months)
        $tenantGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthTenants = Tenant::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            
            $tenantGrowth[] = [
                'month' => $date->format('M Y'),
                'count' => $monthTenants,
            ];
        }

        // Subscription plan distribution
        $planDistribution = DB::table('tenant_subscriptions')
            ->join('subscription_plans', 'tenant_subscriptions.plan_id', '=', 'subscription_plans.id')
            ->select('subscription_plans.name', 'subscription_plans.code', DB::raw('COUNT(*) as count'))
            ->groupBy('subscription_plans.id', 'subscription_plans.name', 'subscription_plans.code')
            ->get();

        // Recent activity
        $recentTenants = Tenant::with(['subscription.plan'])
            ->latest()
            ->take(5)
            ->get();

        $recentTickets = SupportTicket::with(['tenant', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'revenue_trend' => $revenueTrend,
                'tenant_growth' => $tenantGrowth,
                'plan_distribution' => $planDistribution,
                'recent_tenants' => $recentTenants,
                'recent_tickets' => $recentTickets,
            ]
        ]);
    }

    /**
     * Get revenue details
     */
    public function revenue(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, yearly
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Invoice::where('status', 'paid');

        if ($startDate && $endDate) {
            $query->whereBetween('paid_at', [$startDate, $endDate]);
        }

        $revenue = [];
        
        if ($period === 'daily') {
            $revenue = $query->selectRaw('DATE(paid_at) as date, SUM(total) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        } elseif ($period === 'monthly') {
            $revenue = $query->selectRaw('DATE_FORMAT(paid_at, "%Y-%m") as month, SUM(total) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        $totalRevenue = $revenue->sum('total');
        $averageRevenue = $revenue->avg('total') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'revenue' => $revenue,
                'total' => $totalRevenue,
                'average' => $averageRevenue,
                'period' => $period,
            ]
        ]);
    }

    /**
     * Get system usage statistics
     */
    public function usage()
    {
        $totalUsers = User::count();
        $totalTenants = Tenant::count();
        
        // Database usage per tenant
        $databaseUsage = DB::table('information_schema.tables')
            ->select('table_schema', DB::raw('SUM(data_length + index_length) / 1024 / 1024 as size_mb'))
            ->where('table_schema', 'like', 'tenant_%')
            ->groupBy('table_schema')
            ->orderByDesc('size_mb')
            ->limit(10)
            ->get();

        // Active subscriptions
        $activeSubscriptions = TenantSubscription::whereIn('status', ['trial', 'active'])->count();
        $expiringSoon = TenantSubscription::where('expires_at', '<=', Carbon::now()->addDays(7))
            ->whereIn('status', ['trial', 'active'])
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'total_tenants' => $totalTenants,
                'active_subscriptions' => $activeSubscriptions,
                'expiring_soon' => $expiringSoon,
                'database_usage' => $databaseUsage,
            ]
        ]);
    }
}
