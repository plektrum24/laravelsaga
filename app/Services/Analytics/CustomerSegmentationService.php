<?php

namespace App\Services\Analytics;

use App\Models\Customer;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * CustomerSegmentationService
 * 
 * Provides customer segmentation, CLV calculation, and churn prediction
 */
class CustomerSegmentationService
{
    /**
     * Perform RFM (Recency, Frequency, Monetary) analysis
     */
    public function rfmAnalysis()
    {
        $now = Carbon::now();

        $customers = DB::table('customers')
            ->leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->where('transactions.status', 'completed')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                DB::raw('DATEDIFF(' . $now->toDateString() . ', MAX(transactions.created_at)) as recency'),
                DB::raw('COUNT(transactions.id) as frequency'),
                DB::raw('SUM(transactions.total_amount) as monetary')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email')
            ->havingRaw('COUNT(transactions.id) > 0')
            ->orderBy('monetary', 'desc')
            ->get();

        // Calculate RFM scores
        $recencyScores = $this->calculateQuartileScores($customers->pluck('recency')->sortDesc()->values()->all(), true);
        $frequencyScores = $this->calculateQuartileScores($customers->pluck('frequency')->sortDesc()->values()->all(), false);
        $monetaryScores = $this->calculateQuartileScores($customers->pluck('monetary')->sortDesc()->values()->all(), false);

        $segmentedCustomers = $customers->map(function ($customer, $index) use ($recencyScores, $frequencyScores, $monetaryScores) {
            $rScore = $recencyScores[$index] ?? 1;
            $fScore = $frequencyScores[$index] ?? 1;
            $mScore = $monetaryScores[$index] ?? 1;
            $rfmScore = $rScore + $fScore + $mScore;

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'recency' => $customer->recency,
                'frequency' => $customer->frequency,
                'monetary' => $customer->monetary,
                'r_score' => $rScore,
                'f_score' => $fScore,
                'm_score' => $mScore,
                'rfm_score' => $rfmScore,
                'segment' => $this->getCustomerSegment($rScore, $fScore, $mScore),
            ];
        });

        // Group by segment
        $segments = $segmentedCustomers->groupBy('segment')->map(function ($items, $key) {
            return [
                'segment' => $key,
                'count' => $items->count(),
                'total_revenue' => $items->sum('monetary'),
                'avg_frequency' => round($items->avg('frequency'), 2),
                'customers' => $items->take(10),
            ];
        });

