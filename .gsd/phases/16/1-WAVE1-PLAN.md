# Phase 16 - Wave 1: Core Points System

**Status**: `PLANNING`  
**Phase**: 16  
**Wave**: 1 of 3 (for Loyalty Program)  
**Effort**: Medium (5-7 tasks)  
**Dependencies**: Phase 13 (POS), Phase 14 (Analytics)

---

## 📋 Objective

Implement the foundational points earning and redemption system, enabling customers to earn loyalty points on purchases and redeem them for discounts.

---

## 🎯 Deliverables

1. ✅ Database migrations for loyalty tables
2. ✅ Eloquent models with relationships
3. ✅ Loyalty settings API (CRUD)
4. ✅ Customer points API (balance, history)
5. ✅ Points calculation service
6. ✅ Points redemption at checkout
7. ✅ Admin settings UI

---

## 📦 Tasks

### Task 1: Create Database Migrations
**Type**: `auto` | **Effort**: `low`

**Files:**
- `database/migrations/tenant/2026_02_21_000001_create_loyalty_tables.php`

**Tables to Create:**
1. `loyalty_settings` - Tenant configuration
2. `customer_points` - Points ledger
3. `membership_tiers` - Tier definitions (prep for Wave 2)
4. `customer_tiers` - Customer tier assignments (prep for Wave 2)
5. `reward_catalog` - Rewards (prep for Wave 3)
6. `customer_rewards` - Redemptions (prep for Wave 3)

**Implementation:**
```php
// loyalty_settings
Schema::create('loyalty_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
    $table->decimal('earn_rate', 15, 2)->default(10000); // 1 point per 10k
    $table->string('earn_currency', 3)->default('IDR');
    $table->decimal('point_value', 15, 4)->default(100); // 1 point = 100 IDR
    $table->integer('min_redemption_points')->default(100);
    $table->decimal('max_redemption_percent', 5, 2)->default(50.00);
    $table->integer('points_expiry_months')->default(12);
    $table->boolean('enabled')->default(true);
    $table->timestamps();
    
    $table->unique('tenant_id');
});
```

**Verify:**
```bash
php artisan migrate --force
# Check tables exist
```

**Done When:**
- All 6 tables created successfully
- Migrations rollback successfully
- Foreign keys properly configured

---

### Task 2: Create Loyalty Models
**Type**: `auto` | **Effort**: `low`

**Files:**
- `app/Models/LoyaltySetting.php`
- `app/Models/CustomerPoint.php`
- `app/Models/MembershipTier.php`
- `app/Models/CustomerTier.php`
- `app/Models/Reward.php`
- `app/Models/CustomerReward.php`

**Implementation:**
```php
// LoyaltySetting.php
class LoyaltySetting extends Model
{
    protected $connection = 'tenant';
    
    protected $fillable = [
        'tenant_id',
        'earn_rate',
        'earn_currency',
        'point_value',
        'min_redemption_points',
        'max_redemption_percent',
        'points_expiry_months',
        'enabled',
    ];
    
    protected $casts = [
        'earn_rate' => 'decimal:2',
        'point_value' => 'decimal:4',
        'min_redemption_points' => 'integer',
        'max_redemption_percent' => 'decimal:2',
        'points_expiry_months' => 'integer',
        'enabled' => 'boolean',
    ];
    
    public static function forTenant($tenantId): ?self
    {
        return static::where('tenant_id', $tenantId)->first();
    }
}

// CustomerPoint.php
class CustomerPoint extends Model
{
    protected $connection = 'tenant';
    
    protected $fillable = [
        'customer_id',
        'tenant_id',
        'points',
        'type',
        'reference_type',
        'reference_id',
        'expiry_date',
        'balance_after',
        'notes',
    ];
    
    protected $casts = [
        'points' => 'decimal:2',
        'expiry_date' => 'datetime',
        'balance_after' => 'decimal:2',
    ];
    
    const TYPE_EARN = 'earn';
    const TYPE_REDEEM = 'redeem';
    const TYPE_ADJUST = 'adjust';
    const TYPE_EXPIRE = 'expire';
    const TYPE_REFUND = 'refund';
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function reference()
    {
        return $this->morphTo();
    }
}
```

**Verify:**
```bash
# Test model instantiation
php artisan tinker
>>> \App\Models\LoyaltySetting::first()
```

**Done When:**
- All 6 models created
- Relationships defined
- Fillable and casts configured
- No syntax errors

---

### Task 3: Create LoyaltyController
**Type**: `auto` | **Effort**: `medium`

**File:**
- `app/Http/Controllers/Api/LoyaltyController.php`

