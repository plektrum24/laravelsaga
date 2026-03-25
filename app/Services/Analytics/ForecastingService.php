<?php

namespace App\Services\Analytics;

use App\Models\Transaction;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ForecastingService
 * 
 * Provides sales forecasting and trend analysis
 */
class ForecastingService
{
    /**
     * Forecast sales for next period using simple moving average
     */
    public function forecastSales(int $days = 7)
    {
        // Get last 30 days of sales
        $historicalData = Transaction::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as daily_revenue'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($historicalData->isEmpty()) {
            return [
                'forecast' => [],
                'message' => 'Insufficient data for forecasting',
            ];
        }

        // Calculate moving average (7 days)
        $movingAverage = $this->calculateMovingAverage($historicalData, 7);
        
        // Generate forecast
        $forecast = [];
        $lastValue = $movingAverage->last();
        
        for ($i = 1; $i <= $days; $i++) {
            $forecastDate = Carbon::now()->addDays($i);
            $forecast[] = [
                'date' => $forecastDate->format('Y-m-d'),
                'date_formatted' => $forecastDate->format('d M Y'),
                'forecasted_revenue' => $lastValue['daily_revenue'] ?? 0,
                'forecasted_transactions' => round($lastValue['transaction_count'] ?? 0),
                'confidence' => $this->calculateConfidence($historicalData),
            ];
        }

        // Calculate summary
        $totalForecast = array_sum(array_column($forecast, 'forecasted_revenue'));
        $avgDaily = $totalForecast / $days;

        return [
            'forecast' => $forecast,
            'summary' => [
                'total_forecasted_revenue' => $totalForecast,
                'average_daily_revenue' => $avgDaily,
                'forecast_period' => $days . ' days',
                'confidence_level' => $this->calculateConfidence($historicalData),
            ],
            'historical_average' => [
                'daily_revenue' => $historicalData->avg('daily_revenue'),
                'daily_transactions' => $historicalData->avg('transaction_count'),
            ],
        ];
    }

    /**
     * Calculate moving average
     */
    private function calculateMovingAverage($data, int $period = 7)
    {
        $movingAverage = [];
        
        for ($i = 0; $i < $data->count(); $i++) {
            if ($i < $period - 1) {
                continue; // Not enough data points
            }

            $slice = $data->slice($i - $period + 1, $period);
            $avgRevenue = $slice->avg('daily_revenue');
            $avgTransactions = $slice->avg('transaction_count');

            $movingAverage[] = [
                'date' => $data[$i]['date'],
                'daily_revenue' => $avgRevenue,
                'transaction_count' => $avgTransactions,
            ];
        }

        return collect($movingAverage);
    }

    /**
     * Calculate forecast confidence (simple standard deviation based)
     */
    private function calculateConfidence($data)
    {
        if ($data->count() < 7) {
            return 'Low';
        }

        $revenues = $data->pluck('daily_revenue')->toArray();
        $avg = array_sum($revenues) / count($revenues);
        $variance = array_sum(array_map(function ($r) use ($avg) {
            return pow($r - $avg, 2);
        }, $revenues)) / count($revenues);
        $stdDev = sqrt($variance);
        $cv = $avg > 0 ? ($stdDev / $avg) * 100 : 100; // Coefficient of variation

        if ($cv < 20) {
            return 'High';
        } elseif ($cv < 40) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }

    /**
     * Get sales trend analysis
     */
    public function getSalesTrend(int $days = 30)
    {
        $trend = Transaction::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as transactions')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate trend direction
        $firstHalf = $trend->take(floor($trend->count() / 2));
        $secondHalf = $trend->skip(floor($trend->count() / 2));

        $firstAvg = $firstHalf->avg('revenue') ?? 0;
        $secondAvg = $secondHalf->avg('revenue') ?? 0;

        $trendDirection = $secondAvg > $firstAvg ? 'up' : ($secondAvg < $firstAvg ? 'down' : 'stable');
        $trendPercentage = $firstAvg > 0 ? (($secondAvg - $firstAvg) / $firstAvg) * 100 : 0;

        return [
            'trend_data' => $trend,
            'trend_direction' => $trendDirection,
            'trend_percentage' => round($trendPercentage, 2),
            'summary' => [
                'first_half_avg' => $firstAvg,
                'second_half_avg' => $secondAvg,
                'change' => $secondAvg - $firstAvg,
            ],
        ];
    }

    /**
     * Get inventory forecast (when to restock)
     */
    public function forecastInventory()
    {
        // Get products with sales velocity
        $products = DB::table('products')
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->where('transactions.created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.stock',
                'products.min_stock',
                DB::raw('COALESCE(SUM(transaction_items.qty), 0) as sold_30d'),
                DB::raw('COALESCE(AVG(transaction_items.qty), 0) as avg_daily_sale')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.stock', 'products.min_stock')
            ->havingRaw('sold_30d > 0')
            ->orderBy('avg_daily_sale', 'desc')
            ->limit(50)
            ->get();

        // Calculate days until stockout
        $forecast = $products->map(function ($product) {
            $avgDailySale = $product->avg_daily_sale / 30; // Convert to daily
            $daysUntilStockout = $avgDailySale > 0 ? floor($product->stock / $avgDailySale) : 999;
            $restockDate = $daysUntilStockout < 999 ? Carbon::now()->addDays($daysUntilStockout) : null;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'current_stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'avg_daily_sale' => round($avgDailySale, 2),
                'days_until_stockout' => $daysUntilStockout,
                'restock_date' => $restockDate ? $restockDate->format('d M Y') : 'N/A',
                'priority' => $daysUntilStockout <= 7 ? 'High' : ($daysUntilStockout <= 14 ? 'Medium' : 'Low'),
            ];
        });

        return [
            'forecast' => $forecast,
            'high_priority_count' => $forecast->where('priority', 'High')->count(),
            'medium_priority_count' => $forecast->where('priority', 'Medium')->count(),
        ];
    }

    /**
     * Get category performance forecast
     */
    public function forecastCategoryPerformance()
    {
        $categories = DB::table('categories')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->where('transactions.created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(DISTINCT products.id) as product_count'),
                DB::raw('SUM(transaction_items.qty) as total_sold'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return [
            'categories' => $categories,
            'top_category' => $categories->first(),
            'total_categories' => $categories->count(),
        ];
    }
}
