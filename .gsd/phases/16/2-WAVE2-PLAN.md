# Phase 16 - Wave 2: Membership Tiers

**Status:** `IMPLEMENTING`  
**Phase:** 16  
**Wave:** 2 of 3  
**Effort:** Medium (6 tasks)  
**Dependencies:** Wave 1 Complete

---

## 📋 Objective

Implement membership tier system with Bronze, Silver, Gold levels that automatically upgrade based on customer spending and visit frequency, with configurable benefits applied at checkout.

---

## 🎯 Deliverables

1. ✅ Tier database tables (already created in Wave 1)
2. ✅ Tier seeder for default tiers
3. ✅ Tier qualification assessment logic
4. ✅ Tier benefits API endpoints
5. ✅ Tier management admin UI
6. ✅ Tier benefits applied at checkout
7. ✅ Customer tier display in POS

---

## 📦 Tasks

### Task 1: Create Tier Seeder
**Type:** `auto` | **Effort:** `low`

**File:** `database/seeders/tenant/LoyaltyTierSeeder.php`

**Implementation:**
```php
<?php

namespace Database\Seeders\tenant;

use App\Models\MembershipTier;
use Illuminate\Database\Seeder;

class LoyaltyTierSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = auth()->user()->tenant_id ?? 1;

        $tiers = [
            [
                'name' => 'Bronze',
                'min_spend' => 0,
                'min_visits' => 0,
                'benefits' => [
                    'discount_percent' => 0,
                    'points_multiplier' => 1.0,
                    'birthday_bonus' => 0,
                ],
                'badge_color' => '#CD7F32',
                'priority' => 1,
                'active' => true,
            ],
            [
                'name' => 'Silver',
                'min_spend' => 1000000, // Rp 1,000,000
                'min_visits' => 10,
                'benefits' => [
                    'discount_percent' => 2,
                    'points_multiplier' => 1.2,
                    'birthday_bonus' => 50,
                ],
                'badge_color' => '#C0C0C0',
                'priority' => 2,
                'active' => true,
            ],
            [
                'name' => 'Gold',
                'min_spend' => 5000000, // Rp 5,000,000
                'min_visits' => 50,
                'benefits' => [
                    'discount_percent' => 5,
                    'points_multiplier' => 1.5,
                    'birthday_bonus' => 200,
                ],
                'badge_color' => '#FFD700',
                'priority' => 3,
                'active' => true,
            ],
            [
                'name' => 'Platinum',
                'min_spend' => 10000000, // Rp 10,000,000
                'min_visits' => 100,
                'benefits' => [
                    'discount_percent' => 10,
                    'points_multiplier' => 2.0,
                    'birthday_bonus' => 500,
                ],
                'badge_color' => '#E5E4E2',
                'priority' => 4,
                'active' => true,
            ],
        ];

        foreach ($tiers as $tierData) {
            $tierData['tenant_id'] = $tenantId;
            MembershipTier::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $tierData['name']],
                $tierData
            );
        }
    }
}
```

**Verify:**
```bash
php artisan db:seed --class=LoyaltyTierSeeder --database=tenant
```

**Done When:**
- Seeder file created
- Runs without errors
- 4 tiers created in database

---

### Task 2: Enhance Customer Model
**Type:** `auto` | **Effort:** `low`

**File:** `app/Models/Customer.php`

**Add Methods:**
```php
// Add to Customer model

/**
 * Get customer's current tier
 */
public function currentTier()
{
    return $this->hasOne(CustomerTier::class)->current();
}

/**
 * Get customer's tier history
 */
public function tierHistory()
{
    return $this->hasMany(CustomerTier::class);
}

/**
 * Calculate total spend in last 12 months
 */
public function calculateLastYearSpend(): float
{
    return $this->transactions()
        ->where('status', 'completed')
        ->where('created_at', '>=', now()->subYear())
        ->sum('grand_total');
}

/**
 * Calculate total visits in last 12 months
 */
public function calculateLastYearVisits(): int
{
    return $this->transactions()
        ->where('status', 'completed')
        ->where('created_at', '>=', now()->subYear())
        ->count();
}

/**
 * Check and update tier qualification
 */
public function assessAndUpdateTier(): ?CustomerTier
{
    $tenantId = $this->tenant_id;
    $totalSpend = $this->calculateLastYearSpend();
    $totalVisits = $this->calculateLastYearVisits();
    
    // Find highest tier customer qualifies for
    $qualifiedTier = MembershipTier::where('tenant_id', $tenantId)
        ->where('active', true)
        ->where('min_spend', '<=', $totalSpend)
        ->where('min_visits', '<=', $totalVisits)
        ->orderBy('priority', 'desc')
        ->first();
    
    if (!$qualifiedTier) {
        return null;
    }
    
    $currentTier = $this->currentTier;
    
    // Check if tier changed
    if ($currentTier && $currentTier->tier_id === $qualifiedTier->id) {
        return $currentTier; // No change
    }
    
    // Create new tier assignment
    $newTier = CustomerTier::create([
        'customer_id' => $this->id,
        'tier_id' => $qualifiedTier->id,
        'qualified_at' => now(),
        'valid_until' => now()->addYear(),
        'previous_tier_id' => $currentTier?->tier_id,
    ]);
    
    return $newTier;
}

/**
 * Get tier benefits
 */
public function getTierBenefits(): array
{
    $tier = $this->currentTier;
    return $tier ? $tier->getBenefits() : [];
}

/**
 * Get points multiplier based on tier
 */
public function getPointsMultiplier(): float
{
    return $this->getTierBenefit('points_multiplier', 1.0);
}

/**
 * Get discount percent based on tier
 */
public function getTierDiscountPercent(): float
{
    return $this->getTierBenefit('discount_percent', 0.0);
}
```