**Methods:**
```php
class LoyaltyController extends Controller
{
    /**
     * Get loyalty settings for current tenant
     * GET /api/loyalty/settings
     */
    public function settings()
    {
        $settings = LoyaltySetting::forTenant(auth()->user()->tenant_id);
        
        if (!$settings) {
            // Create default settings
            $settings = LoyaltySetting::create([
                'tenant_id' => auth()->user()->tenant_id,
                'earn_rate' => 10000,
                'point_value' => 100,
                'min_redemption_points' => 100,
                'max_redemption_percent' => 50.00,
                'points_expiry_months' => 12,
                'enabled' => true,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }
    
    /**
     * Update loyalty settings
     * POST /api/loyalty/settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'earn_rate' => 'required|numeric|min:1',
            'point_value' => 'required|numeric|min:0.01',
            'min_redemption_points' => 'required|integer|min:0',
            'max_redemption_percent' => 'required|numeric|between:0,100',
            'points_expiry_months' => 'required|integer|min:0',
            'enabled' => 'boolean',
        ]);
        
        $settings = LoyaltySetting::where('tenant_id', auth()->user()->tenant_id)->firstOrFail();
        $settings->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Loyalty settings updated',
            'data' => $settings
        ]);
    }
    
    /**
     * Get customer points balance
     * GET /api/customers/{id}/points
     */
    public function customerPoints($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        
        // Calculate total earned
        $totalEarned = CustomerPoint::where('customer_id', $customerId)
            ->whereIn('type', [CustomerPoint::TYPE_EARN])
            ->sum('points');
        
        // Calculate total redeemed
        $totalRedeemed = CustomerPoint::where('customer_id', $customerId)
            ->whereIn('type', [CustomerPoint::TYPE_REDEEM])
            ->sum('points');
        
        // Calculate expired
        $totalExpired = CustomerPoint::where('customer_id', $customerId)
            ->where('type', CustomerPoint::TYPE_EXPIRE)
            ->sum('points');
        
        $balance = $totalEarned - $totalRedeemed - $totalExpired;
        
        // Get expiring soon (within 30 days)
        $expiringSoon = CustomerPoint::where('customer_id', $customerId)
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->sum('points');
        
        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'balance' => $balance,
                'total_earned' => $totalEarned,
                'total_redeemed' => $totalRedeemed,
                'total_expired' => $totalExpired,
                'expiring_soon' => $expiringSoon,
            ]
        ]);
    }
    
    /**
     * Get customer points history
     * GET /api/customers/{id}/points/history
     */
    public function pointsHistory($customerId, Request $request)
    {
        $limit = $request->get('limit', 50);
        
        $history = CustomerPoint::where('customer_id', $customerId)
            ->with('reference')
            ->latest()
            ->paginate($limit);
        
        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
    
    /**
     * Calculate points for a transaction
     * POST /api/loyalty/calculate
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'total_amount' => 'required|numeric|min:0',
        ]);
        
        $settings = LoyaltySetting::forTenant(auth()->user()->tenant_id);
        
        if (!$settings || !$settings->enabled) {
            return response()->json([
                'success' => true,
                'data' => [
                    'points' => 0,
                    'message' => 'Loyalty program disabled'
                ]
            ]);
        }
        
        $points = floor($validated['total_amount'] / $settings->earn_rate);
        
        return response()->json([
            'success' => true,
            'data' => [
                'points' => $points,
                'earn_rate' => $settings->earn_rate,
                'total_amount' => $validated['total_amount'],
            ]
        ]);
    }
    
    /**
     * Redeem points
     * POST /api/loyalty/redeem
     */
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
            'transaction_id' => 'nullable|exists:transactions,id',
        ]);
        
        DB::connection('tenant')->beginTransaction();
        
        try {
            $customer = Customer::findOrFail($validated['customer_id']);
            $settings = LoyaltySetting::forTenant(auth()->user()->tenant_id);
            
            // Check minimum redemption
            if ($validated['points'] < $settings->min_redemption_points) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum redemption is ' . $settings->min_redemption_points . ' points'
                ], 400);
            }
            
            // Calculate current balance
            $totalEarned = CustomerPoint::where('customer_id', $validated['customer_id'])
                ->whereIn('type', [CustomerPoint::TYPE_EARN])
                ->sum('points');
            
            $totalRedeemed = CustomerPoint::where('customer_id', $validated['customer_id'])
                ->whereIn('type', [CustomerPoint::TYPE_REDEEM])
                ->sum('points');
            
            $totalExpired = CustomerPoint::where('customer_id', $validated['customer_id'])
                ->where('type', CustomerPoint::TYPE_EXPIRE)
                ->sum('points');
            
            $balance = $totalEarned - $totalRedeemed - $totalExpired;
            
            // Check sufficient balance
            if ($validated['points'] > $balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient points balance'
                ], 400);
            }
            
            // Calculate value
            $value = $validated['points'] * $settings->point_value;
            
            // Create redemption record
            CustomerPoint::create([
                'customer_id' => $validated['customer_id'],
                'tenant_id' => auth()->user()->tenant_id,
                'points' => -$validated['points'],
                'type' => CustomerPoint::TYPE_REDEEM,
                'reference_type' => 'transaction',
                'reference_id' => $validated['transaction_id'] ?? null,
                'expiry_date' => null,
                'balance_after' => $balance - $validated['points'],
                'notes' => 'Redeemed for discount',
            ]);
            
            DB::connection('tenant')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Points redeemed successfully',
                'data' => [
                    'points_redeemed' => $validated['points'],
                    'discount_value' => $value,
                    'new_balance' => $balance - $validated['points'],
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

**Verify:**
```bash
# Test API endpoints
curl -X GET /api/loyalty/settings
curl -X GET /api/customers/1/points
```

**Done When:**
- All 5 methods implemented
- Validation working
- Error handling complete
- Tests pass

---

### Task 4: Add API Routes
**Type**: `auto` | **Effort**: `low`

**File:**
- `routes/api.php`

**Implementation:**
```php
// Loyalty Program Routes
Route::prefix('loyalty')->group(function () {
    Route::get('/settings', [\App\Http\Controllers\Api\LoyaltyController::class , 'settings']);
    Route::post('/settings', [\App\Http\Controllers\Api\LoyaltyController::class , 'updateSettings']);
    Route::post('/calculate', [\App\Http\Controllers\Api\LoyaltyController::class , 'calculate']);
    Route::post('/redeem', [\App\Http\Controllers\Api\LoyaltyController::class , 'redeem']);
});

