# Phase 21: Sales Force Enhancement

**Date:** 2026-02-21
**Status:** PLANNING
**Milestone:** v2.0 — Sales Force Optimization
**Priority:** HIGH

---

## 📋 Context

**Current State:**
- Phase 1-20 COMPLETE ✅
- Sales Force menu exists with basic structure
- Sales Order History currently under "Team Karyawan" (needs relocation)
- Sales Analytics & Reports available but lacks Sales Force-specific reports
- Some routes may lead to 404 pages

**Issues Identified:**
1. Sales Order History is under wrong menu (Team Karyawan instead of Sales Force)
2. No dedicated Sales Force reports in Analytics section
3. Potential 404 errors on some inventory pages

---

## 🎯 Objectives

1. **Relocate Sales Order History** - Move from Team Karyawan to Sales Force menu
2. **Add Sales Force Reports** - New report card in Sales Analytics & Reports
3. **Fix 404 Pages** - Identify and create missing pages

---

## 📦 Scope

### Task 1: Update Sales Force Menu Structure

**Current Menu:**
```
Sales Force
├── Salesmen Data
├── Sales Orders
└── Visit Plans
```

**New Menu Structure:**
```
Sales Force
├── Salesmen Data
├── Sales Orders
├── Visit Plans
└── Sales Order History (MOVED from Team Karyawan)
```

**Implementation:**
- Update `app/Modules/Retail/Config/menu.php`
- Add "Sales Order History" submenu item
- Remove duplicate from Team Karyawan menu

---

### Task 2: Add Sales Force Report Card

**Location:** Sales Analytics & Reports page (`/reports`)

**New Report Card:**
```
┌─────────────────────────────────────┐
│  📊 Sales Force Performance         │
├─────────────────────────────────────┤
│  - Total Sales by Salesman          │
│  - Conversion Rate                  │
│  - Visit Effectiveness              │
│  - Orders per Salesman              │
│  - Average Order Value              │
│  - Top Performing Salesman          │
└─────────────────────────────────────┘
```

**Implementation:**
- Add new tab/section in `resources/views/pages/reports/index.blade.php`
- Create API endpoint for Sales Force analytics
- Add filters (date range, salesman, branch)

---

### Task 3: Fix 404 Pages

**Pages to Check:**

| Route | Current Status | Action |
|-------|---------------|--------|
| `/inventory/stock-management` | ⚠️ Check | Create page if 404 |
| `/inventory/receiving/supplier-returns` | ⚠️ Check | Create page if 404 |
| `/inventory/receiving/customer-returns` | ⚠️ Check | Create page if 404 |
| `/sales` | ✅ Exists | Verify functionality |
| `/sales/create` | ✅ Exists | Verify functionality |

**Implementation:**
- Test each route
- Create missing blade views
- Add proper routing in `web.php`

---

## 🗄️ Database Schema (if needed)

### sales_force_analytics (View/Table)
```sql
CREATE VIEW sales_force_analytics AS
SELECT 
    u.id as salesman_id,
    u.name as salesman_name,
    COUNT(o.id) as total_orders,
    SUM(o.grand_total) as total_sales,
    AVG(o.grand_total) as avg_order_value,
    COUNT(DISTINCT o.customer_id) as unique_customers,
    MAX(o.created_at) as last_sale_date
FROM users u
LEFT JOIN orders o ON u.id = o.salesman_id
WHERE u.role = 'salesman'
GROUP BY u.id, u.name;
```

---

## 🔌 API Endpoints

### New Endpoints Required:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/reports/sales-force` | Sales Force performance data |
| GET | `/api/reports/sales-force/{salesmanId}` | Individual salesman report |
| GET | `/api/reports/sales-force/summary` | Summary metrics |
| POST | `/api/reports/sales-force/export` | Export to Excel/PDF |

