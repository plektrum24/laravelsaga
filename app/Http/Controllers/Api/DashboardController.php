<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = now()->format('Y-m-d');
        
        $todaySales = Transaction::whereDate('date', $today)->sum('grand_total');
        $todayTransactions = Transaction::whereDate('date', $today)->count();
        
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'today_sales' => $todaySales,
                'today_transactions' => $todayTransactions,
                'low_stock_count' => $lowStockCount
            ]
        ]);
    }
}
