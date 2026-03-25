# Phase 28: Forecast Generation with Dynamic Cost Calculation

**Date:** 2026-02-23
**Status:** PLANNING
**Milestone:** v3.3.0 — Advanced Forecasting
**Priority:** HIGH

---

## 📋 Context

**Current State:**
- Phase 1-27 COMPLETE ✅
- Existing forecasting service with sales prediction
- Basic forecasting UI with recommendations
- No dynamic cost calculation based on target order value

**Business Need:**
Users want to:
1. Generate forecasts based on **target revenue** they want to achieve
2. See **dynamic cost calculation** that updates as they adjust targets
3. Get **actionable order recommendations** tied to business goals

---

## 🎯 Phase 28 Objectives

### **Feature 1: Generate Forecast by Target Revenue** 🔴
**Priority:** CRITICAL | **Effort:** Medium (3-4 hours)

**Requirement:**
Allow users to input a target revenue amount and generate a forecast that shows:
- Required sales to hit target
- Product mix recommendations
- Timeline to achieve target

**Example:**
```
Target Revenue: Rp 100,000,000
Current Trajectory: Rp 75,000,000
Gap: Rp 25,000,000
Recommended Actions: [List of products to stock/promote]
```

---

### **Feature 2: Target Order Summary Card** 🟡
**Priority:** HIGH | **Effort:** Medium (3-4 hours)

**Requirement:**
Display a summary card showing:
- Total target order value
- Breakdown by category
- Recommended quantities per product
- Expected profit margin

**UI Layout:**
```
┌─────────────────────────────────────────┐
│  📊 Target Order Summary                │
├─────────────────────────────────────────┤
│  Total Target Value    Rp 100,000,000  │
│  Estimated Cost        Rp 70,000,000   │
│  Expected Profit       Rp 30,000,000   │
│  Profit Margin              30%        │
├─────────────────────────────────────────┤
│  By Category:                           │
│  • Beverages    Rp 40,000,000 (40%)    │
│  • Snacks       Rp 35,000,000 (35%)    │
│  • Other        Rp 25,000,000 (25%)    │
└─────────────────────────────────────────┘
```

---

### **Feature 3: Dynamic Cost Calculation** 🟢
**Priority:** HIGH | **Effort:** High (5-6 hours)

**Requirement:**
As user adjusts the target order value in a form, automatically recalculate:
- Total cost (COGS)
- Expected revenue
- Profit margin
- Recommended product quantities
- Break-even point

**User Flow:**
1. User enters target revenue (e.g., Rp 100,000,000)
2. System calculates required product mix
3. User adjusts target up/down
4. All values update in real-time
5. User can see impact on costs and profits

**UI Component:**
```
┌─────────────────────────────────────────┐
│  🎯 Generate Forecast                   │
├─────────────────────────────────────────┤
│  Target Revenue:                        │
│  [ Rp 100,000,000 ▼] (Slider/Input)    │
│                                         │
│  Duration: [30 days ▼]                  │
│                                         │
│  [Generate Forecast]                    │
└─────────────────────────────────────────┘

→ Updates in real-time as user types/slides
```

---

## 📊 Technical Design

### **Database Schema**

**forecast_targets** (New Table)
```sql
- id: bigint
- tenant_id: bigint (FK)
- target_revenue: decimal(15,2)
- target_duration_days: integer
- current_trajectory: decimal(15,2)
- gap: decimal(15,2)
- status: enum (draft, active, achieved, expired)
- generated_at: datetime
- achieved_at: datetime (nullable)
- created_at, updated_at: timestamp
```

**forecast_target_items** (New Table)
```sql
- id: bigint
- forecast_target_id: bigint (FK)
- product_id: bigint (FK)
- recommended_qty: integer
- unit_cost: decimal(15,2)
- total_cost: decimal(15,2)
- expected_revenue: decimal(15,2)
- expected_profit: decimal(15,2)
- priority: integer (1-5)
- created_at, updated_at: timestamp
```

---

### **API Endpoints**

**POST /api/forecast/calculate-target**
```json
Request:
{
  "target_revenue": 100000000,
  "duration_days": 30
}

Response:
{
  "success": true,
  "data": {
    "target_revenue": 100000000,
    "current_trajectory": 75000000,
    "gap": 25000000,
    "required_daily_sales": 3333333,
    "recommended_products": [...],
    "total_cost": 70000000,
    "expected_profit": 30000000,
    "profit_margin": 30.0,
    "break_even_date": "2026-03-15"
  }
}
```

**POST /api/forecast/save-target**
```json
Request:
{
  "target_revenue": 100000000,
  "duration_days": 30,
  "product_mix": [...]
}

Response:
{
  "success": true,
  "data": {
    "forecast_target_id": 123,
    "message": "Target saved successfully"
  }
}
```

**GET /api/forecast/target/{id}**
```json
Response:
{
  "success": true,
  "data": {
    "target_revenue": 100000000,
    "current_progress": 45000000,
    "remaining": 55000000,
    "days_remaining": 15,
    "on_track": true,
    "products": [...]
  }
}
```

---

### **Service Layer**