### Response Format:
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_salesmen": 10,
      "total_orders": 1250,
      "total_revenue": 125000000,
      "avg_order_value": 100000,
      "conversion_rate": 0.75
    },
    "salesmen": [
      {
        "id": 1,
        "name": "John Doe",
        "total_orders": 150,
        "total_sales": 15000000,
        "avg_order_value": 100000,
        "unique_customers": 85,
        "conversion_rate": 0.80,
        "last_sale_date": "2026-02-20"
      }
    ],
    "top_performers": [...],
    "trends": [...]
  }
}
```

---

## 📁 Files to Create/Modify

### Create:
1. `app/Http/Controllers/Api/SalesForceReportController.php`
2. `resources/views/pages/reports/sales-force.blade.php` (if separate page)
3. `database/migrations/tenant/2026_02_21_create_sales_force_analytics_view.php`

### Modify:
1. `app/Modules/Retail/Config/menu.php` - Update Sales Force menu
2. `app/Modules/Barber/Config/menu.php` - Update Sales Force menu (if applicable)
3. `resources/views/pages/reports/index.blade.php` - Add Sales Force report card
4. `routes/api.php` - Add Sales Force report endpoints

---

## ✅ Acceptance Criteria

### Menu Update:
- [ ] "Sales Order History" appears under Sales Force menu
- [ ] "Sales Order History" removed from Team Karyawan menu
- [ ] Menu visible for correct roles (Owner, Manager)
- [ ] No broken links

### Sales Force Reports:
- [ ] New report card visible in Sales Analytics & Reports
- [ ] Data loads correctly from API
- [ ] Filters work (date range, salesman, branch)
- [ ] Export functionality works
- [ ] Charts/graphs render correctly

### 404 Fixes:
- [ ] All inventory routes accessible
- [ ] All sales routes accessible
- [ ] No 404 errors in menu navigation
- [ ] Proper fallback for missing pages

---

## 📊 Wave Breakdown

### Wave 1: Menu Restructuring
**Objective:** Fix menu organization

**Tasks:**
- [ ] Move Sales Order History to Sales Force menu
- [ ] Remove duplicate from Team Karyawan
- [ ] Test menu visibility

**Timeline:** 1 day

---

### Wave 2: Sales Force Reports
**Objective:** Add Sales Force analytics

**Tasks:**
- [ ] Create SalesForceReportController
- [ ] Create API endpoints
- [ ] Add report card to Reports page
- [ ] Implement charts/visualizations
- [ ] Add export functionality

**Timeline:** 2-3 days

---

### Wave 3: 404 Page Fixes
**Objective:** Fix missing pages

**Tasks:**
- [ ] Identify all 404 routes
- [ ] Create missing blade views
- [ ] Add proper routing
- [ ] Test all navigation

**Timeline:** 1-2 days

---

## 🧪 Testing Checklist

### Menu Testing:
- [ ] Sales Force menu displays correctly
- [ ] All submenu items clickable
- [ ] Correct role-based visibility
- [ ] No duplicate menu items

### Report Testing:
- [ ] Sales Force report loads
- [ ] Data accuracy verified
- [ ] Filters apply correctly
- [ ] Export generates correct file
- [ ] Charts display correctly

### Route Testing:
- [ ] All menu routes resolve
- [ ] No 404 errors
- [ ] Proper error handling
- [ ] Fallback pages work

---

## 📈 Success Metrics

| Metric | Target |
|--------|--------|
| Menu items correctly organized | 100% |
| Sales Force reports load time | < 2 seconds |
| 404 errors eliminated | 100% |
| Report data accuracy | 100% |
| User satisfaction | > 4.5/5 |

---

## ⚠️ Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Duplicate menu items | Low | Careful menu.php editing |
| Data accuracy in reports | High | Thorough testing with real data |
| Performance (large datasets) | Medium | Pagination, caching |
| Breaking existing features | High | Test all affected routes |

---

## 🚀 Next Steps

**Upon Approval:**
1. Update menu configuration files
2. Create SalesForceReportController
3. Implement API endpoints
4. Add report card to Reports page
5. Test all routes for 404 errors
6. Create missing pages
7. Deploy and verify

---

**Phase 21 Specification Document**
**Status:** Ready for implementation
**Priority:** HIGH
**Estimated Timeline:** 4-6 days
