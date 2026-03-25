<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;
        
        // Get branch_id from request or user or tenant's first branch
        $branchId = $request->get('branch_id') 
            ?? $user->branch_id 
            ?? Branch::where('tenant_id', $tenantId)->first()?->id;

        $today = now()->format('Y-m-d');

        // Build query with proper tenant and branch filtering
        $transactionQuery = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed');
        
        $productQuery = Product::where('tenant_id', $tenantId);

        // Apply branch filter if branch_id exists
        if ($branchId) {
            $transactionQuery->where('branch_id', $branchId);
            $productQuery->where('branch_id', $branchId);
        }

        // Today's stats
        $todaySales = $transactionQuery
            ->whereDate('date', $today)
            ->sum('grand_total');
            
        $todayTransactions = $transactionQuery
            ->whereDate('date', $today)
            ->count();

        // This week stats
        $weekSales = $transactionQuery
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('grand_total');

        // Low stock count
        $lowStockCount = $productQuery
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        // Total products
        $totalProducts = $productQuery->count();

        // Total customers
        $totalCustomers = DB::connection('tenant')
            ->table('customers')
            ->where('tenant_id', $tenantId)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today_sales' => $todaySales,
                'today_transactions' => $todayTransactions,
                'week_sales' => $weekSales,
                'low_stock_count' => $lowStockCount,
                'total_products' => $totalProducts,
                'total_customers' => $totalCustomers,
                'branch_id' => $branchId,
            ]
        ]);
    }
}
