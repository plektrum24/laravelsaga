<?php

namespace App\Services;

use App\Models\SalesForecast;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForecastingService
{
    /**
     * Generate sales forecast for next N days
     */
    public function generateSalesForecast($tenantId, $days = 30)
    {
        // Get historical sales data (last 90 days)
        $historicalData = $this->getHistoricalSales($tenantId, 90);
        
        if ($historicalData->count() < 30) {
            return ['success' => false, 'message' => 'Insufficient historical data'];
        }

        // Calculate forecast using moving average with trend adjustment
        $forecasts = [];
        $lastDate = $historicalData->max('date');
        
        for ($i = 1; $i <= $days; $i++) {
            $forecastDate = Carbon::parse($lastDate)->addDays($i);
            
            // Simple moving average (last 7 days for weekly pattern)
            $recentSales = $historicalData
                ->filter(fn($item) => Carbon::parse($item->date)->diffInDays($forecastDate) <= 7)
                ->avg('total');
            
            // Apply trend adjustment
            $trend = $this->calculateTrend($historicalData);
            $adjustedForecast = $recentSales * (1 + ($trend * $i));
            
            // Apply day-of-week adjustment
            $dowFactor = $this->getDayOfWeekFactor($historicalData, $forecastDate->dayOfWeek);
            $finalForecast = $adjustedForecast * $dowFactor;
            
            $forecasts[] = [
                'tenant_id' => $tenantId,
                'forecast_date' => $forecastDate,
                'predicted_sales' => max(0, $finalForecast),
                'confidence_score' => $this->calculateConfidence($historicalData, $i),
                'model_version' => 'v1.0-moving-avg',
            ];
        }

        // Save forecasts
        foreach ($forecasts as $forecastData) {
            SalesForecast::updateOrCreate(
                [
                    'tenant_id' => $forecastData['tenant_id'],
                    'forecast_date' => $forecastData['forecast_date'],
                ],
                $forecastData
            );
        }

        return ['success' => true, 'forecasts' => $forecasts];
    }

    /**
     * Get historical sales data
     */
    private function getHistoricalSales($tenantId, $days)
    {
        return DB::connection('tenant')
            ->table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('date', '>=', now()->subDays($days))
            ->selectRaw('DATE(date) as date, SUM(grand_total) as total, COUNT(*) as order_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Calculate trend from historical data
     */
    private function calculateTrend($historicalData)
    {
        if ($historicalData->count() < 2) {
            return 0;
        }

        $firstHalf = $historicalData->take($historicalData->count() / 2)->avg('total');
        $secondHalf = $historicalData->skip($historicalData->count() / 2)->avg('total');
        
        if ($firstHalf == 0) {
            return 0;
        }

        return ($secondHalf - $firstHalf) / $firstHalf / ($historicalData->count() / 2);
    }

    /**
     * Get day of week adjustment factor
     */
    private function getDayOfWeekFactor($historicalData, $dayOfWeek)
    {
        $avgOverall = $historicalData->avg('total');
        
        $dayAvg = $historicalData
            ->filter(fn($item) => Carbon::parse($item->date)->dayOfWeek == $dayOfWeek)
            ->avg('total');
        
        if ($avgOverall == 0 || $dayAvg == null) {
            return 1;
        }

        return $dayAvg / $avgOverall;
    }

    /**
     * Calculate confidence score
     */
    private function calculateConfidence($historicalData, $daysAhead)
    {
        // Confidence decreases as we forecast further into future
        $baseConfidence = 95;
        $decayRate = 1.5; // Confidence drops 1.5% per day
        
        $confidence = max(50, $baseConfidence - ($daysAhead * $decayRate));
        
        // Adjust based on data volatility
        $volatility = $historicalData->std('total') / $historicalData->avg('total');
        $confidence *= (1 - min(0.3, $volatility));
        
        return round($confidence, 2);
    }

    /**
     * Predict demand for products
     */
    public function predictDemand($tenantId, $days = 30)
    {
        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $predictions = [];

        foreach ($products as $product) {
            $historicalSales = $this->getProductHistoricalSales($product->id, 90);
            
            if ($historicalSales->count() < 14) {
                continue;
            }

            $avgDailySales = $historicalSales->avg('qty');
            $stdDev = $historicalSales->std('qty') ?? 0;
            
            // Predict demand with safety stock
            $predictedDemand = $avgDailySales * $days;
            $safetyStock = $stdDev * sqrt($days) * 1.65; // 95% service level
            
            $predictions[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'predicted_demand' => round($predictedDemand),
                'safety_stock' => round($safetyStock),
                'reorder_point' => round($predictedDemand + $safetyStock),
                'current_stock' => $product->stock,
                'recommendation' => $this->getReorderRecommendation($product->stock, $predictedDemand + $safetyStock),
            ];
        }

        return $predictions;
    }

    /**
     * Get product historical sales
     */
    private function getProductHistoricalSales($productId, $days)
    {
        return DB::connection('tenant')
            ->table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transaction_items.product_id', $productId)
            ->where('transactions.status', 'completed')
            ->whereDate('transactions.date', '>=', now()->subDays($days))
            ->selectRaw('DATE(transactions.date) as date, SUM(transaction_items.qty) as qty')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get reorder recommendation
     */
    private function getReorderRecommendation($currentStock, $reorderPoint)
    {
        if ($currentStock <= 0) {
            return 'URGENT: Out of stock!';
        } elseif ($currentStock <= $reorderPoint * 0.25) {
            return 'CRITICAL: Order immediately';
        } elseif ($currentStock <= $reorderPoint * 0.5) {
            return 'WARNING: Order soon';
        } elseif ($currentStock <= $reorderPoint) {
            return 'REORDER: Stock running low';
        } else {
            return 'OK: Stock adequate';
        }
    }

    /**
     * Predict customer churn
     */
    public function predictChurn($tenantId)
    {
        $customers = DB::connection('tenant')
            ->table('customers')
            ->where('tenant_id', $tenantId)
            ->get();

        $churnPredictions = [];

        foreach ($customers as $customer) {
            $lastPurchase = DB::connection('tenant')
                ->table('transactions')
                ->where('customer_id', $customer->id)
                ->where('status', 'completed')
                ->max('date');

            $daysSinceLastPurchase = $lastPurchase ? 
                Carbon::parse($lastPurchase)->diffInDays() : 999;

            $totalPurchases = DB::connection('tenant')
                ->table('transactions')
                ->where('customer_id', $customer->id)
                ->where('status', 'completed')
                ->count();

            $churnScore = $this->calculateChurnScore($daysSinceLastPurchase, $totalPurchases);
            $churnRisk = $this->getChurnRiskLevel($churnScore);

            $churnPredictions[] = [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'days_since_last_purchase' => $daysSinceLastPurchase,
                'total_purchases' => $totalPurchases,
                'churn_score' => $churnScore,
                'churn_risk' => $churnRisk,
                'recommendation' => $this->getChurnRecommendation($churnRisk),
            ];
        }

        // Sort by churn score (highest risk first)
        usort($churnPredictions, fn($a, $b) => $b['churn_score'] <=> $a['churn_score']);

        return $churnPredictions;
    }

    /**
     * Calculate churn score (0-100)
     */
    private function calculateChurnScore($daysSinceLastPurchase, $totalPurchases)
    {
        // Base score from days since last purchase
        $daysScore = min(100, $daysSinceLastPurchase * 2);
        
        // Adjust for purchase frequency
        $frequencyAdjustment = 0;
        if ($totalPurchases > 10) {
            $frequencyAdjustment = -20; // Loyal customers less likely to churn
        } elseif ($totalPurchases > 5) {
            $frequencyAdjustment = -10;
        } elseif ($totalPurchases <= 1) {
            $frequencyAdjustment = 20; // New customers more likely to churn
        }

        return max(0, min(100, $daysScore + $frequencyAdjustment));
    }

    /**
     * Get churn risk level
     */
    private function getChurnRiskLevel($churnScore)
    {
        if ($churnScore >= 80) {
            return 'Critical';
        } elseif ($churnScore >= 60) {
            return 'High';
        } elseif ($churnScore >= 40) {
            return 'Medium';
        } elseif ($churnScore >= 20) {
            return 'Low';
        } else {
            return 'Very Low';
        }
    }

    /**
     * Get churn prevention recommendation
     */
    private function getChurnRecommendation($churnRisk)
    {
        switch ($churnRisk) {
            case 'Critical':
                return 'Immediate action: Send personalized offer + call customer';
            case 'High':
                return 'Send win-back campaign with discount';
            case 'Medium':
                return 'Send engagement email with new products';
            case 'Low':
                return 'Send loyalty rewards update';
            default:
                return 'Continue regular engagement';
        }
    }

    /**
     * Get stock optimization recommendations
     */
    public function optimizeStock($tenantId)
    {
        $predictions = $this->predictDemand($tenantId, 30);
        
        $recommendations = [];
        
        foreach ($predictions as $prediction) {
            if ($prediction['current_stock'] < $prediction['reorder_point']) {
                $recommendations[] = [
                    'product_id' => $prediction['product_id'],
                    'product_name' => $prediction['product_name'],
                    'action' => 'RESTOCK',
                    'quantity_to_order' => round($prediction['reorder_point'] - $prediction['current_stock'] + $prediction['predicted_demand']),
                    'urgency' => $prediction['current_stock'] <= 0 ? 'URGENT' : 'NORMAL',
                    'reason' => $prediction['recommendation'],
                ];
            } elseif ($prediction['current_stock'] > $prediction['predicted_demand'] * 3) {
                $recommendations[] = [
                    'product_id' => $prediction['product_id'],
                    'product_name' => $prediction['product_name'],
                    'action' => 'REDUCE_STOCK',
                    'quantity_to_reduce' => round($prediction['current_stock'] - ($prediction['predicted_demand'] * 2)),
                    'urgency' => 'LOW',
                    'reason' => 'Overstocked - consider promotion',
                ];
            }
        }

        return $recommendations;
    }
}