// Customer Points Routes
Route::get('/customers/{customer}/points', [\App\Http\Controllers\Api\LoyaltyController::class , 'customerPoints']);
Route::get('/customers/{customer}/points/history', [\App\Http\Controllers\Api\LoyaltyController::class , 'pointsHistory']);
```

**Verify:**
```bash
php artisan route:list --path=loyalty
```

**Done When:**
- All routes registered
- Routes appear in route list
- Middleware applied correctly

---

### Task 5: Integrate Points into TransactionController
**Type**: `auto` | **Effort**: `medium`

**File:**
- `app/Http/Controllers/Api/TransactionController.php`

**Changes:**
```php
// Add at the end of store() method, after transaction is created
// Award points to customer
if ($request->customer_id) {
    $this->awardPoints(
        $request->customer_id,
        $transaction->id,
        $transaction->grand_total
    );
}

// Add new method
private function awardPoints($customerId, $transactionId, $amount)
{
    $settings = LoyaltySetting::forTenant(auth()->user()->tenant_id);
    
    if (!$settings || !$settings->enabled) {
        return;
    }
    
    $points = floor($amount / $settings->earn_rate);
    
    if ($points > 0) {
        // Calculate current balance
        $totalEarned = CustomerPoint::where('customer_id', $customerId)
            ->whereIn('type', [CustomerPoint::TYPE_EARN])
            ->sum('points');
        
        $totalRedeemed = CustomerPoint::where('customer_id', $customerId)
            ->whereIn('type', [CustomerPoint::TYPE_REDEEM])
            ->sum('points');
        
        $totalExpired = CustomerPoint::where('customer_id', $customerId)
            ->where('type', CustomerPoint::TYPE_EXPIRE)
            ->sum('points');
        
        $balance = $totalEarned - $totalRedeemed - $totalExpired;
        
        // Create points record
        CustomerPoint::create([
            'customer_id' => $customerId,
            'tenant_id' => auth()->user()->tenant_id,
            'points' => $points,
            'type' => CustomerPoint::TYPE_EARN,
            'reference_type' => 'transaction',
            'reference_id' => $transactionId,
            'expiry_date' => now()->addMonths($settings->points_expiry_months),
            'balance_after' => $balance + $points,
            'notes' => 'Earned from transaction ' . $transactionId,
        ]);
    }
}
```

**Add use statement:**
```php
use App\Models\LoyaltySetting;
use App\Models\CustomerPoint;
```

**Verify:**
```bash
# Create test transaction
# Check customer_points table has new record
```

**Done When:**
- Points awarded on transaction completion
- Points calculated correctly
- Expiry date set properly
- Balance after calculated

---

### Task 6: Create Admin Settings UI
**Type**: `auto` | **Effort**: `medium`

**File:**
- `resources/views/pages/settings/loyalty.blade.php` (NEW)

**Implementation:**
```blade
@extends('layouts.app')

