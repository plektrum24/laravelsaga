<?php

namespace App\Services;

use App\Models\ForecastTarget;
use App\Models\ForecastTargetItem;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForecastTargetService
{
    /**
     * Calculate forecast based on target revenue
     */
    public function calculateFromTarget($tenantId, $targetRevenue, $days)
    {
        // Get historical data (last 90 days)
        $historicalData = $this->getHistoricalData($tenantId, 90);
        
        // Calculate current trajectory
        $currentTrajectory = $this->projectCurrent($historicalData, $days);
        
        // Calculate gap
        $gap = $targetRevenue - $currentTrajectory;
        
        // Calculate required daily sales
        $requiredDailySales = $targetRevenue / $days;
        
        // Get product mix recommendations
        $productMix = $this->recommendProductMix($tenantId, $gap);
        
        // Calculate costs and profits
        $totalCost = $this->calculateTotalCost($productMix);
        $expectedProfit = $targetRevenue - $totalCost;
        $profitMargin = $targetRevenue > 0 ? ($expectedProfit / $targetRevenue) * 100 : 0;
        
        // Calculate break-even date
        $breakEvenDate = $this->calculateBreakEvenDate($tenantId, $targetRevenue);
        
        return [
            'target_revenue' => $targetRevenue,
            'current_trajectory' => $currentTrajectory,
            'gap' => $gap,
            'required_daily_sales' => $requiredDailySales,
            'product_mix' => $productMix,
            'total_cost' => $totalCost,
            'expected_profit' => $expectedProfit,
            'profit_margin' => round($profitMargin, 2),
            'break_even_date' => $breakEvenDate,
            'duration_days' => $days,
        ];
    }

    /**
     * Save forecast target
     */
    public function saveTarget($tenantId, $data)
    {
        DB::connection('tenant')->beginTransaction();
        
        try {
            // Create forecast target
            $forecastTarget = ForecastTarget::create([
                'tenant_id' => $tenantId,
                'target_revenue' => $data['target_revenue'],
                'target_duration_days' => $data['duration_days'] ?? 30,
                'current_trajectory' => $data['current_trajectory'] ?? 0,
                'gap' => $data['gap'] ?? 0,
                'status' => 'active',
                'generated_at' => now(),
            ]);
            
            // Create target items
            if (!empty($data['product_mix'])) {
                foreach ($data['product_mix'] as $item) {
                    ForecastTargetItem::create([
                        'forecast_target_id' => $forecastTarget->id,
                        'product_id' => $item['product_id'],
                        'recommended_qty' => $item['recommended_qty'] ?? 0,
                        'unit_cost' => $item['unit_cost'] ?? 0,
                        'total_cost' => $item['total_cost'] ?? 0,
                        'expected_revenue' => $item['expected_revenue'] ?? 0,
                        'expected_profit' => $item['expected_profit'] ?? 0,
                        'priority' => $item['priority'] ?? 3,
                    ]);
                }
            }
            
            DB::connection('tenant')->commit();
            
            return ['success' => true, 'target' => $forecastTarget];
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get historical sales data
     */
    private function getHistoricalData($tenantId, $days)
    {
        return DB::connection('tenant')
            ->table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('date', '>=', now()->subDays($days))
            ->selectRaw('DATE(date) as date, SUM(grand_total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Project current trajectory
     */
    private function projectCurrent($historicalData, $days)
    {
        if ($historicalData->count() < 7) {
            return 0;
        }

        // Average daily sales from last 7 days
        $recentData = $historicalData->take(7);
        $avgDailySales = $recentData->avg('total');
        
        return $avgDailySales * $days;
    }

    /**
     * Recommend product mix based on gap
     */
    private function recommendProductMix($tenantId, $gap)
    {
        // Get top-selling products by revenue
        $topProducts = DB::connection('tenant')
            ->table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('products.tenant_id', $tenantId)
            ->selectRaw('product_id, SUM(quantity) as total_qty, 
                        SUM(total) as total_revenue,
                        AVG(products.sell_price) as avg_price,
                        AVG(products.buy_price) as avg_cost')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(20)
            ->get();
        
        // Calculate recommended quantities
        $productMix = [];
        $remainingGap = $gap;
        
        foreach ($topProducts as $product) {
            if ($remainingGap <= 0) {
                break;
            }
            
            // Calculate how many units needed based on avg price
            $unitsNeeded = min(
                ceil($remainingGap / $product->avg_price),
                100 // Cap at 100 units per product
            );
            
            $revenue = $unitsNeeded * $product->avg_price;
            $cost = $unitsNeeded * $product->avg_cost;
            $profit = $revenue - $cost;
            
            $productMix[] = [
                'product_id' => $product->product_id,
                'recommended_qty' => $unitsNeeded,
                'unit_cost' => $product->avg_cost,
                'total_cost' => $cost,
                'expected_revenue' => $revenue,
                'expected_profit' => $profit,
                'priority' => count($productMix) + 1,
            ];
            
            $remainingGap -= $revenue;
        }
        
        return $productMix;
    }

    /**
     * Calculate total cost from product mix
     */
    private function calculateTotalCost($productMix)
    {
        return collect($productMix)->sum('total_cost');
    }

    /**
     * Calculate break-even date
     */
    private function calculateBreakEvenDate($tenantId, $targetRevenue)
    {
        // Get average daily revenue
        $avgDailyRevenue = DB::connection('tenant')
            ->table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('date', '>=', now()->subDays(30))
            ->avg('grand_total');
        
        if ($avgDailyRevenue <= 0) {
            return now()->addDays(30);
        }
        
        $daysToBreakEven = ceil($targetRevenue / $avgDailyRevenue);
        
        return now()->addDays($daysToBreakEven);
    }

    /**
     * Get active forecast target
     */
    public function getActiveTarget($tenantId)
    {
        return ForecastTarget::with('items.product')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->latest('generated_at')
            ->first();
    }
}
