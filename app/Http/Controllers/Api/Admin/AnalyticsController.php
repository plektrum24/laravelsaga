<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * GET /api/admin/analytics/overview
     * Get global overview statistics
     */
    public function overview()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $totalUsers = User::where('role', '!=', 'super_admin')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'tenants' => [
                    'total' => $totalTenants,
                    'active' => $activeTenants,
                ],
                'users' => [
                    'total' => $totalUsers,
                ],
            ],
        ]);
    }

    /**
     * GET /api/admin/analytics/revenue
     * Get combined revenue from all tenants
     */
    public function revenue(Request $request)
    {
        $period = $request->get('period', 'week');

        // Calculate date range
        $now = now();
        if ($period === 'week') {
            $startDate = $now->copy()->subDays(7);
        } elseif ($period === 'month') {
            $startDate = $now->copy()->startOfMonth();
        } else {
            $startDate = $now->copy()->startOfYear();
        }

        // For now, return mock data since cross-tenant DB queries are complex
        // In production, this would query each tenant's database
        $tenants = Tenant::where('status', 'active')->get();

        $revenueByTenant = $tenants->map(function ($tenant) {
            return [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'revenue' => 0, // Would query tenant DB
                'transactions' => 0,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'totalRevenue' => 0,
                'totalTransactions' => 0,
                'revenueByTenant' => $revenueByTenant,
                'dailyRevenue' => [],
                'topProducts' => [],
                'period' => $period,
            ],
        ]);
    }

    /**
     * GET /api/admin/analytics/tenants-map
     * Get tenant locations for map display
     */
    public function tenantsMap()
    {
        $tenants = Tenant::where('status', 'active')
            ->get(['id', 'name', 'code', 'address', 'status']);

        return response()->json([
            'success' => true,
            'data' => $tenants,
        ]);
    }
}
