---
phase: 27
plan: 2
wave: 2
---

# Plan 27.2: Deadstock Page UI/UX Enhancement

## Objective
Transform the Deadstock page from a basic warning display into an actionable analytics dashboard with modern UI/UX that helps users identify and resolve deadstock issues efficiently.

**Priority:** 🟡 MEDIUM  
**Effort:** Medium (4-6 hours)  
**Dependencies:** Task 1 complete (404 fixes)

---

## Context
- `.gsd/phases/27/27-OPTIONS.md`
- `resources/views/pages/inventory/deadstock.blade.php` (current)
- `app/Http/Controllers/Api/ProductController.php`
- `.gsd/phases/26/` (Design System reference)

---

## Tasks

### Task 1: Create Enhanced Deadstock API
**Type:** `auto` | **Effort:** `medium`

**Files:**
- `app/Http/Controllers/Api/ProductController.php`
- `app/Services/DeadstockService.php` (NEW)

**Action:**

1. **Create DeadstockService:**
   ```php
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

           // Calculate days without movement
           $products = $query->with(['category', 'supplier', 'units'])->get();

           $deadstock = $products->map(function($product) use ($filters) {
               $lastMovement = $this->getLastMovementDate($product->id);
               $daysWithoutMovement = $lastMovement ?
                   Carbon::parse($lastMovement)->diffInDays(now()) : 999;

               // Filter by days
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

           return $deadstock;
       }

       /**
        * Get deadstock analytics summary
        */
       public function getAnalytics($tenantId)
       {
           $deadstock = $this->getDeadstock($tenantId);

           $totalItems = $deadstock->count();
           $totalValueLocked = $deadstock->sum('value_locked');
           $avgDaysWithoutMovement = $deadstock->avg('days_without_movement') ?? 0;

           // Top category
           $topCategory = $deadstock->groupBy('category.name')
               ->map(fn($items) => $items->sum('value_locked'))
               ->sortDesc()
               ->first();

           return [
               'total_items' => $totalItems,
               'total_value_locked' => $totalValueLocked,
               'avg_days_without_movement' => round($avgDaysWithoutMovement, 1),
               'top_category' => $topCategory,
               'by_days_range' => [
                   '30_60' => $deadstock->whereBetween('days_without_movement', [30, 60])->count(),
                   '60_90' => $deadstock->whereBetween('days_without_movement', [60, 90])->count(),
                   '90_plus' => $deadstock->where('days_without_movement', '>=', 90)->count(),
               ]
           ];
       }

       private function getLastMovementDate($productId)
       {
           return DB::connection('tenant')
               ->table('transaction_items')
               ->where('product_id', $productId)
               ->max('created_at');
       }
   }
   ```

2. **Add API endpoint in ProductController:**
   ```php
   use App\Services\DeadstockService;

   public function deadstock(Request $request, DeadstockService $deadstockService)
   {
       $tenantId = auth()->user()->tenant_id;

       $filters = [
           'category_id' => $request->category_id,
           'supplier_id' => $request->supplier_id,
           'min_days' => $request->min_days ?? 30,
           'max_stock' => $request->max_stock ?? 0,
       ];

       $deadstock = $deadstockService->getDeadstock($tenantId, $filters);
       $analytics = $deadstockService->getAnalytics($tenantId);

       return response()->json([
           'success' => true,
           'data' => [
               'products' => $deadstock,
               'analytics' => $analytics,
           ]
       ]);
   }
   ```

3. **Add route in `api.php`:**
   ```php
   Route::get('/products/deadstock', [\App\Http\Controllers\Api\ProductController::class , 'deadstock']);
   ```

**Verify:**
```bash
# Test API
curl http://localhost/api/products/deadstock \
  -H "Authorization: Bearer {token}"
```

**Done When:**
- API returns deadstock products with analytics
- Filtering works correctly
- Response includes days without movement

---

### Task 2: Create Modern Deadstock UI
**Type:** `auto` | **Effort:** `high`

