<?php

namespace App\Services;

use App\Models\Product;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeadstockService
{
    /**
     * Get deadstock products with analytics
     */
    public function getDeadstock($tenantId, $filters = [])
    {
        $query = Product::where('tenant_id', $tenantId)
            ->where('stock', '<=', $filters['max_stock'] ?? 0);

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by supplier
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        // Filter by search
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('sku', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        // Get products with relationships
        $products = $query->with(['category', 'supplier', 'units'])->get();

        // Calculate days without movement and filter
        $deadstock = $products->map(function($product) use ($filters) {
            $lastMovement = $this->getLastMovementDate($product->id);
            $daysWithoutMovement = $lastMovement ?
                Carbon::parse($lastMovement)->diffInDays(now()) : 999;

            // Filter by minimum days
            if (isset($filters['min_days']) && $daysWithoutMovement < $filters['min_days']) {
                return null;
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock' => $product->stock,
                'price' => $product->price,
                'value_locked' => $product->stock * $product->price,
                'category' => $product->category,
                'supplier' => $product->supplier,
                'last_movement_date' => $lastMovement,
                'days_without_movement' => $daysWithoutMovement,
                'image_url' => $product->image_url,
            ];
        })->filter()->values();

        // Apply sorting
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'days_desc':
                    $deadstock = $deadstock->sortByDesc('days_without_movement');
                    break;
                case 'value_desc':
                    $deadstock = $deadstock->sortByDesc('value_locked');
                    break;
                case 'name_asc':
                    $deadstock = $deadstock->sortBy('name');
                    break;
            }
        }

        return $deadstock->values();
    }

    /**
     * Get deadstock analytics summary
     */
    public function getAnalytics($tenantId, $filters = [])
    {
        $deadstock = $this->getDeadstock($tenantId, $filters);

        $totalItems = $deadstock->count();
        $totalValueLocked = $deadstock->sum('value_locked');
        $avgDaysWithoutMovement = $deadstock->avg('days_without_movement') ?? 0;

        // Top category by value locked
        $topCategory = $deadstock->groupBy('category.name')
            ->map(fn($items) => $items->sum('value_locked'))
            ->sortDesc()
            ->keys()
            ->first();

        // Breakdown by days range
        $byDaysRange = [
            '0_30' => $deadstock->whereBetween('days_without_movement', [0, 30])->count(),
            '30_60' => $deadstock->whereBetween('days_without_movement', [30, 60])->count(),
            '60_90' => $deadstock->whereBetween('days_without_movement', [60, 90])->count(),
            '90_plus' => $deadstock->where('days_without_movement', '>=', 90)->count(),
        ];

        return [
            'total_items' => $totalItems,
            'total_value_locked' => $totalValueLocked,
            'avg_days_without_movement' => round($avgDaysWithoutMovement, 1),
            'top_category' => $topCategory,
            'by_days_range' => $byDaysRange,
        ];
    }

    /**
     * Get last movement date for a product
     */
    private function getLastMovementDate($productId)
    {
        return DB::connection('tenant')
            ->table('transaction_items')
            ->where('product_id', $productId)
            ->max('created_at');
    }

    /**
     * Export deadstock data to CSV
     */
    public function exportToCsv($tenantId, $filters = [])
    {
        $deadstock = $this->getDeadstock($tenantId, $filters);

        $csv = "SKU,Product Name,Category,Supplier,Stock,Unit Price,Value Locked,Days Without Movement,Last Movement\n";

        foreach ($deadstock as $product) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%d,%s,%s,%d,%s\n",
                $product['sku'],
                $product['name'],
                $product['category']['name'] ?? 'Uncategorized',
                $product['supplier']['name'] ?? 'N/A',
                $product['stock'],
                number_format($product['price'], 0, ',', '.'),
                number_format($product['value_locked'], 0, ',', '.'),
                $product['days_without_movement'],
                $product['last_movement_date'] ?? 'Never'
            );
        }

        return $csv;
    }
}
