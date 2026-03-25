<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\RealtimeService;
use Illuminate\Http\Request;

/**
 * RealtimeController
 * 
 * API endpoints for real-time analytics
 */
class RealtimeController extends Controller
{
    protected $realtimeService;

    public function __construct(RealtimeService $realtimeService)
    {
        $this->realtimeService = $realtimeService;
    }

    /**
     * Get real-time dashboard data
     * 
     * GET /api/analytics/realtime
     */
    public function index()
    {
        try {
            $data = $this->realtimeService->getDashboardSummary();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch real-time data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get live sales feed
     * 
     * GET /api/analytics/sales/live
     */
    public function liveSales()
    {
        try {
            $sales = $this->realtimeService->getLiveSales();

            return response()->json([
                'success' => true,
                'data' => $sales,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch live sales: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active users
     * 
     * GET /api/analytics/users/active
     */
    public function activeUsers()
    {
        try {
            $users = $this->realtimeService->getActiveUsers();

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active users: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get revenue today
     * 
     * GET /api/analytics/revenue/today
     */
    public function revenueToday()
    {
        try {
            $revenue = $this->realtimeService->getRevenueToday();

            return response()->json([
                'success' => true,
                'data' => $revenue,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch revenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get hourly statistics
     * 
     * GET /api/analytics/stats/hourly
     */
    public function hourlyStats()
    {
        try {
            $stats = $this->realtimeService->getHourlyStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hourly stats: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get top products
     * 
     * GET /api/analytics/products/top
     */
    public function topProducts()
    {
        try {
            $products = $this->realtimeService->getTopProducts();

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch top products: ' . $e->getMessage(),
            ], 500);
        }
    }
}
