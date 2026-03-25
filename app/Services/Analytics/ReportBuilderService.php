<?php

namespace App\Services\Analytics;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ReportBuilderService
 * 
 * Generates custom reports with various filters and formats
 */
class ReportBuilderService
{
    /**
     * Generate sales report with filters
     */
    public function generateSalesReport(array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth();
        $branchId = $filters['branch_id'] ?? null;
        $categoryId = $filters['category_id'] ?? null;

        $query = Transaction::with(['customer', 'user', 'branch', 'items.product'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed');

        // Apply filters
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($categoryId) {
            $query->whereHas('items.product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary
        $totalRevenue = $transactions->sum('total_amount');
        $totalTransactions = $transactions->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Group by date
        $dailySales = $transactions->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        })->map(function ($items, $key) {
            return [
                'date' => $key,
                'revenue' => $items->sum('total_amount'),
                'transactions' => $items->count(),
            ];
        })->values();

        // Group by product
        $productSales = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->where('transactions.status', 'completed')
            ->when($branchId, function ($q) use ($branchId) {
                return $q->where('transactions.branch_id', $branchId);
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                return $q->where('products.category_id', $categoryId);
            })
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_revenue', 'desc')
            ->limit(20)
            ->get();

        return [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_transactions' => $totalTransactions,
                'average_transaction' => $averageTransaction,
                'date_from' => Carbon::parse($dateFrom)->format('d M Y'),
                'date_to' => Carbon::parse($dateTo)->format('d M Y'),
            ],
            'daily_sales' => $dailySales,
            'product_sales' => $productSales,
            'transactions' => $transactions,
        ];
    }

    /**
     * Generate inventory report
     */
    public function generateInventoryReport(array $filters = [])
    {
        $branchId = $filters['branch_id'] ?? null;

        $query = Product::with(['category', 'branch']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $products = $query->get();

        // Calculate inventory metrics
        $totalProducts = $products->count();
        $totalStock = $products->sum('stock');
        $lowStockProducts = $products->filter(function ($p) {
            return $p->stock <= ($p->min_stock ?? 10);
        })->count();
        $outOfStockProducts = $products->filter(function ($p) {
            return $p->stock == 0;
        })->count();

        // Group by category
        $categoryBreakdown = $products->groupBy('category.name')->map(function ($items) {
            return [
                'category' => $items->first()->category?->name ?? 'Uncategorized',
                'products' => $items->count(),
                'total_stock' => $items->sum('stock'),
                'total_value' => $items->sum(function ($p) {
                    return $p->stock * $p->sell_price;
                }),
            ];
        })->values();

        return [
            'summary' => [
                'total_products' => $totalProducts,
                'total_stock' => $totalStock,
                'low_stock' => $lowStockProducts,
                'out_of_stock' => $outOfStockProducts,
            ],
            'category_breakdown' => $categoryBreakdown,
            'products' => $products,
        ];
    }

    /**
     * Generate customer report
     */
    public function generateCustomerReport(array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth();

        $customers = DB::table('customers')
            ->leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->where('transactions.status', 'completed')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('SUM(transactions.total_amount) as total_spent'),
                DB::raw('MAX(transactions.created_at) as last_purchase')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email')
            ->orderBy('total_spent', 'desc')
            ->limit(50)
            ->get();

        $topCustomers = $customers->take(10);
        $totalCustomers = $customers->count();
        $averageSpent = $totalCustomers > 0 ? $customers->sum('total_spent') / $totalCustomers : 0;

        return [
            'summary' => [
                'total_customers' => $totalCustomers,
                'average_spent' => $averageSpent,
                'date_from' => Carbon::parse($dateFrom)->format('d M Y'),
                'date_to' => Carbon::parse($dateTo)->format('d M Y'),
            ],
            'top_customers' => $topCustomers,
            'all_customers' => $customers,
        ];
    }

    /**
     * Export report to array format (for Excel/CSV)
     */
    public function exportToArray($reportType, array $data)
    {
        switch ($reportType) {
            case 'sales':
                return $this->exportSalesToArray($data);
            case 'inventory':
                return $this->exportInventoryToArray($data);
            case 'customers':
                return $this->exportCustomersToArray($data);
            default:
                return [];
        }
    }

    private function exportSalesToArray($data)
    {
        $rows = [];
        foreach ($data['transactions'] as $transaction) {
            $rows[] = [
                'Date' => Carbon::parse($transaction->created_at)->format('d/m/Y H:i'),
                'Reference' => $transaction->reference_number,
                'Customer' => $transaction->customer?->name ?? 'Walk-in',
                'Cashier' => $transaction->user?->name ?? 'Unknown',
                'Branch' => $transaction->branch?->name ?? 'Main',
                'Amount' => $transaction->total_amount,
                'Payment Method' => $transaction->payment_method,
                'Status' => $transaction->status,
            ];
        }
        return $rows;
    }

    private function exportInventoryToArray($data)
    {
        $rows = [];
        foreach ($data['products'] as $product) {
            $rows[] = [
                'SKU' => $product->sku,
                'Name' => $product->name,
                'Category' => $product->category?->name ?? 'Uncategorized',
                'Stock' => $product->stock,
                'Buy Price' => $product->buy_price,
                'Sell Price' => $product->sell_price,
                'Stock Value' => $product->stock * $product->sell_price,
                'Status' => $product->stock == 0 ? 'Out of Stock' : ($product->stock <= ($product->min_stock ?? 10) ? 'Low Stock' : 'In Stock'),
            ];
        }
        return $rows;
    }

    private function exportCustomersToArray($data)
    {
        $rows = [];
        foreach ($data['all_customers'] as $customer) {
            $rows[] = [
                'Name' => $customer->name,
                'Phone' => $customer->phone ?? '-',
                'Email' => $customer->email ?? '-',
                'Transactions' => $customer->total_transactions,
                'Total Spent' => $customer->total_spent,
                'Last Purchase' => Carbon::parse($customer->last_purchase)->format('d/m/Y'),
            ];
        }
        return $rows;
    }
}