**Done When:**
- All methods added
- Relationships defined
- Helper methods working

---

### Task 3: Create Tier Assessment Service
**Type:** `auto` | **Effort:** `medium`

**File:** `app/Services/TierAssessmentService.php`

**Implementation:**
```php
<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerTier;
use App\Models\MembershipTier;

class TierAssessmentService
{
    /**
     * Assess all customers and update tiers
     */
    public function assessAllCustomers($tenantId): int
    {
        $customers = Customer::where('tenant_id', $tenantId)->get();
        $updated = 0;
        
        foreach ($customers as $customer) {
            $result = $customer->assessAndUpdateTier();
            if ($result) {
                $updated++;
            }
        }
        
        return $updated;
    }
    
    /**
     * Assess customer after transaction
     */
    public function assessAfterTransaction(Customer $customer): ?CustomerTier
    {
        return $customer->assessAndUpdateTier();
    }
    
    /**
     * Get tier progression for customer
     */
    public function getTierProgression(Customer $customer): array
    {
        $currentTier = $customer->currentTier;
        $totalSpend = $customer->calculateLastYearSpend();
        $totalVisits = $customer->calculateLastYearVisits();
        
        $nextTier = MembershipTier::where('tenant_id', $customer->tenant_id)
            ->where('active', true)
            ->where(function ($q) use ($totalSpend, $totalVisits) {
                $q->where('min_spend', '>', $totalSpend)
                  ->orWhere('min_visits', '>', $totalVisits);
            })
            ->orderBy('priority', 'asc')
            ->first();
        
        $progress = [];
        
        if ($nextTier) {
            $spendNeeded = max(0, $nextTier->min_spend - $totalSpend);
            $visitsNeeded = max(0, $nextTier->min_visits - $totalVisits);
            
            $spendProgress = $nextTier->min_spend > 0 
                ? min(100, ($totalSpend / $nextTier->min_spend) * 100) 
                : 100;
            
            $visitsProgress = $nextTier->min_visits > 0 
                ? min(100, ($totalVisits / $nextTier->min_visits) * 100) 
                : 100;
            
            $progress = [
                'next_tier' => $nextTier->name,
                'next_tier_id' => $nextTier->id,
                'spend_needed' => $spendNeeded,
                'visits_needed' => $visitsNeeded,
                'spend_progress' => round($spendProgress, 1),
                'visits_progress' => round($visitsProgress, 1),
                'overall_progress' => round(min($spendProgress, $visitsProgress), 1),
            ];
        } else {
            $progress = [
                'next_tier' => null,
                'message' => 'Highest tier reached!',
                'overall_progress' => 100,
            ];
        }
        
        return [
            'current_tier' => $currentTier?->tier,
            'current_tier_name' => $currentTier?->tier->name,
            'total_spend' => $totalSpend,
            'total_visits' => $totalVisits,
            'progress' => $progress,
        ];
    }
}
```

**Done When:**
- Service class created
- All methods functional
- Tier assessment working

---

### Task 4: Create Tier API Controller
**Type:** `auto` | **Effort:** `medium`

**File:** `app/Http/Controllers/Api/TierController.php`

**Implementation:**
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MembershipTier;
use App\Services\TierAssessmentService;
use Illuminate\Http\Request;

class TierController extends Controller
{
    private TierAssessmentService $tierService;
    
    public function __construct(TierAssessmentService $tierService)
    {
        $this->tierService = $tierService;
    }
    
