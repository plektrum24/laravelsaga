<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerSegment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerAnalyticsService
{
    /**
     * Calculate RFM segmentation for all customers
     */
    public function calculateRFM($tenantId)
    {
        $customers = Customer::where('tenant_id', $tenantId)->get();
        
        $rfmResults = [];
        
        foreach ($customers as $customer) {
            $rfm = $this->getCustomerRFM($customer);
            $segment = $this->getRFMSegment($rfm['r_score'], $rfm['m_score'], $rfm['f_score']);
            
            $rfmResults[] = [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'recency_days' => $rfm['recency_days'],
                'frequency' => $rfm['frequency'],
                'monetary' => $rfm['monetary'],
                'r_score' => $rfm['r_score'],
                'f_score' => $rfm['f_score'],
                'm_score' => $rfm['m_score'],
                'rfm_score' => $rfm['r_score'] * 100 + $rfm['f_score'] * 10 + $rfm['m_score'],
                'segment' => $segment['segment'],
                'description' => $segment['description'],
            ];
            
            // Save to database
            CustomerSegment::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'customer_id' => $customer->id,
                    'segment_type' => 'RFM',
                ],
                [
                    'segment_value' => $segment['segment'],
                    'score' => $rfm['r_score'] * 10 + $rfm['f_score'] * 5 + $rfm['m_score'],
                    'metadata' => $rfm,
                    'calculated_at' => now(),
                ]
            );
        }
        
        return $rfmResults;
    }

    /**
     * Get RFM metrics for a customer
     */
    private function getCustomerRFM($customer)
    {
        $transactions = Transaction::where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->orderBy('date', 'desc')
            ->get();
        
        if ($transactions->isEmpty()) {
            return [
                'recency_days' => 999,
                'frequency' => 0,
                'monetary' => 0,
                'r_score' => 1,
                'f_score' => 1,
                'm_score' => 1,
            ];
        }
        
        // Recency: Days since last purchase
        $recencyDays = Carbon::parse($transactions->first()->date)->diffInDays();
        
        // Frequency: Total number of purchases
        $frequency = $transactions->count();
        
        // Monetary: Total spend
        $monetary = $transactions->sum('grand_total');
        
        // Score each metric (1-5 scale)
        $rScore = $this->scoreRecency($recencyDays);
        $fScore = $this->scoreFrequency($frequency);
        $mScore = $this->scoreMonetary($monetary);
        
        return [
            'recency_days' => $recencyDays,
            'frequency' => $frequency,
            'monetary' => round($monetary, 2),
            'r_score' => $rScore,
            'f_score' => $fScore,
            'm_score' => $mScore,
        ];
    }

    /**
     * Score recency (1-5)
     */
    private function scoreRecency($days)
    {
        if ($days <= 7) return 5;
        if ($days <= 30) return 4;
        if ($days <= 60) return 3;
        if ($days <= 90) return 2;
        return 1;
    }

    /**
     * Score frequency (1-5)
     */
    private function scoreFrequency($frequency)
    {
        if ($frequency >= 20) return 5;
        if ($frequency >= 10) return 4;
        if ($frequency >= 5) return 3;
        if ($frequency >= 2) return 2;
        return 1;
    }

    /**
     * Score monetary (1-5)
     */
    private function scoreMonetary($monetary)
    {
        if ($monetary >= 5000000) return 5;
        if ($monetary >= 2000000) return 4;
        if ($monetary >= 1000000) return 3;
        if ($monetary >= 500000) return 2;
        return 1;
    }

    /**
     * Get RFM segment name and description
     */
    private function getRFMSegment($r, $f, $m)
    {
        $segments = [
            '555' => ['segment' => 'Champions', 'description' => 'Best customers - buy frequently, spend heavily'],
            '554' => ['segment' => 'Champions', 'description' => 'Best customers - buy frequently, spend heavily'],
            '545' => ['segment' => 'Champions', 'description' => 'Best customers - buy frequently, spend heavily'],
            
            '544' => ['segment' => 'Loyal Customers', 'description' => 'Reliable repeat customers with good spending'],
            '543' => ['segment' => 'Loyal Customers', 'description' => 'Reliable repeat customers with good spending'],
            '454' => ['segment' => 'Loyal Customers', 'description' => 'Reliable repeat customers with good spending'],
            
            '535' => ['segment' => 'Big Spenders', 'description' => 'High spenders but less frequent'],
            '534' => ['segment' => 'Big Spenders', 'description' => 'High spenders but less frequent'],
            
            '455' => ['segment' => 'Recent Customers', 'description' => 'Bought recently but need nurturing'],
            '445' => ['segment' => 'Recent Customers', 'description' => 'Bought recently but need nurturing'],
            
            '355' => ['segment' => 'At Risk', 'description' => 'Previously good customers who haven\'t bought recently'],
            '354' => ['segment' => 'At Risk', 'description' => 'Previously good customers who haven\'t bought recently'],
            '255' => ['segment' => 'At Risk', 'description' => 'Previously good customers who haven\'t bought recently'],
            
            '225' => ['segment' => 'Lost', 'description' => 'Low engagement, haven\'t bought in a long time'],
            '115' => ['segment' => 'Lost', 'description' => 'Low engagement, haven\'t bought in a long time'],
            '111' => ['segment' => 'Lost', 'description' => 'Low engagement, haven\'t bought in a long time'],
        ];
        
        $key = $r . $f . $m;
        
        return $segments[$key] ?? [
            'segment' => 'Regular',
            'description' => 'Average customer with moderate engagement'
        ];
    }

    /**
     * Calculate Customer Lifetime Value
     */
    public function calculateCLV($tenantId)
    {
        $customers = Customer::where('tenant_id', $tenantId)->get();
        
        $clvResults = [];
        
        foreach ($customers as $customer) {
            $clv = $this->getCustomerCLV($customer);
            
            $clvResults[] = array_merge([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
            ], $clv);
            
            // Save CLV segment
            CustomerSegment::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'customer_id' => $customer->id,
                    'segment_type' => 'CLV',
                ],
                [
                    'segment_value' => $clv['clv_tier'],
                    'score' => $clv['clv'],
                    'metadata' => $clv,
                    'calculated_at' => now(),
                ]
            );
        }
        
        return $clvResults;
    }

    /**
     * Get CLV for a customer
     */
    private function getCustomerCLV($customer)
    {
        $transactions = Transaction::where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->orderBy('date')
            ->get();
        
        if ($transactions->isEmpty()) {
            return [
                'avg_order_value' => 0,
                'purchase_frequency' => 0,
                'customer_lifespan_months' => 0,
                'clv' => 0,
                'clv_tier' => 'Unknown',
            ];
        }
        
        // Average Order Value
        $avgOrderValue = $transactions->avg('grand_total');
        
        // Purchase Frequency (purchases per month)
        $firstPurchase = Carbon::parse($transactions->first()->date);
        $lastPurchase = Carbon::parse($transactions->last()->date);
        $customerAgeMonths = max(1, $firstPurchase->diffInMonths($lastPurchase));
        $purchaseFrequency = $transactions->count() / $customerAgeMonths;
        
        // Customer Lifespan (average customer lifespan in months)
        $customerLifespan = 12; // Default 1 year, can be calibrated
        
        // CLV Calculation
        $clv = $avgOrderValue * $purchaseFrequency * $customerLifespan;
        
        // CLV Tier
        $clvTier = $this->getCLVTier($clv);
        
        return [
            'avg_order_value' => round($avgOrderValue, 2),
            'purchase_frequency' => round($purchaseFrequency, 2),
            'customer_lifespan_months' => $customerLifespan,
            'clv' => round($clv, 2),
            'clv_tier' => $clvTier,
        ];
    }

    /**
     * Get CLV tier
     */
    private function getCLVTier($clv)
    {
        if ($clv >= 10000000) return 'Platinum';
        if ($clv >= 5000000) return 'Gold';
        if ($clv >= 2000000) return 'Silver';
        if ($clv >= 500000) return 'Bronze';
        return 'Standard';
    }

    /**
     * Perform cohort analysis
     */
    public function cohortAnalysis($tenantId, $period = 'month')
    {
        // Get customers grouped by cohort (first purchase date)
        $cohorts = DB::connection('tenant')
            ->table('customers')
            ->join('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->where('customers.tenant_id', $tenantId)
            ->where('transactions.status', 'completed')
            ->selectRaw('customers.id, MIN(transactions.date) as cohort_date')
            ->groupBy('customers.id')
            ->get();
        
        // Group by cohort period
        $cohortGroups = $cohorts->groupBy(function($customer) use ($period) {
            $date = Carbon::parse($customer->cohort_date);
            return $period === 'month' ? 
                $date->format('Y-m') : 
                $date->format('Y-W');
        });
        
        $analysis = [];
        
        foreach ($cohortGroups as $cohortPeriod => $customers) {
            $cohortSize = $customers->count();
            
            // Calculate retention for each period after cohort
            $retention = [];
            for ($i = 0; $i <= 12; $i++) {
                $periodDate = Carbon::parse($cohortPeriod . '-01')->addMonths($i);
                
                $activeCustomers = DB::connection('tenant')
                    ->table('transactions')
                    ->whereIn('customer_id', $customers->pluck('id'))
                    ->where('status', 'completed')
                    ->whereYear('date', $periodDate->year)
                    ->whereMonth('date', $periodDate->month)
                    ->distinct('customer_id')
                    ->count('customer_id');
                
                $retentionRate = $cohortSize > 0 ? ($activeCustomers / $cohortSize) * 100 : 0;
                
                $retention[] = [
                    'period' => $i,
                    'active_customers' => $activeCustomers,
                    'retention_rate' => round($retentionRate, 2),
                ];
            }
            
            $analysis[] = [
                'cohort' => $cohortPeriod,
                'cohort_size' => $cohortSize,
                'retention' => $retention,
            ];
        }
        
        return $analysis;
    }

    /**
     * Generate automated report
     */
    public function generateReport($tenantId, $reportType, $filters = [])
    {
        switch ($reportType) {
            case 'sales_summary':
                return $this->generateSalesSummaryReport($tenantId, $filters);
            case 'customer_report':
                return $this->generateCustomerReport($tenantId, $filters);
            case 'inventory_report':
                return $this->generateInventoryReport($tenantId, $filters);
            default:
                return null;
        }
    }

    /**
     * Generate sales summary report
     */
    private function generateSalesSummaryReport($tenantId, $filters)
    {
        $from = $filters['from'] ?? now()->startOfMonth();
        $to = $filters['to'] ?? now()->endOfDay();
        
        $salesData = DB::connection('tenant')
            ->table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(grand_total) as total_revenue,
                AVG(grand_total) as avg_order_value,
                COUNT(DISTINCT customer_id) as unique_customers
            ')
            ->first();
        
        return [
            'report_type' => 'sales_summary',
            'period' => [
                'from' => $from,
                'to' => $to,
            ],
            'data' => [
                'total_orders' => $salesData->total_orders,
                'total_revenue' => $salesData->total_revenue,
                'avg_order_value' => $salesData->avg_order_value,
                'unique_customers' => $salesData->unique_customers,
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Generate customer report
     */
    private function generateCustomerReport($tenantId, $filters)
    {
        $totalCustomers = Customer::where('tenant_id', $tenantId)->count();
        
        $newCustomers = Customer::where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $filters['from'] ?? now()->startOfMonth())
            ->whereDate('created_at', '<=', $filters['to'] ?? now()->endOfDay())
            ->count();
        
        $activeCustomers = DB::connection('tenant')
            ->table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('date', [$filters['from'] ?? now()->startOfMonth(), $filters['to'] ?? now()->endOfDay()])
            ->distinct('customer_id')
            ->count('customer_id');
        
        return [
            'report_type' => 'customer_report',
            'period' => [
                'from' => $filters['from'] ?? now()->startOfMonth(),
                'to' => $filters['to'] ?? now()->endOfDay(),
            ],
            'data' => [
                'total_customers' => $totalCustomers,
                'new_customers' => $newCustomers,
                'active_customers' => $activeCustomers,
                'customer_growth' => $totalCustomers > 0 ? (($newCustomers / $totalCustomers) * 100) : 0,
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Generate inventory report
     */
    private function generateInventoryReport($tenantId, $filters)
    {
        $totalProducts = DB::connection('tenant')
            ->table('products')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();
        
        $lowStock = DB::connection('tenant')
            ->table('products')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();
        
        $outOfStock = DB::connection('tenant')
            ->table('products')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('stock', '<=', 0)
            ->count();
        
        $totalValue = DB::connection('tenant')
            ->table('products')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->selectRaw('SUM(stock * buy_price) as total_value')
            ->value('total_value') ?? 0;
        
        return [
            'report_type' => 'inventory_report',
            'period' => [
                'from' => $filters['from'] ?? now()->startOfMonth(),
                'to' => $filters['to'] ?? now()->endOfDay(),
            ],
            'data' => [
                'total_products' => $totalProducts,
                'low_stock_products' => $lowStock,
                'out_of_stock_products' => $outOfStock,
                'total_inventory_value' => $totalValue,
            ],
            'generated_at' => now(),
        ];
    }
}
