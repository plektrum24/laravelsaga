<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UsageController extends Controller
{
    /**
     * Get current usage statistics
     */
    public function current()
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $currentPeriod = TenantUsage::forTenant($tenant->id)
            ->currentPeriod()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $currentPeriod
        ]);
    }

    /**
     * Get usage history
     */
    public function history(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $months = $request->get('months', 6);
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();

        $history = TenantUsage::forTenant($tenant->id)
            ->where('period_start', '>=', $startDate)
            ->orderBy('period_start', 'desc')
            ->get()
            ->groupBy('metric');

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Get specific metric usage
     */
    public function metric($metric)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $usage = TenantUsage::forTenant($tenant->id)
            ->metric($metric)
            ->currentPeriod()
            ->first();

        if (!$usage) {
            return response()->json([
                'success' => false,
                'message' => 'Usage data not found for this metric'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $usage
        ]);
    }

    /**
     * Check if tenant is over limit
     */
    public function checkLimits()
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $overLimit = TenantUsage::forTenant($tenant->id)
            ->currentPeriod()
            ->get()
            ->filter(function ($usage) {
                return $usage->isOverLimit();
            });

        return response()->json([
            'success' => true,
            'data' => [
                'is_over_limit' => $overLimit->isNotEmpty(),
                'over_limit_metrics' => $overLimit,
            ]
        ]);
    }
}