**Files:**
- `resources/views/pages/inventory/deadstock.blade.php` (REPLACE)

**Action:**

Replace entire file with enhanced UI:

```blade
@extends('layouts.app')

@section('title', 'Deadstock Analytics | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="deadstockPage()">
    <!-- Header -->
    <div class="max-w-8xl mx-auto mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-xl shadow-amber-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v-2m0 16v-2m0-8H8m8 0h4M8 8V6a4 4 0 118 0v2m-4 8a4 4 0 100-8 4 4 0 000 8z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Deadstock Analytics</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Identify and resolve stagnant inventory</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button @click="exportData()" class="px-5 py-2.5 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export
                </button>
                <button @click="bulkRestock()" class="px-5 py-2.5 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-brand-700 hover:to-indigo-700 transition-all shadow-lg shadow-brand-500/30 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Bulk Restock
                </button>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard -->
    <div class="max-w-8xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Items -->
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-xl shadow-amber-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="text-4xl font-bold" x-text="analytics.total_items || 0"></span>
            </div>
            <p class="text-amber-100 text-sm font-medium">Deadstock Items</p>
            <p class="text-amber-200 text-xs mt-1">Products with no movement</p>
        </div>

        <!-- Value Locked -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900/30 dark:to-rose-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(analytics.total_value_locked)"></span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Capital Locked</p>
            <p class="text-gray-400 text-xs mt-1">Inactive inventory value</p>
        </div>

        <!-- Avg Days Stuck -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="(analytics.avg_days_without_movement || 0) + 'd'"></span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Avg. Days Stuck</p>
            <p class="text-gray-400 text-xs mt-1">Without movement</p>
        </div>

        <!-- Top Category -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Most Affected Category</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1 truncate" x-text="analytics.top_category || 'N/A'"></p>
            <p class="text-gray-400 text-xs mt-1">By value locked</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="max-w-8xl mx-auto mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        🔍 Search Product
                    </label>
                    <input type="text" x-model="filters.search" @input.debounce="fetchDeadstock()"
                        placeholder="Name or SKU..."
                        class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        📂 Category
                    </label>
                    <select x-model="filters.category_id" @change="fetchDeadstock()"
                        class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                        <option value="">All Categories</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Days Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        ⏱️ Days Stuck
                    </label>
                    <select x-model="filters.min_days" @change="fetchDeadstock()"
                        class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                        <option value="30">30+ days</option>
                        <option value="60">60+ days</option>
                        <option value="90">90+ days</option>
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        🔄 Sort By
                    </label>
                    <select x-model="filters.sort" @change="fetchDeadstock()"
                        class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                        <option value="days_desc">Days Stuck (High-Low)</option>
                        <option value="value_desc">Value Locked (High-Low)</option>
                        <option value="name_asc">Name (A-Z)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="max-w-8xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Grid Header -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="font-bold text-gray-800 dark:text-white">Deadstock Products</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="products.length + ' items found'"></span>
            </div>

            <!-- Products Grid -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="product in products" :key="product.id">
                    <div class="rounded-2xl border-2 border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl hover:border-amber-300 dark:hover:border-amber-600 transition-all duration-300 group">
                        <!-- Product Image -->
                        <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 relative overflow-hidden">
                            <img :src="product.image_url || 'https://placehold.co/300x300?text=No+Image'"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">

                            <!-- Days Badge -->
                            <div class="absolute top-3 right-3 px-3 py-1.5 rounded-full text-xs font-bold shadow-lg"
                                :class="product.days_without_movement >= 90 ? 'bg-red-500 text-white' :
                                         product.days_without_movement >= 60 ? 'bg-orange-500 text-white' :
                                         'bg-amber-500 text-white'">
                                <span x-text="product.days_without_movement + ' days'"></span>
                            </div>

                            <!-- Stock Badge -->
                            <div class="absolute top-3 left-3 px-3 py-1.5 rounded-full text-xs font-bold bg-white/90 dark:bg-gray-900/90 text-gray-800 dark:text-white shadow-lg">
                                <span x-text="'Stock: ' + product.stock"></span>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-1 line-clamp-2" x-text="product.name"></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mb-3" x-text="product.sku"></p>

                            <!-- Category & Supplier -->
                            <div class="flex items-center gap-2 mb-3 text-xs">
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-600 dark:text-gray-400"
                                    x-text="product.category?.name || 'Uncategorized'"></span>
                            </div>

                            <!-- Value Locked -->
                            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Value Locked</span>
                                    <span class="text-sm font-bold text-red-600 dark:text-red-400" x-text="formatCurrency(product.value_locked)"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Unit Price</span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="formatCurrency(product.price)"></span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 flex gap-2">
                                <a :href="'/inventory/receiving?restock=' + product.id"
                                    class="flex-1 py-2.5 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl font-semibold text-sm hover:from-brand-700 hover:to-indigo-700 transition-all text-center">
                                    Restock
                                </a>
                                <button @click="createPromotion(product)"
                                    class="px-3 py-2.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-xl font-semibold text-sm hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-all"
                                    title="Create Clearance Promotion">
                                    🏷️
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="products.length === 0 && !isLoading" class="py-16 text-center">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">No Deadstock!</h3>
                <p class="text-gray-500 dark:text-gray-400">All products are moving well</p>
            </div>
        </div>
    </div>
</div>

<script>
function deadstockPage() {
    return {
        products: [],
        categories: [],
        isLoading: true,
        analytics: {
            total_items: 0,
            total_value_locked: 0,
            avg_days_without_movement: 0,
            top_category: '',
        },
        filters: {
            search: '',
            category_id: '',
            min_days: 30,
            sort: 'days_desc',
        },

        async init() {
            await this.fetchCategories();
            await this.fetchDeadstock();
        },

        async fetchCategories() {
            const token = localStorage.getItem('saga_token');
            const res = await fetch('/api/products/categories', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const data = await res.json();
            if (data.success) this.categories = data.data;
        },

        async fetchDeadstock() {
            this.isLoading = true;
            const token = localStorage.getItem('saga_token');
            const params = new URLSearchParams(this.filters);

            try {
                const res = await fetch('/api/products/deadstock?' + params, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.products = data.data.products;
                    this.analytics = data.data.analytics;
                }
            } catch (error) {
                console.error('Fetch error:', error);
            } finally {
                this.isLoading = false;
            }
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },

        exportData() {
            // Implement CSV export
            console.log('Export deadstock data');
        },

        bulkRestock() {
            // Implement bulk restock modal
            console.log('Bulk restock');
        },

        createPromotion(product) {
            // Implement promotion creation
            console.log('Create promotion for', product);
        }
    }
}
</script>
@endsection
```

**Verify:**
- Page loads without errors
- Analytics cards display correctly
- Filtering works
- Products grid renders

**Done When:**
- Modern UI renders correctly
- All filters functional
- Analytics accurate
- Responsive design works

---

## Success Criteria

- [ ] Analytics dashboard displays 4 key metrics
- [ ] Filtering by category, days, sort works
- [ ] Product cards show all relevant info
- [ ] Days stuck badge color-coded
- [ ] Restock button links correctly
- [ ] Export button functional
- [ ] Page loads in <2 seconds
- [ ] Mobile-responsive design

---

## 🧪 Verification Commands

```bash
# 1. Test API endpoint
curl http://localhost/api/products/deadstock \
  -H "Authorization: Bearer {token}"

# 2. Test with filters
curl "http://localhost/api/products/deadstock?min_days=60&category_id=1" \
  -H "Authorization: Bearer {token}"

# 3. Clear caches
php artisan view:clear
php artisan cache:clear

# 4. Test page in browser
# Navigate to /inventory/deadstock
# Verify no console errors
```

---

**Ready for implementation!**
