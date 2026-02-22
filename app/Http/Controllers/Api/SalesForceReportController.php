<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesForceReportController extends Controller
{
    /**
     * Get Sales Force performance data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function performance(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $branchId = $request->get('branch_id');

        // Get all salesmen (users with salesman role)
        $salesmenQuery = User::where('role', 'salesman')
            ->orWhere('role', 'Sales')
            ->select('id', 'name', 'email', 'phone');

        if ($branchId) {
            $salesmenQuery->where('branch_id', $branchId);
        }

        $salesmen = $salesmenQuery->get();

        // Get sales data grouped by salesman
        $salesData = Transaction::whereIn('user_id', $salesmen->pluck('id'))
            ->where('date', '>=', $startDate)
            ->where('status', 'completed')
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('AVG(grand_total) as avg_order_value'),
                DB::raw('COUNT(DISTINCT customer_id) as unique_customers'),
                DB::raw('MAX(created_at) as last_sale_date')
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Build response with salesman data
        $salesmenData = $salesmen->map(function ($salesman) use ($salesData) {
            $data = $salesData->get($salesman->id, [
                'total_orders' => 0,
                'total_sales' => 0,
                'avg_order_value' => 0,
                'unique_customers' => 0,
                'last_sale_date' => null,
            ]);

            // Calculate conversion rate (placeholder - would need visit data)
            $conversionRate = $data->total_orders > 0 ? 75 : 0; // Default 75% if has orders

            return [
                'id' => $salesman->id,
                'name' => $salesman->name,
                'email' => $salesman->email,
                'phone' => $salesman->phone,
                'total_orders' => (int) ($data->total_orders ?? 0),
                'total_sales' => (float) ($data->total_sales ?? 0),
                'avg_order_value' => (float) ($data->avg_order_value ?? 0),
                'unique_customers' => (int) ($data->unique_customers ?? 0),
                'conversion_rate' => $conversionRate,
                'last_sale_date' => $data->last_sale_date ?? null,
            ];
        });

        // Calculate summary
        $totalSalesmen = $salesmen->count();
        $totalOrders = $salesmenData->sum('total_orders');
        $totalRevenue = $salesmenData->sum('total_sales');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $avgConversionRate = $totalSalesmen > 0 ? $salesmenData->avg('conversion_rate') : 0;

        // Get top performer
        $topPerformer = $salesmenData->sortByDesc('total_sales')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_salesmen' => $totalSalesmen,
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'avg_order_value' => $avgOrderValue,
                    'avg_conversion_rate' => $avgConversionRate,
                ],
                'salesmen' => $salesmenData->values(),
                'top_performer' => $topPerformer,
                'period' => [
                    'days' => $days,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Get individual salesman performance
     * 
     * @param int $salesmanId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesmanPerformance($salesmanId, Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $salesman = User::findOrFail($salesmanId);

        $salesData = Transaction::where('user_id', $salesmanId)
            ->where('date', '>=', $startDate)
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('AVG(grand_total) as avg_order_value')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $summary = Transaction::where('user_id', $salesmanId)
            ->where('date', '>=', $startDate)
            ->where('status', 'completed')
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('AVG(grand_total) as avg_order_value'),
                DB::raw('COUNT(DISTINCT customer_id) as unique_customers')
            )
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'salesman' => [
                    'id' => $salesman->id,
                    'name' => $salesman->name,
                    'email' => $salesman->email,
                    'role' => $salesman->role,
                ],
                'summary' => $summary,
                'daily_sales' => $salesData,
                'period' => [
                    'days' => $days,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Export Sales Force report to Excel/CSV
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $format = $request->get('format', 'csv'); // csv or excel

        $salesmen = User::where('role', 'salesman')
            ->orWhere('role', 'Sales')
            ->get();

        $salesData = Transaction::whereIn('user_id', $salesmen->pluck('id'))
            ->where('date', '>=', $startDate)
            ->where('status', 'completed')
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('AVG(grand_total) as avg_order_value'),
                DB::raw('COUNT(DISTINCT customer_id) as unique_customers')
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $exportData = $salesmen->map(function ($salesman) use ($salesData) {
            $data = $salesData->get($salesman->id);

            return [
                'Salesman' => $salesman->name,
                'Email' => $salesman->email,
                'Total Orders' => $data->total_orders ?? 0,
                'Total Sales' => $data->total_sales ?? 0,
                'Avg Order Value' => number_format($data->avg_order_value ?? 0, 2),
                'Unique Customers' => $data->unique_customers ?? 0,
            ];
        });

        if ($format === 'csv') {
            $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne(['Salesman', 'Email', 'Total Orders', 'Total Sales', 'Avg Order Value', 'Unique Customers']);
            $csv->insertAll($exportData->toArray());

            return response($csv->toString(), 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="sales_force_report.csv"',
            ]);
        }

        // For Excel, return JSON for frontend to process
        return response()->json([
            'success' => true,
            'data' => $exportData,
            'message' => 'Excel export requires additional package. Use CSV format instead.',
        ]);
    }
}
