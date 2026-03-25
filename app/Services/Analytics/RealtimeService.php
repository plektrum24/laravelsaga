<?php

namespace App\Services\Analytics;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * RealtimeService
 * 
 * Provides real-time analytics data for dashboard
 */
class RealtimeService
{
    /**
     * Get live sales data (last 50 transactions)
     */
    public function getLiveSales()
    {
        $sales = Transaction::with(['customer', 'user', 'branch'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'reference_number' => $transaction->reference_number,
                    'customer_name' => $transaction->customer?->name ?? 'Walk-in Customer',
                    'cashier_name' => $transaction->user?->name ?? 'Unknown',
                    'branch_name' => $transaction->branch?->name ?? 'Main Branch',
                    'total_amount' => $transaction->total_amount,
                    'payment_method' => $transaction->payment_method,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at->diffForHumans(),
                    'created_at_full' => $transaction->created_at->format('d M Y H:i:s'),
                ];
            });

        return $sales;
    }

    /**
     * Get active users count (last 5 minutes)
     */
    public function getActiveUsers()
    {
        $activeUsers = User::where('last_active_at', '>=', Carbon::now()->subMinutes(5))
            ->count();

        $activeCashiers = User::role('cashier')
            ->where('last_active_at', '>=', Carbon::now()->subMinutes(5))
            ->count();

        return [
            'total' => $activeUsers,
            'cashiers' => $activeCashiers,
            'last_updated' => Carbon::now()->format('H:i:s'),
        ];
    }

    /**
     * Get revenue today
     */
    public function getRevenueToday()
    {
        $today = Carbon::today();

        $revenue = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $count = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        $yesterday = Carbon::yesterday();
        $yesterdayRevenue = Transaction::whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->sum('total_amount');

        $growth = $yesterdayRevenue > 0 
            ? (($revenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 
            : 0;

        return [
            'amount' => $revenue,
            'formatted' => 'Rp ' . number_format($revenue, 0, ',', '.'),
            'transactions' => $count,
            'growth' => round($growth, 2),
            'growth_formatted' => ($growth > 0 ? '+' : '') . round($growth, 2) . '%',
        ];
    }

    /**
     * Get sales statistics for current hour
     */
    public function getHourlyStats()
    {
        $now = Carbon::now();
        $startOfDay = Carbon::today();

        // Get hourly sales for today
        $hourlySales = Transaction::whereDate('created_at', $startOfDay)
            ->where('status', 'completed')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Get current hour stats
        $currentHour = Transaction::whereDate('created_at', $startOfDay)
            ->whereHour('created_at', $now->hour)
            ->where('status', 'completed')
            ->sum('total_amount');

        $currentHourCount = Transaction::whereDate('created_at', $startOfDay)
            ->whereHour('created_at', $now->hour)
            ->where('status', 'completed')
            ->count();

        return [
            'hourly' => $hourlySales,
            'current_hour' => [
                'hour' => $now->hour,
                'amount' => $currentHour,
                'formatted' => 'Rp ' . number_format($currentHour, 0, ',', '.'),
                'transactions' => $currentHourCount,
            ],
        ];
    }

    /**
     * Get top products (last hour)
     */
    public function getTopProducts()
    {
        $topProducts = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.created_at', '>=', Carbon::now()->subHour())
            ->where('transactions.status', 'completed')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(transaction_items.qty) as total_sold'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        return $topProducts;
    }

    /**
     * Get dashboard summary
     */
    public function getDashboardSummary()
    {
        return [
            'revenue_today' => $this->getRevenueToday(),
            'active_users' => $this->getActiveUsers(),
            'live_sales' => $this->getLiveSales(),
            'hourly_stats' => $this->getHourlyStats(),
            'top_products' => $this->getTopProducts(),
            'last_updated' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }
}
