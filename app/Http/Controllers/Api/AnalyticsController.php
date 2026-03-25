<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsDashboard;
use App\Models\DashboardWidget;
use App\Models\SalesForecast;
use App\Models\CustomerSegment;
use App\Models\AutomatedReport;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\WebOrder;
use App\Services\ForecastingService;
use App\Services\CustomerAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected ForecastingService $forecastingService;
    protected CustomerAnalyticsService $customerAnalyticsService;

    public function __construct(
        ForecastingService $forecastingService,
        CustomerAnalyticsService $customerAnalyticsService
    ) {
        $this->forecastingService = $forecastingService;
        $this->customerAnalyticsService = $customerAnalyticsService;
    }

    /**
     * Get dashboard data
     * GET /api/analytics/dashboard
     */
    public function dashboard(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        // Get or create default dashboard
        $dashboard = AnalyticsDashboard::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->first();

        if (!$dashboard) {
            $dashboard = $this->createDefaultDashboard($tenantId);
        }

        $dashboard->load('widgets');

        return response()->json([
            'success' => true,
            'data' => [
                'dashboard' => $dashboard,
                'widgets' => $dashboard->widgets,
            ]
        ]);
    }

    /**
     * Get KPI metrics
     * GET /api/analytics/kpis?from=2026-01-01&to=2026-02-21
     */
    public function kpis(Request $request)
    {
        $validated = $request->validate([
            'from' => 'date',
            'to' => 'date',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $from = $validated['from'] ?? now()->startOfMonth();
        $to = $validated['to'] ?? now()->endOfDay();

        // Revenue
        $revenue = Transaction::where('tenant_id', $tenantId)
            ->whereBetween('date', [$from, $to])
            ->where('status', 'completed')
            ->sum('grand_total');

        // Orders
        $orders = WebOrder::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Customers
        $customers = Customer::where('tenant_id', $tenantId)->count();

        // Average Order Value
        $avgOrderValue = $orders > 0 ? $revenue / $orders : 0;

        // Today's metrics
        $todayRevenue = Transaction::where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->where('status', 'completed')
            ->sum('grand_total');

        $todayOrders = WebOrder::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->count();

        // Growth calculation (vs previous period)
        $previousFrom = Carbon::parse($from)->subDays($from->diffInDays($to));
        $previousTo = Carbon::parse($from)->subDay();

        $previousRevenue = Transaction::where('tenant_id', $tenantId)
            ->whereBetween('date', [$previousFrom, $previousTo])
            ->where('status', 'completed')
            ->sum('grand_total');

        $growth = $previousRevenue > 0 ? (($revenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'revenue' => $revenue,
                'orders' => $orders,
                'customers' => $customers,
                'avg_order_value' => $avgOrderValue,
                'today_revenue' => $todayRevenue,
                'today_orders' => $todayOrders,
                'growth_percent' => round($growth, 2),
                'period' => [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                ],
            ]
        ]);
    }

    /**
     * Get sales trend chart data
     * GET /api/analytics/sales-trend?period=30
     */
    public function salesTrend(Request $request)
    {
        $period = $request->get('period', 30);
        $tenantId = auth()->user()->tenant_id;
        
        $startDate = now()->subDays($period);

        $salesData = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('date', '>=', $startDate)
            ->selectRaw('DATE(date) as date, SUM(grand_total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $salesData->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => $item->total,
                    'orders' => $item->count,
                ];
            }),
        ]);
    }

    /**
     * Get top products
     * GET /api/analytics/top-products?limit=10&period=30
     */
    public function topProducts(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 30);
        $tenantId = auth()->user()->tenant_id;
        
        $startDate = now()->subDays($period);

        $topProducts = DB::connection('tenant')
            ->table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', 'completed')
            ->where('transactions.date', '>=', $startDate)
            ->selectRaw('products.id, products.name, products.sku, 
                        SUM(transaction_items.qty) as total_qty, 
                        SUM(transaction_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_qty', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topProducts,
        ]);
    }

    /**
     * Get category performance
     * GET /api/analytics/category-performance?period=30
     */
    public function categoryPerformance(Request $request)
    {
        $period = $request->get('period', 30);
        $tenantId = auth()->user()->tenant_id;
        
        $startDate = now()->subDays($period);

        $categoryData = DB::connection('tenant')
            ->table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', 'completed')
            ->where('transactions.date', '>=', $startDate)
            ->selectRaw('categories.id, categories.name, 
                        SUM(transaction_items.subtotal) as total_revenue,
                        SUM(transaction_items.qty) as total_qty')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categoryData,
        ]);
    }

    /**
     * Get customer segments
     * GET /api/analytics/customer-segments
     */
    public function customerSegments()
    {
        $tenantId = auth()->user()->tenant_id;

        $segments = CustomerSegment::where('tenant_id', $tenantId)
            ->selectRaw('segment_type, segment_value, COUNT(*) as count')
            ->groupBy('segment_type', 'segment_value')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $segments,
        ]);
    }

    /**
     * Get sales forecast
     * GET /api/analytics/forecast?days=30
     */
    public function forecast(Request $request)
    {
        $days = $request->get('days', 30);
        $tenantId = auth()->user()->tenant_id;

        $forecasts = SalesForecast::where('tenant_id', $tenantId)
            ->where('forecast_date', '>=', now())
            ->where('forecast_date', '<=', now()->addDays($days))
            ->orderBy('forecast_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $forecasts->map(function ($forecast) {
                return [
                    'date' => $forecast->forecast_date->format('Y-m-d'),
                    'predicted_sales' => $forecast->predicted_sales,
                    'confidence_score' => $forecast->confidence_score,
                ];
            }),
        ]);
    }

    /**
     * Get automated reports
     * GET /api/analytics/reports
     */
    public function reports()
    {
        $tenantId = auth()->user()->tenant_id;

        $reports = AutomatedReport::where('tenant_id', $tenantId)
            ->active()
            ->with('executionLogs')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Create default dashboard
     */
    private function createDefaultDashboard($tenantId)
    {
        $dashboard = AnalyticsDashboard::create([
            'tenant_id' => $tenantId,
            'name' => 'Executive Dashboard',
            'description' => 'Main business metrics dashboard',
            'is_default' => true,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        // Add default widgets
        $widgets = [
            ['widget_type' => 'kpi_revenue', 'title' => 'Total Revenue', 'position' => 1, 'width' => 3],
            ['widget_type' => 'kpi_orders', 'title' => 'Total Orders', 'position' => 2, 'width' => 3],
            ['widget_type' => 'kpi_customers', 'title' => 'Total Customers', 'position' => 3, 'width' => 3],
            ['widget_type' => 'kpi_growth', 'title' => 'Growth', 'position' => 4, 'width' => 3],
            ['widget_type' => 'sales_chart', 'title' => 'Sales Trend', 'position' => 5, 'width' => 12, 'height' => 2],
            ['widget_type' => 'top_products', 'title' => 'Top Products', 'position' => 6, 'width' => 6],
            ['widget_type' => 'category_chart', 'title' => 'Category Performance', 'position' => 7, 'width' => 6],
        ];

        foreach ($widgets as $widgetData) {
            DashboardWidget::create([
                'dashboard_id' => $dashboard->id,
                'widget_type' => $widgetData['widget_type'],
                'title' => $widgetData['title'],
                'position' => $widgetData['position'],
                'width' => $widgetData['width'],
                'height' => $widgetData['height'] ?? 1,
                'is_active' => true,
            ]);
        }

        return $dashboard;
    }

    /**
     * Generate sales forecast
     * POST /api/analytics/forecast/generate
     */
    public function generateForecast(Request $request)
    {
        try {
            $validated = $request->validate([
                'days' => 'nullable|integer|min:7|max:90',
            ]);

            $tenantId = auth()->user()->tenant_id;
            $days = $validated['days'] ?? 30;

            $result = $this->forecastingService->generateSalesForecast($tenantId, $days);

            if ($result['success'] && !empty($result['forecasts'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Forecast generated successfully',
                    'data' => $result['forecasts'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to generate forecast. Insufficient historical data.',
                    'data' => [],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate forecast: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Get demand predictions
     * GET /api/analytics/demand-prediction?days=30
     */
    public function demandPrediction(Request $request)
    {
        $validated = $request->validate([
            'days' => 'integer|min:7|max:90',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $days = $validated['days'] ?? 30;

        $predictions = $this->forecastingService->predictDemand($tenantId, $days);

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * Get stock optimization recommendations
     * GET /api/analytics/stock-optimization
     */
    public function stockOptimization()
    {
        $tenantId = auth()->user()->tenant_id;
        $recommendations = $this->forecastingService->optimizeStock($tenantId);

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * Get churn predictions
     * GET /api/analytics/churn-prediction
     */
    public function churnPrediction()
    {
        $tenantId = auth()->user()->tenant_id;
        $predictions = $this->forecastingService->predictChurn($tenantId);

        // Return top 50 at-risk customers
        $atRisk = array_filter($predictions, fn($p) => 
            in_array($p['churn_risk'], ['Critical', 'High', 'Medium'])
        );

        return response()->json([
            'success' => true,
            'data' => [
                'all_predictions' => array_slice($predictions, 0, 50),
                'at_risk_count' => count($atRisk),
                'critical_count' => count(array_filter($predictions, fn($p) => $p['churn_risk'] === 'Critical')),
                'high_risk_count' => count(array_filter($predictions, fn($p) => $p['churn_risk'] === 'High')),
            ],
        ]);
    }

    /**
     * Run forecast generation (admin)
     * POST /api/analytics/run-forecast
     */
    public function runForecast(Request $request)
    {
        $validated = $request->validate([
            'forecast_days' => 'integer|min:7|max:90',
            'include_demand' => 'boolean',
            'include_churn' => 'boolean',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $results = [];

        // Generate sales forecast
        if ($validated['forecast_days'] ?? 30) {
            $forecastResult = $this->forecastingService->generateSalesForecast(
                $tenantId, 
                $validated['forecast_days'] ?? 30
            );
            $results['sales_forecast'] = $forecastResult;
        }

        // Generate demand prediction
        if ($validated['include_demand'] ?? false) {
            $results['demand_prediction'] = $this->forecastingService->predictDemand(
                $tenantId, 
                $validated['forecast_days'] ?? 30
            );
        }

        // Generate churn prediction
        if ($validated['include_churn'] ?? false) {
            $results['churn_prediction'] = $this->forecastingService->predictChurn($tenantId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Forecast generation completed',
            'data' => $results,
        ]);
    }

    /**
     * Calculate RFM segmentation
     * POST /api/analytics/rfm-calculate
     */
    public function calculateRFM()
    {
        $tenantId = auth()->user()->tenant_id;
        $results = $this->customerAnalyticsService->calculateRFM($tenantId);
        
        // Get segment summary
        $segments = collect($results)->groupBy('segment')->map(function($group) {
            return $group->count();
        });
        
        return response()->json([
            'success' => true,
            'message' => 'RFM segmentation completed',
            'data' => [
                'total_customers' => count($results),
                'segments' => $segments,
                'details' => array_slice($results, 0, 100), // Return first 100
            ],
        ]);
    }

    /**
     * Get RFM segments
     * GET /api/analytics/rfm-segments
     */
    public function getRFMSegments()
    {
        $tenantId = auth()->user()->tenant_id;
        
        $segments = CustomerSegment::where('tenant_id', $tenantId)
            ->where('segment_type', 'RFM')
            ->selectRaw('segment_value, COUNT(*) as count, AVG(score) as avg_score')
            ->groupBy('segment_value')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $segments,
        ]);
    }

    /**
     * Calculate CLV
     * POST /api/analytics/clv-calculate
     */
    public function calculateCLV()
    {
        $tenantId = auth()->user()->tenant_id;
        $results = $this->customerAnalyticsService->calculateCLV($tenantId);
        
        $summary = [
            'total_customers' => count($results),
            'avg_clv' => collect($results)->avg('clv'),
            'total_clv' => collect($results)->sum('clv'),
            'by_tier' => collect($results)->groupBy('clv_tier')->map->count(),
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'CLV calculation completed',
            'data' => [
                'summary' => $summary,
                'details' => array_slice($results, 0, 100),
            ],
        ]);
    }

    /**
     * Get cohort analysis
     * GET /api/analytics/cohort-analysis?period=month
     */
    public function getCohortAnalysis(Request $request)
    {
        $validated = $request->validate([
            'period' => 'in:month,week',
        ]);
        
        $tenantId = auth()->user()->tenant_id;
        $period = $validated['period'] ?? 'month';
        
        $analysis = $this->customerAnalyticsService->cohortAnalysis($tenantId, $period);
        
        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }

    /**
     * Generate report
     * POST /api/analytics/generate-report
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:sales_summary,customer_report,inventory_report',
            'from' => 'date',
            'to' => 'date',
        ]);
        
        $tenantId = auth()->user()->tenant_id;
        $report = $this->customerAnalyticsService->generateReport(
            $tenantId, 
            $validated['report_type'],
            [
                'from' => $validated['from'] ?? now()->startOfMonth(),
                'to' => $validated['to'] ?? now()->endOfDay(),
            ]
        );
        
        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Export report to CSV
     * GET /api/analytics/export/{reportType}
     */
    public function exportReport($reportType, Request $request)
    {
        $validated = $request->validate([
            'from' => 'date',
            'to' => 'date',
        ]);
        
        $tenantId = auth()->user()->tenant_id;
        $report = $this->customerAnalyticsService->generateReport(
            $tenantId,
            $reportType,
            [
                'from' => $validated['from'] ?? now()->startOfMonth(),
                'to' => $validated['to'] ?? now()->endOfDay(),
            ]
        );
        
        // Convert to CSV
        $csv = $this->generateCSV($report);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $reportType . '_' . date('Y-m-d') . '.csv"');
    }

    /**
     * Generate CSV from report data
     */
    private function generateCSV($report)
    {
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, ['Report Type', 'Generated At']);
        fputcsv($output, [$report['report_type'], $report['generated_at']]);
        fputcsv($output, []);
        
        // Add data
        foreach ($report['data'] as $key => $value) {
            fputcsv($output, [$key, $value]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
