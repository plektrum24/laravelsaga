---
phase: 28
plan: 1
wave: 1
---

# Plan 28.1: Backend Foundation for Forecast Targets

## Objective
Create the database schema, models, and service layer for forecast target calculation and storage.

**Priority:** 🔴 P0  
**Effort:** 4-5 hours  
**Dependencies:** None

---

## Context
- `.gsd/phases/28/28-OPTIONS.md`
- `app/Services/ForecastingService.php` (existing)
- `app/Models/Product.php`
- `app/Models/Transaction.php`

---

## Tasks

### Task 1: Create Database Migrations
**Type:** `auto` | **Effort:** `low`

**Files:**
- `database/migrations/tenant/2026_02_23_000002_create_forecast_targets_tables.php`

**Action:**

Create migration for forecast targets:

```php
<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->decimal('target_revenue', 15, 2);
            $table->integer('target_duration_days')->default(30);
            $table->decimal('current_trajectory', 15, 2)->default(0);
            $table->decimal('gap', 15, 2)->default(0);
            $table->enum('status', ['draft', 'active', 'achieved', 'expired'])->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('achieved_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'generated_at']);
        });

        Schema::create('forecast_target_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_target_id')->constrained('forecast_targets')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('recommended_qty')->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('expected_revenue', 15, 2)->default(0);
            $table->decimal('expected_profit', 15, 2)->default(0);
            $table->integer('priority')->default(3); // 1-5, 1 is highest
            $table->timestamps();
            
            $table->index(['forecast_target_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_target_items');
        Schema::dropIfExists('forecast_targets');
    }
};
```

**Verify:**
```bash
php artisan migrate
```

**Done When:**
- Migration file created
- Migration runs successfully
- Tables created with indexes

---

### Task 2: Create Models
**Type:** `auto` | **Effort:** `low`

**Files:**
- `app/Models/ForecastTarget.php`
- `app/Models/ForecastTargetItem.php`

**Action:**

**ForecastTarget.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastTarget extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'target_revenue',
        'target_duration_days',
        'current_trajectory',
        'gap',
        'status',
        'generated_at',
        'achieved_at',
    ];

    protected $casts = [
        'target_revenue' => 'decimal:2',
        'current_trajectory' => 'decimal:2',
        'gap' => 'decimal:2',
        'target_duration_days' => 'integer',
        'generated_at' => 'datetime',
        'achieved_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ForecastTargetItem::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get progress percentage
     */
    public function getProgressAttribute()
    {
        if ($this->target_revenue == 0) {
            return 0;
        }
        return round(($this->current_trajectory / $this->target_revenue) * 100, 2);
    }

    /**
     * Check if on track
     */
    public function getOnTrackAttribute()
    {
        $elapsedDays = now()->diffInDays($this->generated_at, false);
        if ($elapsedDays < 0 || $this->target_duration_days <= 0) {
            return false;
        }

        $expectedProgress = ($elapsedDays / $this->target_duration_days) * 100;
        return $this->progress >= $expectedProgress;
    }

    /**
     * Scope for active targets
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

**ForecastTargetItem.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastTargetItem extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'forecast_target_id',
        'product_id',
        'recommended_qty',
        'unit_cost',
        'total_cost',
        'expected_revenue',
        'expected_profit',
        'priority',
    ];

    protected $casts = [
        'recommended_qty' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expected_revenue' => 'decimal:2',
        'expected_profit' => 'decimal:2',
        'priority' => 'integer',
    ];

    public function forecastTarget()
    {
        return $this->belongsTo(ForecastTarget::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get profit margin
     */
    public function getProfitMarginAttribute()
    {
        if ($this->expected_revenue == 0) {
            return 0;
        }
        return round(($this->expected_profit / $this->expected_revenue) * 100, 2);
    }
}
```

**Verify:**
```bash
php artisan tinker
>>> \App\Models\ForecastTarget::class
>>> \App\Models\ForecastTargetItem::class
```

**Done When:**
- Both models created
- Relationships defined
- Accessors working
- No syntax errors

---

### Task 3: Create ForecastTargetService
**Type:** `auto` | **Effort:** `high`

**Files:**
- `app/Services/ForecastTargetService.php`

**Action:**

```php
<?php

namespace App\Services;