**ForecastTargetService.php** (NEW)
```php
class ForecastTargetService
{
    /**
     * Calculate forecast based on target revenue
     */
    public function calculateFromTarget($tenantId, $targetRevenue, $days)
    {
        // Get historical data
        $historicalData = $this->getHistoricalData($tenantId);
        
        // Calculate current trajectory
        $currentTrajectory = $this->projectCurrent($historicalData, $days);
        
        // Calculate gap
        $gap = $targetRevenue - $currentTrajectory;
        
        // Get product mix recommendations
        $productMix = $this->recommendProductMix($tenantId, $gap);
        
        // Calculate costs
        $totalCost = $this->calculateTotalCost($productMix);
        $expectedProfit = $targetRevenue - $totalCost;
        $profitMargin = ($expectedProfit / $targetRevenue) * 100;
        
        return [
            'target_revenue' => $targetRevenue,
            'current_trajectory' => $currentTrajectory,
            'gap' => $gap,
            'required_daily_sales' => $targetRevenue / $days,
            'product_mix' => $productMix,
            'total_cost' => $totalCost,
            'expected_profit' => $expectedProfit,
            'profit_margin' => $profitMargin,
        ];
    }
    
    /**
     * Save forecast target
     */
    public function saveTarget($tenantId, $data)
    {
        // Create forecast target record
        // Create target items
        // Return saved target
    }
}
```

---

## 🗓️ Execution Plan

### **Wave 1: Backend Foundation** (Day 1-2)
**Priority:** P0 | **Effort:** 4-5 hours

**Tasks:**
1. Create database migrations
2. Create ForecastTarget and ForecastTargetItem models
3. Create ForecastTargetService
4. Create API endpoints
5. Test calculation logic

**Files:**
- `database/migrations/tenant/2026_02_23_000002_create_forecast_targets_tables.php`
- `app/Models/ForecastTarget.php`
- `app/Models/ForecastTargetItem.php`
- `app/Services/ForecastTargetService.php`
- `app/Http/Controllers/Api/ForecastTargetController.php`

---

### **Wave 2: Target Input Form** (Day 2-3)
**Priority:** P1 | **Effort:** 3-4 hours

**Tasks:**
1. Create target input modal/component
2. Add slider for revenue input
3. Add duration selector
4. Implement real-time calculation
5. Add generate button

**Files:**
- `resources/views/components/forecast-target-input.blade.php`
- `resources/views/pages/inventory/forecasting.blade.php` (updated)

---

### **Wave 3: Summary Card & Dynamic Updates** (Day 3-4)
**Priority:** P1 | **Effort:** 4-5 hours

**Tasks:**
1. Create summary card component
2. Display breakdown by category
3. Implement real-time updates
4. Add profit margin visualization
5. Add export/save functionality

**Files:**
- `resources/views/components/forecast-summary-card.blade.php`
- Updated forecasting page

---

## 📊 UI/UX Design

### **Main Forecasting Page Layout**

```
┌─────────────────────────────────────────────────────────────┐
│  Product Forecasting                              [Generate]│
│  AI-powered demand prediction & reorder recommendations    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  🎯 Target Forecast Input                                   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Target Revenue:                                            │
│  ┌─────────────────────────────────────────────────┐       │
│  │ Rp 100,000,000                    [▼ Slider ▼] │       │
│  └─────────────────────────────────────────────────┘       │
│                                                             │
│  Duration: [30 days ▼]    Start Date: [2026-02-23]         │
│                                                             │
│  [Generate Forecast]  [Save Target]  [Export]              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  📊 Forecast Summary                                        │
├──────────────────┬──────────────────┬──────────────────────┤
│  Target Revenue  │  Estimated Cost  │  Expected Profit     │
│  Rp 100,000,000  │  Rp 70,000,000   │  Rp 30,000,000       │
│                  │                  │  Profit Margin: 30%  │
└──────────────────┴──────────────────┴──────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  📦 Recommended Product Mix                                 │
├─────────────────────────────────────────────────────────────┤
│  Category: [All ▼]  Sort: [Priority ▼]                     │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐      │
│  │ Product 1│ │ Product 2│ │ Product 3│ │ Product 4│      │
│  │ Qty: 100 │ │ Qty: 250 │ │ Qty: 150 │ │ Qty: 80  │      │
│  │ Cost: 5M │ │ Cost: 3M │ │ Cost: 4M │ │ Cost: 2M │      │
│  │ Rev: 7M  │ │ Rev: 4.5M│ │ Rev: 6M  │ │ Rev: 3M  │      │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘      │
└─────────────────────────────────────────────────────────────┘
```

---

## ✅ Success Criteria

### **Wave 1:**
- [ ] Migrations created and run
- [ ] Models with relationships
- [ ] Service calculates correctly
- [ ] API endpoints functional
- [ ] Tests pass

### **Wave 2:**
- [ ] Input form renders correctly
- [ ] Slider works smoothly
- [ ] Duration selector functional
- [ ] Real-time calculation works
- [ ] Generate button triggers API

### **Wave 3:**
- [ ] Summary card displays all metrics
- [ ] Category breakdown accurate
- [ ] Real-time updates on input change
- [ ] Profit margin visualized
- [ ] Save/export functional

---

## 📝 Next Steps

**Pending your decision:**

1. **Approve Phase 28** - Start implementation
2. **Prioritize waves** - Which to tackle first
3. **Review UI/UX mockups** - Provide feedback

---

```
▶ NEXT

/approve phase-28 — Start implementation
/plan phase-28 — Create detailed execution plans
/discuss phase-28 — Discuss requirements
```
