<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\ForecastingService;
use Illuminate\Http\Request;

/**
 * ForecastingController
 * 
 * API endpoints for sales forecasting and trend analysis
 */
class ForecastingController extends Controller
{
    protected $forecastingService;

    public function __construct(ForecastingService $forecastingService)
    {
        $this->forecastingService = $forecastingService;
    }

    /**
     * Get sales forecast
     * 
     * GET /api/forecasting/sales
     */
    public function salesForecast(Request $request)
    {
        try {
            $days = $request->input('days', 7);
            $forecast = $this->forecastingService->forecastSales($days);

            return response()->json([
                'success' => true,
                'data' => $forecast,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate forecast: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sales trend
     * 
     * GET /api/forecasting/trend
     */
    public function salesTrend(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $trend = $this->forecastingService->getSalesTrend($days);

            return response()->json([
                'success' => true,
                'data' => $trend,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get trend: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get inventory forecast
     * 
     * GET /api/forecasting/inventory
     */
    public function inventoryForecast()
    {
        try {
            $forecast = $this->forecastingService->forecastInventory();

            return response()->json([
                'success' => true,
                'data' => $forecast,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate inventory forecast: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get category performance forecast
     * 
     * GET /api/forecasting/categories
     */
    public function categoryForecast()
    {
        try {
            $forecast = $this->forecastingService->forecastCategoryPerformance();

            return response()->json([
                'success' => true,
                'data' => $forecast,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get category forecast: ' . $e->getMessage(),
            ], 500);
        }
    }
}
