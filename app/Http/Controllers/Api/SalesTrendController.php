<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesTrendController extends Controller
{
    /**
     * Get sales trend data
     * GET /api/sales/trend
     */
    public function trend(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|in:daily,weekly,monthly,yearly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $period = $validated['period'] ?? 'daily';
        $startDate = $validated['start_date'] ?? now()->subDays(30);
        $endDate = $validated['end_date'] ?? now();

        $tenantId = auth()->user()->tenant_id;

        $query = Transaction::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'completed');

        $data = [];
        
        switch ($period) {
            case 'daily':
                $data = $this->getDailyData($query, $startDate, $endDate);
                break;
            case 'weekly':
                $data = $this->getWeeklyData($query, $startDate, $endDate);
                break;
            case 'monthly':
                $data = $this->getMonthlyData($query, $startDate, $endDate);
                break;
            case 'yearly':
                $data = $this->getYearlyData($query, $startDate, $endDate);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get sales by category
     * GET /api/sales/by-category
     */
    public function byCategory(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(30);
        $endDate = $validated['end_date'] ?? now();
        $tenantId = auth()->user()->tenant_id;

        $data = Transaction::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(transaction_items.subtotal) as total_sales'),
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('COUNT(DISTINCT transactions.id) as transaction_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_sales', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get sales by product
     * GET /api/sales/top-products
     */
    public function topProducts(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $limit = $validated['limit'] ?? 10;
        $startDate = $validated['start_date'] ?? now()->subDays(30);
        $endDate = $validated['end_date'] ?? now();
        $tenantId = auth()->user()->tenant_id;

        $data = Transaction::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(transaction_items.qty) as total_sold'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue'),
                DB::raw('AVG(transaction_items.price) as avg_price')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get hourly sales pattern
     * GET /api/sales/hourly-pattern
     */
    public function hourlyPattern(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(30);
        $endDate = $validated['end_date'] ?? now();
        $tenantId = auth()->user()->tenant_id;

        $data = Transaction::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as avg_transaction')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function getDailyData($query, $startDate, $endDate)
    {
        return $query->select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as avg_transaction')
            )
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('date')
            ->get();
    }

    private function getWeeklyData($query, $startDate, $endDate)
    {
        return $query->select(
                DB::raw('YEARWEEK(date) as year_week'),
                DB::raw('MIN(DATE(date)) as week_start'),
                DB::raw('MAX(DATE(date)) as week_end'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->groupBy(DB::raw('YEARWEEK(date)'))
            ->orderBy('year_week')
            ->get();
    }

    private function getMonthlyData($query, $startDate, $endDate)
    {
        return $query->select(
                DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as avg_transaction')
            )
            ->groupBy(DB::raw('DATE_FORMAT(date, "%Y-%m")'))
            ->orderBy('month')
            ->get();
    }

    private function getYearlyData($query, $startDate, $endDate)
    {
        return $query->select(
                DB::raw('YEAR(date) as year'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as avg_transaction')
            )
            ->groupBy(DB::raw('YEAR(date)'))
            ->orderBy('year')
            ->get();
    }
}
