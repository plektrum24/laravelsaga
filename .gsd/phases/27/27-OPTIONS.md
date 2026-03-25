# Phase 27: POS & Inventory Enhancement

**Date:** 2026-02-23
**Status:** PLANNING
**Milestone:** v3.2.0 — UX & Automation Improvements
**Priority:** HIGH

---

## 📋 Context

**Current State:**
- Phase 1-26 COMPLETE ✅
- Production-ready SaaS POS platform
- 150+ features implemented
- Mobile app ready (iOS & Android)

**Issues Identified:**
1. ⚠️ Goods In page returns 404
2. ⚠️ Returns page returns 404
3. ⚠️ Deadstock page needs UI/UX improvement
4. ⚠️ POS product modal lacks automated pricing tiers

---

## 🎯 Phase 27 Objectives

### **Task 1: Fix 404 Errors on Goods In & Returns Pages** 🔴
**Priority:** CRITICAL | **Effort:** Low (2-3 hours)

**Problem:**
- `/inventory/receiving/goods-in` → 404 Not Found
- `/returns` → 404 Not Found

**Root Cause:**
- Route mismatch between `web.php` and view paths
- Missing route definitions or incorrect route names

**Solution:**
1. Verify route definitions in `routes/web.php`
2. Check view file paths match route closures
3. Test navigation from menu to page
4. Add fallback redirects if needed

**Files to Check:**
- `routes/web.php` (lines 120-145, 189-201)
- `resources/views/pages/inventory/receiving/goods-in.blade.php`
- `resources/views/pages/inventory/returns/index.blade.php`
- `app/Modules/Retail/Config/menu.php`

**Acceptance Criteria:**
- [ ] Goods In page loads without 404
- [ ] Returns page loads without 404
- [ ] Menu navigation works correctly
- [ ] All child routes (create, supplier, customer) functional

---

### **Task 2: Improve Deadstock Page UI/UX** 🟡
**Priority:** MEDIUM | **Effort:** Medium (4-6 hours)

**Current State:**
- Basic card grid layout
- Red-themed warning design
- Limited filtering options
- No analytics or insights

**Enhancement Requirements:**

#### **2.1 Visual Design**
- Modern gradient cards with better visual hierarchy
- Improved color scheme (less alarming, more actionable)
- Better typography and spacing
- Smooth animations and transitions

#### **2.2 Enhanced Features**
- **Filtering:**
  - Filter by category
  - Filter by days without movement (30/60/90 days)
  - Filter by supplier
  - Search by name/SKU

- **Sorting:**
  - Last movement date
  - Stock value lost
  - Product name (A-Z)
  - Days without movement

- **Analytics:**
  - Total deadstock count
  - Total value locked (Rp)
  - Average days without movement
  - Category breakdown chart

- **Actions:**
  - Bulk restock button
  - Export to CSV/Excel
  - Create clearance promotion
  - Set auto-reorder alerts

#### **2.3 UI Layout Options**

**Option A: Dashboard Style** ⭐ RECOMMENDED
```
┌─────────────────────────────────────────────────────┐
│  Deadstock Analytics                                │
│  ┌─────────┬─────────┬─────────┬─────────┐         │
│  │ Total   │ Value   │ Avg Days│ Top     │         │
│  │ Items   │ Locked  │ Stuck   │ Category│         │
│  │   45    │ Rp 12.5M│  67     │ Drinks  │         │
│  └─────────┴─────────┴─────────┴─────────┘         │
└─────────────────────────────────────────────────────┘
┌──────────┐ ┌──────────────────────────────────────┐
│ Filters  │ │  Product Grid (Enhanced Cards)       │
│          │ │  ┌────┐ ┌────┐ ┌────┐ ┌────┐        │
│ Category │ │  │ P1 │ │ P2 │ │ P3 │ │ P4 │        │
│ Days     │ │  └────┘ └────┘ └────┘ └────┘        │
│ Supplier │ │  ┌────┐ ┌────┐ ┌────┐ ┌────┘        │
│          │ │  │ P5 │ │ P6 │ │ P7 │ │ P8 │        │
│ [Search] │ │  └────┘ └────┘ └────┘ └────┘        │
│          │ │  ...more products...                 │
└──────────┘ └──────────────────────────────────────┘
```

