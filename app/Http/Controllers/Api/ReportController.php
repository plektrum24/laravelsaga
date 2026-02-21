<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function assets(Request $request)
    {
        $query = Product::query();
        if ($request->has('branch_id') && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $totalAsset = $query->sum(DB::raw('stock * buy_price'));

        return response()->json(['success' => true, 'data' => ['totalAsset' => $totalAsset]]);
    }

    public function salesOverview(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $sales = Transaction::where('date', '>=', $startDate)
            ->where('status', 'completed')
            ->select(
            DB::raw('DATE(date) as day'),
            DB::raw('SUM(grand_total) as total_revenue'),
            DB::raw('COUNT(*) as total_transactions')
        )
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Calculate total profit for the period
        $profitData = TransactionItem::whereHas('transaction', function ($q) use ($startDate) {
            $q->where('date', '>=', $startDate)->where('status', 'completed');
        })
            ->select(DB::raw('SUM(subtotal - (qty * cogs)) as total_profit'))
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'chartData' => $sales,
                'totalRevenue' => $sales->sum('total_revenue'),
                'totalProfit' => $profitData->total_profit ?? 0,
                'totalTransactions' => $sales->sum('total_transactions'),
            ]
        ]);
    }

    public function topProducts(Request $request)
    {
        $limit = $request->get('limit', 5);
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $products = TransactionItem::whereHas('transaction', function ($q) use ($startDate) {
            $q->where('date', '>=', $startDate)->where('status', 'completed');
        })
            ->select(
            'product_id',
            DB::raw('SUM(qty) as total_qty'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['success' => true, 'data' => $products]);
    }

    public function categoryPerformance(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $categories = TransactionItem::whereHas('transaction', function ($q) use ($startDate) {
            $q->where('date', '>=', $startDate)->where('status', 'completed');
        })
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
            'categories.name',
            DB::raw('SUM(transaction_items.subtotal) as total_revenue')
        )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function inventoryMovements(Request $request)
    {
        $limit = $request->get('limit', 50);
        $movements = InventoryMovement::with(['product:id,name,sku', 'user:id,name', 'branch:id,name'])
            ->latest()
            ->paginate($limit);

        return response()->json(['success' => true, 'data' => $movements]);
    }
}
