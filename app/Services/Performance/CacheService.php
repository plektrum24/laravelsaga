<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * CacheService
 * 
 * Manages application caching for improved performance
 */
class CacheService
{
    /**
     * Clear all application cache
     */
    public function clearAll()
    {
        Cache::flush();
        
        return [
            'success' => true,
            'message' => 'All cache cleared successfully',
            'cleared_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get cache statistics
     */
    public function getStatistics()
    {
        // Try to get cache driver info
        $driver = config('cache.default');
        
        return [
            'driver' => $driver,
            'prefix' => config('cache.prefix'),
            'stores' => array_keys(config('cache.stores')),
            'default_ttl' => config('cache.default_ttl', 3600),
        ];
    }

    /**
     * Cache dashboard data (common queries)
     */
    public function cacheDashboardData()
    {
        $cacheTime = now()->addMinutes(5);
        
        // Cache today's revenue
        Cache::remember('dashboard.revenue.today', $cacheTime, function () {
            return DB::table('transactions')
                ->whereDate('created_at', Carbon::today())
                ->where('status', 'completed')
                ->sum('total_amount');
        });
        
        // Cache today's transaction count
        Cache::remember('dashboard.transactions.today', $cacheTime, function () {
            return DB::table('transactions')
                ->whereDate('created_at', Carbon::today())
                ->where('status', 'completed')
                ->count();
        });
        
        // Cache active products count
        Cache::remember('dashboard.products.active', $cacheTime, function () {
            return DB::table('products')
                ->where('is_active', true)
                ->count();
        });
        
        // Cache low stock count
        Cache::remember('dashboard.products.low_stock', $cacheTime, function () {
            return DB::table('products')
                ->whereColumn('stock', '<=', 'min_stock')
                ->count();
        });
        
        return [
            'success' => true,
            'message' => 'Dashboard data cached for 5 minutes',
            'cached_keys' => [
                'dashboard.revenue.today',
                'dashboard.transactions.today',
                'dashboard.products.active',
                'dashboard.products.low_stock',
            ],
            'cache_until' => $cacheTime->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Cache product data
     */
    public function cacheProductData()
    {
        $cacheTime = now()->addMinutes(30);
        
        // Cache all products with relationships
        Cache::remember('products.all_with_details', $cacheTime, function () {
            return DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('branches', 'products.branch_id', '=', 'branches.id')
                ->select(
                    'products.*',
                    'categories.name as category_name',
                    'branches.name as branch_name'
                )
                ->get();
        });
        
        // Cache product categories
        Cache::remember('products.categories', $cacheTime, function () {
            return DB::table('categories')
                ->select('id', 'name', 'parent_id')
                ->get();
        });
        
        return [
            'success' => true,
            'message' => 'Product data cached for 30 minutes',
            'cached_keys' => [
                'products.all_with_details',
                'products.categories',
            ],
            'cache_until' => $cacheTime->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Cache customer data
     */
    public function cacheCustomerData()
    {
        $cacheTime = now()->addHour();
        
        // Cache customer count
        Cache::remember('customers.total_count', $cacheTime, function () {
            return DB::table('customers')->count();
        });
        
        // Cache top customers
        Cache::remember('customers.top_50', $cacheTime, function () {
            return DB::table('customers')
                ->leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
                ->select(
                    'customers.*',
                    DB::raw('COUNT(transactions.id) as transaction_count'),
                    DB::raw('SUM(transactions.total_amount) as total_spent')
                )
                ->groupBy('customers.id')
                ->orderBy('total_spent', 'desc')
                ->limit(50)
                ->get();
        });
        
        return [
            'success' => true,
            'message' => 'Customer data cached for 1 hour',
            'cached_keys' => [
                'customers.total_count',
                'customers.top_50',
            ],
            'cache_until' => $cacheTime->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Warm up all caches
     */
    public function warmupCaches()
    {
        $results = [];
        
        $results['dashboard'] = $this->cacheDashboardData();
        $results['products'] = $this->cacheProductData();
        $results['customers'] = $this->cacheCustomerData();
        
        return [
            'success' => true,
            'message' => 'All caches warmed up successfully',
            'results' => $results,
            'warmed_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Remove specific cache key
     */
    public function forget($key)
    {
        Cache::forget($key);
        
        return [
            'success' => true,
            'message' => "Cache key '{$key}' removed",
        ];
    }

    /**
     * Get cache hit/miss statistics (if available)
     */
    public function getHitMissStats()
    {
        // This depends on cache driver
        // Redis/Memcached would have stats
        // File driver doesn't track this
        
        return [
            'available' => in_array(config('cache.default'), ['redis', 'memcached']),
            'message' => 'Cache statistics only available for Redis/Memcached drivers',
        ];
    }
}