@section('title', 'Loyalty Settings')

@section('content')
<div x-data="loyaltySettings()" x-init="init()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Loyalty Program Settings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Configure points earning and redemption rules</p>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Enable/Disable -->
            <div class="md:col-span-2">
                <label class="flex items-center gap-3">
                    <input type="checkbox" x-model="settings.enabled"
                        class="w-5 h-5 text-brand-600 rounded focus:ring-brand-500">
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Enable Loyalty Program</span>
                </label>
            </div>
            
            <!-- Earn Rate -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Earn Rate (Amount per Point)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                    <input type="number" x-model="settings.earn_rate"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                </div>
                <p class="text-xs text-gray-500 mt-1">Customer earns 1 point per Rp <span x-text="settings.earn_rate"></span></p>
            </div>
            
            <!-- Point Value -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Point Value (Redemption)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                    <input type="number" x-model="settings.point_value" step="0.01"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                </div>
                <p class="text-xs text-gray-500 mt-1">1 point = Rp <span x-text="settings.point_value"></span> discount</p>
            </div>
            
            <!-- Min Redemption -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Minimum Points for Redemption
                </label>
                <input type="number" x-model="settings.min_redemption_points"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-500 mt-1">Customer must have at least this many points to redeem</p>
            </div>
            
            <!-- Max Redemption % -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Maximum Redemption (%)
                </label>
                <input type="number" x-model="settings.max_redemption_percent" step="0.01"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-500 mt-1">Max percentage of bill that can be paid with points</p>
            </div>
            
            <!-- Points Expiry -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Points Expiry (Months)
                </label>
                <input type="number" x-model="settings.points_expiry_months"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-500 mt-1">Points expire after this many months</p>
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="mt-6 flex justify-end gap-3">
            <button @click="saveSettings()"
                class="px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-medium">
                Save Settings
            </button>
        </div>
    </div>
</div>

<script>
function loyaltySettings() {
    return {
        settings: {
            enabled: true,
            earn_rate: 10000,
            point_value: 100,
            min_redemption_points: 100,
            max_redemption_percent: 50.00,
            points_expiry_months: 12,
        },
        
        async init() {
            await this.loadSettings();
        },
        
        async loadSettings() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/loyalty/settings', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.settings = data.data;
                }
            } catch (e) {
                console.error('Load settings error:', e);
            }
        },
        
        async saveSettings() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/loyalty/settings', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.settings)
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'Loyalty settings saved successfully',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                console.error('Save error:', e);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to save settings'
                });
            }
        }
    }
}
</script>
@endsection
```

**Verify:**
- Settings page loads
- Data fetches from API
- Save updates database

**Done When:**
- UI renders correctly
- Settings load from API
- Save button works
- Success/error notifications show

---

### Task 7: Add Settings Menu Item
**Type**: `auto` | **Effort**: `low`

**File:**
- `app/Modules/Retail/Config/menu.php`

**Implementation:**
```php
[
    'label' => 'Loyalty Program',
    'route' => 'settings.loyalty',
    'icon' => '<path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />',
    'roles' => ['Owner', 'Manager'],
]
```

**Web Route:**
- `routes/web.php`
```php
Route::get('/settings/loyalty', function () {
    return view('pages.settings.loyalty');
})->name('settings.loyalty');
```

**Verify:**
- Menu appears in sidebar
- Route accessible

**Done When:**
- Menu item visible
- Page loads without errors

---

## ✅ Wave 1 Success Criteria

- [ ] All 6 database tables created
- [ ] All 6 models created with relationships
- [ ] LoyaltyController has all 5 methods
- [ ] API routes registered and accessible
- [ ] Points awarded on transaction completion
- [ ] Points redemption works
- [ ] Admin settings UI functional
- [ ] Menu item added

---

## 🧪 Verification Commands

```bash
# 1. Check migrations
php artisan migrate:status

# 2. Check routes
php artisan route:list --path=loyalty

# 3. Test API (with auth token)
curl -X GET http://localhost/api/loyalty/settings
curl -X GET http://localhost/api/customers/1/points

# 4. Test points calculation
curl -X POST http://localhost/api/loyalty/calculate \
  -H "Content-Type: application/json" \
  -d '{"customer_id":1,"total_amount":50000}'

# 5. Check database
SELECT * FROM customer_points ORDER BY created_at DESC LIMIT 10;
SELECT * FROM loyalty_settings;
```

---

**Ready for implementation!**