        return [
            'customers' => $segmentedCustomers,
            'segments' => $segments,
            'summary' => [
                'total_customers' => $segmentedCustomers->count(),
                'segments_count' => $segments->count(),
            ],
        ];
    }

    /**
     * Calculate Customer Lifetime Value (CLV)
     */
    public function calculateCLV()
    {
        $customers = DB::table('customers')
            ->leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->where('transactions.status', 'completed')
            ->select(
                'customers.id',
                'customers.name',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('SUM(transactions.total_amount) as total_revenue'),
                DB::raw('AVG(transactions.total_amount) as avg_order_value'),
                DB::raw('MIN(transactions.created_at) as first_purchase'),
                DB::raw('MAX(transactions.created_at) as last_purchase')
            )
            ->groupBy('customers.id', 'customers.name')
            ->havingRaw('COUNT(transactions.id) > 0')
            ->get();

        $clvData = $customers->map(function ($customer) {
            $customerSince = Carbon::parse($customer->first_purchase);
            $lastPurchase = Carbon::parse($customer->last_purchase);
            $customerAgeMonths = max(1, $customerSince->diffInMonths(Carbon::now()));
            
            // Calculate metrics
            $avgOrderValue = $customer->avg_order_value;
            $purchaseFrequency = $customer->total_transactions / $customerAgeMonths;
            $customerLifespanMonths = 12; // Assume 12 months lifespan
            
            // CLV = Average Order Value × Purchase Frequency × Customer Lifespan
            $clv = $avgOrderValue * $purchaseFrequency * $customerLifespanMonths;
            
            // Monthly CLV
            $monthlyCLV = $clv / $customerLifespanMonths;

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'total_transactions' => $customer->total_transactions,
                'total_revenue' => $customer->total_revenue,
                'avg_order_value' => $avgOrderValue,
                'purchase_frequency' => round($purchaseFrequency, 2),
                'customer_age_months' => $customerAgeMonths,
                'clv' => $clv,
                'monthly_clv' => $monthlyCLV,
                'clv_tier' => $this->getCLVTier($clv),
            ];
        });

        // Summary statistics
        $totalCLV = $clvData->sum('clv');
        $avgCLV = $clvData->avg('clv');

        return [
            'customers' => $clvData,
            'summary' => [
                'total_clv' => $totalCLV,
                'average_clv' => $avgCLV,
                'high_value_customers' => $clvData->where('clv_tier', 'High')->count(),
                'medium_value_customers' => $clvData->where('clv_tier', 'Medium')->count(),
                'low_value_customers' => $clvData->where('clv_tier', 'Low')->count(),
            ],
        ];
    }

    /**
     * Predict churn risk
     */
    public function predictChurn()
    {
        $now = Carbon::now();
        $churnThreshold = 90; // Days without purchase

        $customers = DB::table('customers')
            ->leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                DB::raw('MAX(transactions.created_at) as last_purchase'),
                DB::raw('COUNT(transactions.id) as total_purchases'),
                DB::raw('SUM(transactions.total_amount) as total_spent'),
                DB::raw('AVG(transactions.total_amount) as avg_purchase_value')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email')
            ->havingRaw('COUNT(transactions.id) > 0')
            ->get();

        $churnAnalysis = $customers->map(function ($customer) use ($now, $churnThreshold) {
            $lastPurchase = Carbon::parse($customer->last_purchase);
            $daysSincePurchase = $now->diffInDays($lastPurchase);
            
            // Calculate churn probability (simple logistic model)
            $churnProbability = $this->calculateChurnProbability($daysSincePurchase, $customer->total_purchases);
            
            // Determine churn risk
            $churnRisk = $churnProbability > 0.7 ? 'High' : ($churnProbability > 0.4 ? 'Medium' : 'Low');

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'last_purchase' => $customer->last_purchase,
                'days_since_purchase' => $daysSincePurchase,
                'total_purchases' => $customer->total_purchases,
                'total_spent' => $customer->total_spent,
                'churn_probability' => round($churnProbability * 100, 2),
                'churn_risk' => $churnRisk,
                'is_at_risk' => $daysSincePurchase > $churnThreshold,
            ];
        });

        // Summary
        $highRisk = $churnAnalysis->where('churn_risk', 'High');
        $mediumRisk = $churnAnalysis->where('churn_risk', 'Medium');
        $lowRisk = $churnAnalysis->where('churn_risk', 'Low');

        $atRiskRevenue = $highRisk->sum('total_spent');

        return [
            'customers' => $churnAnalysis,
            'summary' => [
                'total_customers' => $churnAnalysis->count(),
                'high_risk' => $highRisk->count(),
                'medium_risk' => $mediumRisk->count(),
                'low_risk' => $lowRisk->count(),
                'at_risk_revenue' => $atRiskRevenue,
                'churn_rate' => round(($highRisk->count() / $churnAnalysis->count()) * 100, 2),
            ],
        ];
    }

    /**
     * Get customer journey analysis
     */
    public function getCustomerJourney()
    {
        $journeys = DB::table('customers')
            ->leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->where('transactions.status', 'completed')
            ->select(
                'customers.id',
                'customers.name',
                DB::raw('MIN(transactions.created_at) as first_purchase'),
                DB::raw('MAX(transactions.created_at) as last_purchase'),
                DB::raw('COUNT(transactions.id) as total_visits'),
                DB::raw('SUM(transactions.total_amount) as total_spent')
            )
            ->groupBy('customers.id', 'customers.name')
            ->havingRaw('COUNT(transactions.id) > 0')
            ->limit(100)
            ->get();

        $journeyData = $journeys->map(function ($customer) {
            $firstPurchase = Carbon::parse($customer->first_purchase);
            $lastPurchase = Carbon::parse($customer->last_purchase);
            $customerLifespan = $firstPurchase->diffInDays($lastPurchase);
            $avgDaysBetweenPurchases = $customer->total_visits > 1 ? $customerLifespan / ($customer->total_visits - 1) : 0;

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'first_purchase' => $customer->first_purchase,
                'last_purchase' => $customer->last_purchase,
                'customer_lifespan_days' => $customerLifespan,
                'total_visits' => $customer->total_visits,
                'total_spent' => $customer->total_spent,
                'avg_days_between_purchases' => round($avgDaysBetweenPurchases, 1),
                'purchase_frequency' => round($customer->total_visits / max(1, $customerLifespan / 30), 2), // per month
            ];
        });

        return [
            'journeys' => $journeyData,
            'summary' => [
                'avg_lifespan_days' => round($journeyData->avg('customer_lifespan_days'), 1),
                'avg_visits' => round($journeyData->avg('total_visits'), 2),
                'avg_spent' => round($journeyData->avg('total_spent'), 2),
            ],
        ];
    }

    /**
     * Helper: Calculate quartile scores (1-4)
     */
    private function calculateQuartileScores($values, $lowerIsBetter = false)
    {
        if (empty($values)) return [];

        $sorted = $lowerIsBetter ? sort($values) : rsort($values);
        $count = count($values);
        $scores = [];

        foreach ($values as $index => $value) {
            $percentile = ($index + 1) / $count;
            if ($percentile <= 0.25) {
                $scores[$index] = 1;
            } elseif ($percentile <= 0.50) {
                $scores[$index] = 2;
            } elseif ($percentile <= 0.75) {
                $scores[$index] = 3;
            } else {
                $scores[$index] = 4;
            }
        }

        return $scores;
    }

    /**
     * Helper: Get customer segment based on RFM scores
     */
    private function getCustomerSegment($r, $f, $m)
    {
        if ($r >= 3 && $f >= 3 && $m >= 3) {
            return 'Champions';
        } elseif ($r >= 3 && $f >= 2 && $m >= 2) {
            return 'Loyal Customers';
        } elseif ($r >= 3 && $f <= 2 && $m <= 2) {
            return 'New Customers';
        } elseif ($r <= 2 && $f >= 3 && $m >= 3) {
            return 'At Risk';
        } elseif ($r <= 2 && $f <= 2 && $m <= 2) {
            return 'Lost';
        } else {
            return 'Regular';
        }
    }

    /**
     * Helper: Get CLV tier
     */
    private function getCLVTier($clv)
    {
        if ($clv >= 10000000) {
            return 'High';
        } elseif ($clv >= 1000000) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }

    /**
     * Helper: Calculate churn probability (simple logistic model)
     */
    private function calculateChurnProbability($daysSincePurchase, $totalPurchases)
    {
        // Base probability increases with days
        $baseProbability = min(0.9, $daysSincePurchase / 180);
        
        // Reduce probability for frequent buyers
        $frequencyFactor = max(0.1, 1 - ($totalPurchases / 50));
        
        return $baseProbability * $frequencyFactor;
    }
}