**Option B: Table View**
- Sortable columns
- Inline actions
- Bulk selection
- Compact view for many items

**Option C: Kanban Board**
- Columns by days stuck (30/60/90+)
- Drag-to-action workflow
- Visual status progression

---

### **Task 3: POS Product Modal with Pricing Tiers** 🟢
**Priority:** HIGH | **Effort:** High (8-12 hours)

**Requirement:**
Add automated pricing calculation and display for:
- **Retail Price** (standard)
- **Wholesale/Grosir Price** (bulk discount)
- **B2B Price** (contract pricing)
- **Auto-calculated price** based on quantity input

#### **3.1 Backend Requirements**

**Database Schema:**
```sql
-- Add to products table or separate pricing table
ALTER TABLE products ADD COLUMN pricing_tier_config JSON;

-- Example structure:
{
  "enable_tiers": true,
  "tiers": [
    {
      "name": "Retail",
      "min_qty": 1,
      "price_type": "fixed",
      "price": 10000,
      "discount_percent": 0
    },
    {
      "name": "Wholesale",
      "min_qty": 10,
      "price_type": "discount",
      "discount_percent": 10,
      "price": 9000
    },
    {
      "name": "B2B",
      "min_qty": 50,
      "price_type": "discount",
      "discount_percent": 20,
      "price": 8000
    }
  ]
}
```

**Migration:**
```php
Schema::table('products', function (Blueprint $table) {
    $table->json('pricing_tier_config')->nullable()->after('price');
    $table->boolean('enable_tiered_pricing')->default(false);
});
```

**API Endpoint:**
```php
// GET /api/products/{id}/pricing-tiers
public function getPricingTiers($productId)
{
    $product = Product::findOrFail($productId);
    return response()->json([
        'success' => true,
        'data' => [
            'base_price' => $product->price,
            'tiers' => $product->pricing_tier_config,
            'auto_calculated' => $this->calculatePriceByQty($product, $qty)
        ]
    ]);
}
```

#### **3.2 UI/UX Design**

**Modal Structure:**
```
┌─────────────────────────────────────────────────────┐
│  Add Product to Cart                        [✕]    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌─────────────────┐  ┌────────────────────────┐   │
│  │   Product Img   │  │  Product Name          │   │
│  │                 │  │  SKU: ABC-123          │   │
│  │                 │  │  Stock: 150 units      │   │
│  └─────────────────┘  │                        │   │
│                       │  ┌──────────────────┐  │   │
│                       │  │  Quantity        │  │   │
│                       │  │  [  -  ] 25 [ + ]│  │   │
│                       │  └──────────────────┘  │   │
│                       │                        │   │
│                       │  💰 Auto-Price:        │   │
│                       │  Rp 225,000            │   │
│                       │  (Rp 9,000/unit)       │   │
│                       └────────────────────────┘   │
│                                                     │
│  ─────────────────────────────────────────────────  │
│                                                     │
│  📊 Pricing Tiers (Auto-Applied by Quantity)       │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │ 🏷️ Retail (1-9 units)                      │   │
│  │    Rp 10,000/unit  │  Total: Rp 250,000    │   │
│  │    [Select]                                │   │
│  ├─────────────────────────────────────────────┤   │
│  │ 📦 Wholesale (10-49 units) ✅ ACTIVE        │   │
│  │    Rp 9,000/unit   │  Total: Rp 225,000    │   │
│  │    10% OFF  │  [Select]                     │   │
│  ├─────────────────────────────────────────────┤   │
│  │ 🏢 B2B (50+ units)                          │   │
│  │    Rp 8,000/unit   │  Total: Rp 200,000    │   │
│  │    20% OFF  │  [Select]                     │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ─────────────────────────────────────────────────  │
│                                                     │
│  Unit Selector: [Pack ▼]  (1 Pack = 12 Pcs)        │
│                                                     │
│  [        Add to Cart (25 units @ Rp 9,000)       ]│
│                                                     │
└─────────────────────────────────────────────────────┘
```