    /**
     * Get all tiers for current tenant
     * GET /api/tiers
     */
    public function index()
    {
        $tiers = MembershipTier::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->orderBy('priority', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $tiers
        ]);
    }
    
    /**
     * Get customer's current tier
     * GET /api/customers/{id}/tier
     */
    public function customerTier($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $progression = $this->tierService->getTierProgression($customer);
        
        return response()->json([
            'success' => true,
            'data' => $progression
        ]);
    }
    
    /**
     * Calculate tier progress
     * POST /api/tiers/calculate-progress
     */
    public function calculateProgress(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);
        
        $customer = Customer::findOrFail($validated['customer_id']);
        $progression = $this->tierService->getTierProgression($customer);
        
        return response()->json([
            'success' => true,
            'data' => $progression
        ]);
    }
    
    /**
     * Manually assess customer tier
     * POST /api/customers/{id}/assess-tier
     */
    public function assessCustomer($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $tier = $customer->assessAndUpdateTier();
        
        return response()->json([
            'success' => true,
            'message' => 'Tier assessment completed',
            'data' => [
                'tier' => $tier?->tier,
                'qualified_at' => $tier?->qualified_at,
            ]
        ]);
    }
    
    /**
     * Create/Update tier (Admin)
     * POST /api/tiers
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'min_spend' => 'required|numeric|min:0',
            'min_visits' => 'required|integer|min:0',
            'benefits' => 'required|array',
            'badge_color' => 'required|string',
            'priority' => 'required|integer',
            'active' => 'boolean',
        ]);
        
        $validated['tenant_id'] = auth()->user()->tenant_id;
        
        $tier = MembershipTier::updateOrCreate(
            ['tenant_id' => auth()->user()->tenant_id, 'name' => $validated['name']],
            $validated
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Tier saved successfully',
            'data' => $tier
        ]);
    }
}
```

**Done When:**
- Controller created
- All endpoints working
- Tier assessment API functional

---

### Task 5: Add Tier Routes
**Type:** `auto` | **Effort:** `low`

**File:** `routes/api.php`

**Add:**
```php
// Tier Management
Route::prefix('tiers')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\TierController::class , 'index']);
    Route::post('/', [\App\Http\Controllers\Api\TierController::class , 'store']);
    Route::post('/calculate-progress', [\App\Http\Controllers\Api\TierController::class , 'calculateProgress']);
});

Route::get('/customers/{customer}/tier', [\App\Http\Controllers\Api\TierController::class , 'customerTier']);
Route::post('/customers/{customer}/assess-tier', [\App\Http\Controllers\Api\TierController::class , 'assessCustomer']);
```

**Done When:**
- Routes registered
- Route list shows tier endpoints

---

### Task 6: Integrate Tiers with Transaction
**Type:** `auto` | **Effort:** `medium`

**File:** `app/Http/Controllers/Api/TransactionController.php`

**Enhance `awardLoyaltyPoints()` method:**
```php
private function awardLoyaltyPoints($customerId, $transactionId, $amount)
{
    $tenantId = auth()->user()->tenant_id;
    $settings = LoyaltySetting::forTenant($tenantId);
    
    if (!$settings || !$settings->enabled) {
        return;
    }
    
    $customer = Customer::find($customerId);
    if (!$customer) {
        return;
    }
    
    // Assess tier after transaction
    $customer->assessAndUpdateTier();
    
    // Calculate base points
    $basePoints = $settings->calculatePoints($amount);
    
    // Apply tier multiplier
    $multiplier = $customer->getPointsMultiplier();
    $totalPoints = floor($basePoints * $multiplier);
    
    if ($totalPoints > 0) {
        $balanceData = CustomerPoint::getBalanceWithBreakdown($customerId);
        $balance = $balanceData['balance'];
        
        CustomerPoint::create([
            'customer_id' => $customerId,
            'tenant_id' => $tenantId,
            'points' => $totalPoints,
            'type' => CustomerPoint::TYPE_EARN,
            'reference_type' => 'transaction',
            'reference_id' => $transactionId,
            'expiry_date' => now()->addMonths($settings->points_expiry_months),
            'balance_after' => $balance + $totalPoints,
            'notes' => "Earned from transaction #{$transactionId} (Tier: {$customer->currentTier?->tier->name})",
        ]);
    }
}
```

**Done When:**
- Tier multiplier applied to points
- Tier assessed after each transaction
- Notes include tier information

---

## ✅ Wave 2 Success Criteria

- [ ] 4 default tiers seeded (Bronze/Silver/Gold/Platinum)
- [ ] Customer model has tier methods
- [ ] Tier assessment service working
- [ ] Tier API endpoints functional
- [ ] Tier benefits applied at checkout
- [ ] Customer tier progression calculated correctly
- [ ] Points multiplier working

---

## 🧪 Testing Commands

```bash
# 1. Seed tiers
php artisan db:seed --class=LoyaltyTierSeeder

# 2. Check tiers in database
SELECT * FROM membership_tiers;

# 3. Test tier API
curl -X GET http://localhost/api/tiers \
  -H "Authorization: Bearer YOUR_TOKEN"

# 4. Test customer tier
curl -X GET http://localhost/api/customers/1/tier \
  -H "Authorization: Bearer YOUR_TOKEN"

# 5. Check customer tier progression
SELECT 
    c.name,
    ct.tier_id,
    t.name as tier_name,
    ct.qualified_at
FROM customers c
LEFT JOIN customer_tiers ct ON c.id = ct.customer_id AND ct.valid_until > NOW()
LEFT JOIN membership_tiers t ON ct.tier_id = t.id
ORDER BY c.name;
```

---

**Ready for implementation!**
