---
phase: 27
plan: 3
wave: 3
---

# Plan 27.3: POS Pricing Tiers with Auto-Calculation

## Objective
Implement automated pricing tier system in POS with Retail, Wholesale (Grosir), and B2B pricing that auto-calculates based on quantity input, maintaining a clean and intuitive UI/UX.

**Priority:** 🟢 HIGH | **Effort:** High (8-12 hours)  
**Dependencies:** Task 1 complete (404 fixes)

---

## Context
- `.gsd/phases/27/27-OPTIONS.md`
- `resources/views/pages/pos/index.blade.php`
- `app/Models/Product.php`
- `app/Http/Controllers/Api/ProductController.php`
- `.gsd/phases/26/` (Design System reference)

---

## Tasks

### Task 1: Database Schema for Pricing Tiers
**Type:** `auto` | **Effort:** `low`

**Files:**
- `database/migrations/tenant/2026_02_23_000001_add_pricing_tiers_to_products_table.php` (NEW)

**Action:**

1. **Create migration:**
   ```php
   <?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration
   {
       public function up(): void
       {
           Schema::table('products', function (Blueprint $table) {
               $table->boolean('enable_tiered_pricing')->default(false)
                     ->after('price');
               $table->json('pricing_tier_config')->nullable()
                     ->after('enable_tiered_pricing');
           });
       }

       public function down(): void
       {
           Schema::table('products', function (Blueprint $table) {
               $table->dropColumn(['enable_tiered_pricing', 'pricing_tier_config']);
           });
       }
   };
   ```

2. **Run migration:**
   ```bash
   php artisan migrate
   ```

3. **Update Product Model:**
   ```php
   // app/Models/Product.php
   protected $fillable = [
       // ... existing fields
       'enable_tiered_pricing',
       'pricing_tier_config',
   ];

   protected $casts = [
       // ... existing casts
       'enable_tiered_pricing' => 'boolean',
       'pricing_tier_config' => 'array',
   ];

   // Helper method
   public function getPricingTiers()
   {
       if (!$this->enable_tiered_pricing) {
           return [];
       }

       return $this->pricing_tier_config['tiers'] ?? [];
   }

   public function getPriceForQuantity($qty)
   {
       if (!$this->enable_tiered_pricing) {
           return $this->price;
       }

       $tiers = $this->getPricingTiers();
       if (empty($tiers)) {
           return $this->price;
       }

       // Find applicable tier (highest min_qty that qty qualifies for)
       $applicableTier = collect($tiers)
           ->sortByDesc('min_qty')
           ->firstWhere('min_qty', '<=', $qty);

       return $applicableTier ? $applicableTier['price'] : $this->price;
   }
   ```

**Verify:**
```bash
# Check migration ran
php artisan migrate:status

# Test model methods in tinker
php artisan tinker
>>> $product = Product::first();
>>> $product->getPriceForQuantity(10);
```

**Done When:**
- Migration successful
- Model has helper methods
- Fields fillable and casted

---

### Task 2: API Endpoint for Pricing Tiers
**Type:** `auto` | **Effort:** `medium`

**Files:**
- `app/Http/Controllers/Api/ProductController.php`

**Action:**

1. **Add new endpoint:**
   ```php
   /**
    * Get pricing tiers for a product
    * GET /api/products/{id}/pricing-tiers
    */
   public function getPricingTiers($productId, Request $request)
   {
       $product = Product::findOrFail($productId);

       $qty = $request->input('qty', 1);
       $calculatedPrice = $product->getPriceForQuantity($qty);

       return response()->json([
           'success' => true,
           'data' => [
               'product_id' => $product->id,
               'product_name' => $product->name,
               'base_price' => $product->price,
               'enable_tiered_pricing' => $product->enable_tiered_pricing,
               'tiers' => $product->getPricingTiers(),
               'selected_qty' => $qty,
               'calculated_price' => $calculatedPrice,
               'total' => $calculatedPrice * $qty,
               'savings' => ($product->price - $calculatedPrice) * qty,
               'discount_percent' => $product->price > 0
                   ? round((($product->price - $calculatedPrice) / $product->price) * 100, 1)
                   : 0,
           ]
       ]);
   }

   /**
    * Calculate price for quantity
    * POST /api/products/calculate-price
    */
   public function calculatePrice(Request $request)
   {
       $validated = $request->validate([
           'product_id' => 'required|exists:products,id',
           'quantity' => 'required|integer|min:1',
       ]);

       $product = Product::findOrFail($validated['product_id']);
       $unitPrice = $product->getPriceForQuantity($validated['quantity']);

       return response()->json([
           'success' => true,
           'data' => [
               'unit_price' => $unitPrice,
               'quantity' => $validated['quantity'],
               'total' => $unitPrice * $validated['quantity'],
               'base_total' => $product->price * $validated['quantity'],
               'savings' => ($product->price - $unitPrice) * $validated['quantity'],
           ]
       ]);
   }
   ```