#### **3.3 UX Flow**

1. **User clicks product** → Modal opens
2. **Default quantity = 1** → Shows Retail price
3. **User adjusts quantity:**
   - Price auto-updates based on tier
   - Active tier highlighted
   - Savings displayed
4. **User can manually select tier:**
   - Click tier card → Quantity auto-adjusts to tier minimum
   - Price updates accordingly
5. **Unit conversion:**
   - If product has multiple units (Pack, Pcs, Box)
   - Price recalculates based on unit selected
6. **Add to cart:**
   - Single button with dynamic label
   - Shows final quantity and price

#### **3.4 Clean UI Principles**

**Do:**
- ✅ Progressive disclosure (show details when needed)
- ✅ Visual hierarchy (price is prominent)
- ✅ Clear active state (which tier is applied)
- ✅ Smooth animations (quantity changes)
- ✅ Minimal clicks (1-2 clicks to add)

**Don't:**
- ❌ Overwhelm with too many options
- ❌ Hide the "Add to Cart" button
- ❌ Require manual tier selection (auto-detect)
- ❌ Show complex pricing formulas

#### **3.5 Technical Implementation**

**Frontend (Alpine.js):**
```javascript
function posSystem() {
  return {
    selectedProduct: null,
    selectedQty: 1,
    selectedTier: 'retail',
    selectedUnit: 'pcs',

    get calculatedPrice() {
      // Auto-calculate based on qty
      const tier = this.detectTier(this.selectedQty);
      return tier.price * this.selectedQty;
    },

    detectTier(qty) {
      // Find applicable tier based on quantity
      return this.product.tiers
        .reverse()
        .find(t => qty >= t.min_qty) || this.product.tiers[0];
    },

    selectTier(tier) {
      this.selectedQty = tier.min_qty;
      this.selectedTier = tier.name;
    }
  }
}
```

---

## 📊 Priority Matrix

| Task | Impact | Effort | Priority |
|------|--------|--------|----------|
| **Fix 404 Errors** | 🔴 Critical | 🟢 Low | **P0** |
| **POS Pricing Tiers** | 🟡 High | 🔴 High | **P1** |
| **Deadstock UI/UX** | 🟢 Medium | 🟡 Medium | **P2** |

---

## 🗓️ Execution Plan

### **Wave 1: Critical Fixes** (Day 1)
- Fix Goods In 404
- Fix Returns 404
- Verify all routes

### **Wave 2: Deadstock Enhancement** (Day 2-3)
- Design new UI components
- Implement analytics dashboard
- Add filtering and sorting
- Export functionality

### **Wave 3: POS Pricing Tiers** (Day 4-7)
- Database migration
- Backend API endpoints
- Modal UI implementation
- Auto-calculation logic
- Testing and refinement

---

## ✅ Success Criteria

### **Wave 1:**
- [ ] No 404 errors on Goods In or Returns
- [ ] All menu links functional
- [ ] Navigation tested end-to-end

### **Wave 2:**
- [ ] Deadstock page loads in <2s
- [ ] Analytics display correctly
- [ ] Filters work as expected
- [ ] Export generates valid CSV/Excel
- [ ] Visual design matches design system

### **Wave 3:**
- [ ] Pricing tiers configurable per product
- [ ] Auto-calculation works in real-time
- [ ] Modal UI is clean and intuitive
- [ ] Cart receives correct price
- [ ] Unit conversion works correctly
- [ ] Mobile-responsive design

---

## 📝 Next Steps

**Pending your decision:**

1. **Approve Phase 27** - Start implementation
2. **Prioritize waves** - Which to tackle first
3. **Review UI/UX mockups** - Provide feedback

---

```
▶ NEXT

/approve phase-27 — Start implementation
/plan phase-27 — Create detailed execution plan
/discuss phase-27 — Discuss requirements
```