use App\Models\ForecastTarget;
use App\Models\ForecastTargetItem;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForecastTargetService
{
    /**
     * Calculate forecast based on target revenue
     */
    public function calculateFromTarget($tenantId, $targetRevenue, $days)
    {
        // Get historical data (last 90 days)
        $historicalData = $this->getHistoricalData($tenantId, 90);
        
        // Calculate current trajectory
        $currentTrajectory = $this->projectCurrent($historicalData, $days);
        
        // Calculate gap
        $gap = $targetRevenue - $currentTrajectory;
        
        // Calculate required daily sales
        $requiredDailySales = $targetRevenue / $days;
        
        // Get product mix recommendations
        $productMix = $this->recommendProductMix($tenantId, $gap);
        
        // Calculate costs and profits
        $totalCost = $this->calculateTotalCost($productMix);
        $expectedProfit = $targetRevenue - $totalCost;
        $profitMargin = $targetRevenue > 0 ? ($expectedProfit / $targetRevenue) * 100 : 0;
        
        // Calculate break-even date
        $breakEvenDate = $this->calculateBreakEvenDate($tenantId, $targetRevenue);
        
        return [
            'target_revenue' => $targetRevenue,
            'current_trajectory' => $currentTrajectory,
            'gap' => $gap,
            'required_daily_sales' => $requiredDailySales,
            'product_mix' => $productMix,
            'total_cost' => $totalCost,
            'expected_profit' => $expectedProfit,
            'profit_margin' => round($profitMargin, 2),
            'break_even_date' => $breakEvenDate,
            'duration_days' => $days,
        ];
    }

    /**
     * Save forecast target
     */
    public function saveTarget($tenantId, $data)
    {
        DB::connection('tenant')->beginTransaction();
        
        try {
            // Create forecast target
            $forecastTarget = ForecastTarget::create([
                'tenant_id' => $tenantId,
                'target_revenue' => $data['target_revenue'],
                'target_duration_days' => $data['duration_days'] ?? 30,
                'current_trajectory' => $data['current_trajectory'] ?? 0,
                'gap' => $data['gap'] ?? 0,
                'status' => 'active',
                'generated_at' => now(),
            ]);
            
            // Create target items
            if (!empty($data['product_mix'])) {
                foreach ($data['product_mix'] as $item) {
                    ForecastTargetItem::create([
                        'forecast_target_id' => $forecastTarget->id,
                        'product_id' => $item['product_id'],
                        'recommended_qty' => $item['recommended_qty'] ?? 0,
                        'unit_cost' => $item['unit_cost'] ?? 0,
                        'total_cost' => $item['total_cost'] ?? 0,
                        'expected_revenue' => $item['expected_revenue'] ?? 0,
                        'expected_profit' => $item['expected_profit'] ?? 0,
                        'priority' => $item['priority'] ?? 3,
                    ]);
                }
            }
            
            DB::connection('tenant')->commit();
            
            return ['success' => true, 'target' => $forecastTarget];
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get historical sales data
     */
    private function getHistoricalData($tenantId, $days)
    {
        return DB::connection('tenant')
            ->table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('date', '>=', now()->subDays($days))
            ->selectRaw('DATE(date) as date, SUM(grand_total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Project current trajectory
     */
    private function projectCurrent($historicalData, $days)
    {
        if ($historicalData->count() < 7) {
            return 0;
        }

        // Average daily sales from last 7 days
        $recentData = $historicalData->take(7);
        $avgDailySales = $recentData->avg('total');
        
        return $avgDailySales * $days;
    }

    /**
     * Recommend product mix based on gap
     */
    private function recommendProductMix($tenantId, $gap)
    {
        // Get top-selling products by revenue
        $topProducts = DB::connection('tenant')
            ->table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('products.tenant_id', $tenantId)
            ->selectRaw('product_id, SUM(quantity) as total_qty, 
                        SUM(total) as total_revenue,
                        AVG(products.sell_price) as avg_price,
                        AVG(products.buy_price) as avg_cost')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(20)
            ->get();
        
        // Calculate recommended quantities
        $productMix = [];
        $remainingGap = $gap;
        
        foreach ($topProducts as $product) {
            if ($remainingGap <= 0) {
                break;
            }
            
            // Calculate how many units needed based on avg price
            $unitsNeeded = min(
                ceil($remainingGap / $product->avg_price),
                100 // Cap at 100 units per product
            );
            
            $revenue = $unitsNeeded * $product->avg_price;
            $cost = $unitsNeeded * $product->avg_cost;
            $profit = $revenue - $cost;
            
            $productMix[] = [
                'product_id' => $product->product_id,
                'recommended_qty' => $unitsNeeded,
                'unit_cost' => $product->avg_cost,
                'total_cost' => $cost,
                'expected_revenue' => $revenue,
                'expected_profit' => $profit,
                'priority' => count($productMix) + 1,
            ];
            
            $remainingGap -= $revenue;
        }
        
        return $productMix;
    }

    /**
     * Calculate total cost from product mix
     */
    private function calculateTotalCost($productMix)
    {
        return collect($productMix)->sum('total_cost');
    }

    /**
     * Calculate break-even date
     */
    private function calculateBreakEvenDate($tenantId, $targetRevenue)
    {
        // Get average daily revenue
        $avgDailyRevenue = DB::connection('tenant')
            ->table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('date', '>=', now()->subDays(30))
            ->avg('grand_total');
        
        if ($avgDailyRevenue <= 0) {
            return now()->addDays(30);
        }
        
        $daysToBreakEven = ceil($targetRevenue / $avgDailyRevenue);
        
        return now()->addDays($daysToBreakEven);
    }

    /**
     * Get active forecast target
     */
    public function getActiveTarget($tenantId)
    {
        return ForecastTarget::with('items.product')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->latest('generated_at')
            ->first();
    }
}
```

**Verify:**
```bash
php artisan tinker
>>> $service = new \App\Services\ForecastTargetService();
>>> $result = $service->calculateFromTarget(1, 100000000, 30);
>>> print_r($result);
```

**Done When:**
- Service created
- All methods implemented
- Calculations accurate
- No syntax errors

---

### Task 4: Create API Controller
**Type:** `auto` | **Effort:** `medium`

**Files:**
- `app/Http/Controllers/Api/ForecastTargetController.php`

**Action:**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ForecastTargetService;
use Illuminate\Http\Request;

class ForecastTargetController extends Controller
{
    protected $forecastTargetService;

    public function __construct(ForecastTargetService $forecastTargetService)
    {
        $this->forecastTargetService = $forecastTargetService;
    }

    /**
     * Calculate forecast from target
     * POST /api/forecast/calculate-target
     */
    public function calculateTarget(Request $request)
    {
        $validated = $request->validate([
            'target_revenue' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        $tenantId = auth()->user()->tenant_id;
        
        $result = $this->forecastTargetService->calculateFromTarget(
            $tenantId,
            $validated['target_revenue'],
            $validated['duration_days']
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Save forecast target
     * POST /api/forecast/save-target
     */
    public function saveTarget(Request $request)
    {
        $validated = $request->validate([
            'target_revenue' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'current_trajectory' => 'numeric|min:0',
            'gap' => 'numeric',
            'product_mix' => 'array',
        ]);

        $tenantId = auth()->user()->tenant_id;
        
        $result = $this->forecastTargetService->saveTarget($tenantId, $validated);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['target'],
                'message' => 'Forecast target saved successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Get active target
     * GET /api/forecast/active-target
     */
    public function getActiveTarget()
    {
        $tenantId = auth()->user()->tenant_id;
        
        $target = $this->forecastTargetService->getActiveTarget($tenantId);

        if (!$target) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No active forecast target',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $target,
        ]);
    }

    /**
     * Update target progress
     * POST /api/forecast/update-progress
     */
    public function updateProgress(Request $request)
    {
        $validated = $request->validate([
            'target_id' => 'required|exists:forecast_targets,id',
            'current_trajectory' => 'required|numeric|min:0',
        ]);

        $target = \App\Models\ForecastTarget::findOrFail($validated['target_id']);
        
        // Verify tenant ownership
        if ($target->tenant_id !== auth()->user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $target->update([
            'current_trajectory' => $validated['current_trajectory'],
            'gap' => $target->target_revenue - $validated['current_trajectory'],
        ]);

        // Check if achieved
        if ($target->current_trajectory >= $target->target_revenue) {
            $target->update([
                'status' => 'achieved',
                'achieved_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $target->fresh(),
            'message' => 'Progress updated successfully',
        ]);
    }
}
```

**Verify:**
```bash
php artisan tinker
>>> $controller = new \App\Http\Controllers\Api\ForecastTargetController();
```

**Done When:**
- Controller created
- All methods implemented
- Validation working
- Error handling complete

---

### Task 5: Add API Routes
**Type:** `auto` | **Effort:** `low`

**Files:**
- `routes/api.php`

**Action:**

Add routes after existing forecast routes:

```php
// Forecast Target Routes
Route::prefix('forecast')->group(function () {
    Route::post('/calculate-target', [\App\Http\Controllers\Api\ForecastTargetController::class , 'calculateTarget']);
    Route::post('/save-target', [\App\Http\Controllers\Api\ForecastTargetController::class , 'saveTarget']);
    Route::get('/active-target', [\App\Http\Controllers\Api\ForecastTargetController::class , 'getActiveTarget']);
    Route::post('/update-progress', [\App\Http\Controllers\Api\ForecastTargetController::class , 'updateProgress']);
});
```

**Verify:**
```bash
php artisan route:list --path=forecast
```

**Done When:**
- Routes registered
- Route list shows all 4 endpoints
- Middleware applied correctly

---

## Success Criteria

- [ ] Migrations run successfully
- [ ] Models created with relationships
- [ ] Service calculates correctly
- [ ] Controller endpoints functional
- [ ] Routes registered
- [ ] API tests pass

---

## 🧪 Verification Commands

```bash
# 1. Run migrations
php artisan migrate

# 2. Check routes
php artisan route:list --path=forecast

# 3. Test API (with auth token)
curl -X POST http://localhost/api/forecast/calculate-target \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"target_revenue":100000000,"duration_days":30}'

# 4. Test in tinker
php artisan tinker
>>> $service = new \App\Services\ForecastTargetService();
>>> $result = $service->calculateFromTarget(1, 100000000, 30);
>>> print_r($result);
```

---

**Ready for implementation!**