2. **Add routes in `api.php`:**
   ```php
   // Pricing Tiers
   Route::get('/products/{product}/pricing-tiers', [\App\Http\Controllers\Api\ProductController::class , 'getPricingTiers']);
   Route::post('/products/calculate-price', [\App\Http\Controllers\Api\ProductController::class , 'calculatePrice']);
   ```

**Verify:**
```bash
# Test API
curl http://localhost/api/products/1/pricing-tiers?qty=25 \
  -H "Authorization: Bearer {token}"

curl -X POST http://localhost/api/products/calculate-price \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":25}'
```

**Done When:**
- API returns pricing tiers
- Auto-calculation works
- Savings calculated correctly

---

### Task 3: Product Edit Modal with Tier Configuration
**Type:** `auto` | **Effort:** `high`

**Files:**
- `resources/views/pages/products/edit-modal.blade.php` (NEW or modify existing)

**Action:**

Create modal for configuring pricing tiers when editing/creating products:

```blade
<!-- Pricing Tiers Configuration Section -->
<div class="mb-6">
    <label class="flex items-center gap-3 mb-4">
        <input type="checkbox" x-model="product.enable_tiered_pricing"
            class="w-5 h-5 text-brand-600 rounded focus:ring-brand-500">
        <span class="font-semibold text-gray-700 dark:text-gray-300">
            Enable Tiered Pricing
        </span>
    </label>

    <div x-show="product.enable_tiered_pricing" class="space-y-4">
        <!-- Tier Cards -->
        <template x-for="(tier, index) in product.pricing_tiers" :key="index">
            <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-xl relative">
                <!-- Remove Button -->
                <button @click="removeTier(index)"
                    class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="grid grid-cols-3 gap-4">
                    <!-- Tier Name -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                            Tier Name
                        </label>
                        <input type="text" x-model="tier.name"
                            placeholder="e.g., Wholesale"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                    </div>

                    <!-- Min Quantity -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                            Min Quantity
                        </label>
                        <input type="number" x-model="tier.min_qty"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                            Price (Rp)
                        </label>
                        <input type="number" x-model="tier.price"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                    </div>
                </div>

                <!-- Discount Badge -->
                <div class="mt-3 flex items-center gap-2">
                    <span class="text-xs text-gray-500">Discount:</span>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold"
                        x-text="calculateDiscount(tier) + '%'"></span>
                </div>
            </div>
        </template>

        <!-- Add Tier Button -->
        <button @click="addTier()"
            class="w-full py-3 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl text-gray-500 hover:border-brand-500 hover:text-brand-600 transition-all font-semibold">
            + Add Pricing Tier
        </button>
    </div>
</div>

<script>
function productForm() {
    return {
        product: {
            enable_tiered_pricing: false,
            pricing_tiers: [
                { name: 'Retail', min_qty: 1, price: 0 },
                { name: 'Wholesale', min_qty: 10, price: 0 },
                { name: 'B2B', min_qty: 50, price: 0 },
            ]
        },

        addTier() {
            this.product.pricing_tiers.push({ name: '', min_qty: 0, price: 0 });
        },

        removeTier(index) {
            this.product.pricing_tiers.splice(index, 1);
        },

        calculateDiscount(tier) {
            const basePrice = this.product.price;
            if (!basePrice || !tier.price) return 0;
            return Math.round(((basePrice - tier.price) / basePrice) * 100);
        }
    }
}
</script>
```

**Verify:**
- Modal opens correctly
- Tiers can be added/removed
- Discount auto-calculates
- Data saves to database

**Done When:**
- Configuration UI functional
- Data persists correctly
- Validation works

---

### Task 4: POS Product Modal with Auto-Pricing
**Type:** `auto` | **Effort:** `high`

**Files:**
- `resources/views/pages/pos/index.blade.php`

**Action:**

Add product detail modal with pricing tiers display. Insert after the main product grid section:

```blade
<!-- Product Detail Modal -->
<div x-show="selectedProduct"
     x-transition.opacity
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     style="display: none;">

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
         @click.away="selectedProduct = null">

        <!-- Modal Header -->
        <div class="sticky top-0 z-10 flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Product Details</h2>
            <button @click="selectedProduct = null"
                class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="p-6" x-data="productModal()">
            <template x-if="selectedProduct">
                <div>
                    <!-- Product Info -->
                    <div class="flex gap-6 mb-6">
                        <!-- Image -->
                        <div class="w-40 h-40 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 overflow-hidden flex-shrink-0">
                            <img :src="selectedProduct.image_url || 'https://placehold.co/200x200'"
                                class="w-full h-full object-cover">
                        </div>

                        <!-- Details -->
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2"
                                x-text="selectedProduct.name"></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3"
                                x-text="'SKU: ' + (selectedProduct.sku || 'N/A')"></p>

                            <!-- Stock Badge -->
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full"
                                :class="selectedProduct.stock > 10 ? 'bg-green-100 text-green-700' :
                                        selectedProduct.stock > 0 ? 'bg-yellow-100 text-yellow-700' :
                                        'bg-red-100 text-red-700'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span class="font-bold" x-text="'Stock: ' + selectedProduct.stock"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity Selector -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                            Quantity
                        </label>
                        <div class="flex items-center gap-4">
                            <button @click="decrementQty()"
                                class="w-12 h-12 rounded-xl bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 hover:border-brand-500 flex items-center justify-center text-xl font-bold transition-all">
                                −
                            </button>
                            <input type="number" x-model.number="quantity" @input="onQuantityChange()"
                                class="flex-1 text-center text-2xl font-bold py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <button @click="incrementQty()"
                                class="w-12 h-12 rounded-xl bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 hover:border-brand-500 flex items-center justify-center text-xl font-bold transition-all">
                                +
                            </button>
                        </div>
                    </div>

                    <!-- Auto-Calculated Price -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-brand-50 to-indigo-50 dark:from-gray-900 dark:to-gray-900 rounded-2xl border-2 border-brand-200 dark:border-brand-800">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Unit Price</span>
                            <span class="text-2xl font-bold text-brand-600 dark:text-brand-400"
                                x-text="formatCurrency(unitPrice)"></span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Quantity</span>
                            <span class="text-lg font-semibold text-gray-800 dark:text-white"
                                x-text="quantity + ' units'"></span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t-2 border-brand-200 dark:border-brand-800">
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Total</span>
                            <span class="text-3xl font-bold text-gray-800 dark:text-white"
                                x-text="formatCurrency(totalPrice)"></span>
                        </div>

                        <!-- Savings Badge -->
                        <div x-show="savings > 0"
                            class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                            <span class="font-bold" x-text="formatCurrency(savings) + ' saved (' + discountPercent + '% off)'"></span>
                        </div>
                    </div>

                    <!-- Pricing Tiers Display -->
                    <div x-show="pricingTiers.length > 0" class="mb-6">
                        <h4 class="font-bold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Pricing Tiers (Auto-Applied)
                        </h4>

                        <div class="space-y-2">
                            <template x-for="(tier, index) in pricingTiers" :key="index">
                                <div @click="selectTier(tier)"
                                    class="p-4 rounded-xl border-2 cursor-pointer transition-all"
                                    :class="activeTier === tier.name ?
                                        'border-brand-500 bg-brand-50 dark:bg-brand-900/20' :
                                        'border-gray-200 dark:border-gray-700 hover:border-brand-300'">

                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-3">
                                            <!-- Tier Icon -->
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                                :class="tier.name === 'Retail' ? 'bg-blue-100 text-blue-600' :
                                                        tier.name === 'Wholesale' ? 'bg-purple-100 text-purple-600' :
                                                        'bg-orange-100 text-orange-600'">
                                                <span class="text-lg"
                                                    x-text="tier.name === 'Retail' ? '🏷️' :
                                                            tier.name === 'Wholesale' ? '📦' :
                                                            '🏢'"></span>
                                            </div>

                                            <div>
                                                <p class="font-bold text-gray-800 dark:text-white"
                                                    x-text="tier.name"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400"
                                                    x-text="'Min ' + tier.min_qty + ' units'"></p>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-lg font-bold text-gray-800 dark:text-white"
                                                x-text="formatCurrency(tier.price)"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">per unit</p>
                                        </div>
                                    </div>

                                    <!-- Active Indicator -->
                                    <div x-show="activeTier === tier.name"
                                        class="mt-3 flex items-center gap-2 text-brand-600 dark:text-brand-400">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs font-bold">Active</span>
                                    </div>

                                    <!-- Discount Badge -->
                                    <div x-show="tier.price < basePrice"
                                        class="mt-2 inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-bold">
                                        <span x-text="calculateTierDiscount(tier) + '% OFF'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Unit Selector (if multiple units) -->
                    <div x-show="selectedProduct.units && selectedProduct.units.length > 1" class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Unit Type
                        </label>
                        <select x-model="selectedUnit" @change="onUnitChange()"
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <template x-for="unit in selectedProduct.units" :key="unit.id">
                                <option :value="unit.id" x-text="unit.name + ' (' + unit.conversion + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Add to Cart Button -->
                    <button @click="addToCartFromModal()"
                        class="w-full py-4 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-2xl font-bold text-lg hover:from-brand-700 hover:to-indigo-700 transition-all shadow-lg shadow-brand-500/30 flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span x-text="'Add ' + quantity + ' units to Cart - ' + formatCurrency(totalPrice)"></span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function productModal() {
    return {
        quantity: 1,
        selectedUnit: null,
        activeTier: '',
        pricingTiers: [],
        basePrice: 0,
        unitPrice: 0,

        get totalPrice() {
            return this.unitPrice * this.quantity;
        },

        get savings() {
            return (this.basePrice - this.unitPrice) * this.quantity;
        },

        get discountPercent() {
            if (this.basePrice === 0) return 0;
            return Math.round(((this.basePrice - this.unitPrice) / this.basePrice) * 100);
        },

        async onQuantityChange() {
            await this.fetchPricing();
        },

        async fetchPricing() {
            const token = localStorage.getItem('saga_token');
            const res = await fetch(`/api/products/${this.selectedProduct.id}/pricing-tiers?qty=${this.quantity}`, {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const data = await res.json();

            if (data.success) {
                this.pricingTiers = data.data.tiers || [];
                this.basePrice = data.data.base_price;
                this.unitPrice = data.data.calculated_price;
                this.activeTier = this.detectActiveTier(this.quantity);
            }
        },

        detectActiveTier(qty) {
            const tier = this.pricingTiers
                .sort((a, b) => b.min_qty - a.min_qty)
                .find(t => qty >= t.min_qty);
            return tier ? tier.name : 'Retail';
        },

        selectTier(tier) {
            this.quantity = tier.min_qty;
            this.onQuantityChange();
        },

        calculateTierDiscount(tier) {
            if (this.basePrice === 0 || !tier.price) return 0;
            return Math.round(((this.basePrice - tier.price) / this.basePrice) * 100);
        },

        incrementQty() {
            this.quantity++;
            this.onQuantityChange();
        },

        decrementQty() {
            if (this.quantity > 1) {
                this.quantity--;
                this.onQuantityChange();
            }
        },

        addToCartFromModal() {
            // Add to cart logic
            console.log('Add to cart:', {
                product: this.selectedProduct,
                quantity: this.quantity,
                price: this.unitPrice,
                total: this.totalPrice
            });

            // Close modal
            this.selectedProduct = null;
        }
    }
}
</script>
```

**Verify:**
- Modal opens on product click
- Quantity auto-calculates price
- Tiers display correctly
- Active tier highlights
- Add to cart works

**Done When:**
- Modal UI is clean and intuitive
- Auto-calculation works in real-time
- Tier selection updates quantity
- Cart receives correct data

---

## Success Criteria

- [ ] Database migration successful
- [ ] API endpoints return correct pricing
- [ ] Product edit modal configures tiers
- [ ] POS modal displays pricing tiers
- [ ] Auto-calculation works on quantity change
- [ ] Active tier highlights correctly
- [ ] Savings displayed prominently
- [ ] Add to cart with correct price
- [ ] Mobile-responsive design
- [ ] Page load <2 seconds

---

## 🧪 Verification Commands

```bash
# 1. Test API
curl http://localhost/api/products/1/pricing-tiers?qty=25 \
  -H "Authorization: Bearer {token}"

# 2. Test calculation
curl -X POST http://localhost/api/products/calculate-price \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":25}'

# 3. Clear caches
php artisan view:clear
php artisan cache:clear

# 4. Test in browser
# Open POS page
# Click product
# Adjust quantity
# Verify price auto-updates
```

---

**Ready for implementation!**
