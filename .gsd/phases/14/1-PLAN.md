# Phase 14: Sales Analytics & Reporting Dashboards

## Objective
Provide business owners and managers with visual insights into sales performance, inventory turnover, and profitability.

## Proposed Changes

### Backend: API Enhancements
#### [MODIFY] [ReportController.php](file:///d:/Project%20App/laravelsaga/app/Http/Controllers/Api/ReportController.php)
Implement the following methods:
- `salesOverview(Request $request)`: Returns daily/monthly sales data for Chart.js.
- `topProducts(Request $request)`: Returns top 5-10 selling products by quantity and revenue.
- `categoryPerformance(Request $request)`: Returns sales distribution by category.

#### [MODIFY] [api.php](file:///d:/Project%20App/laravelsaga/routes/api.php)
Register new reporting routes:
- `GET /api/reports/sales-overview`
- `GET /api/reports/top-products`
- `GET /api/reports/category-performance`

### Frontend: Dashboard Implementation
#### [NEW] [sales.blade.php](file:///d:/Project%20App/laravelsaga/resources/views/pages/reports/sales.blade.php)
- Layout: 4 Stat Cards (Revenue, Profit, Transactions, Avg Ticket).
- Charts: 
    - Line Chart (Sales Trend).
    - Bar Chart (Top Products).
    - Pie Chart (Category Mix).
- Filtering: Date range picker (Last 7 Days, This Month, Custom).

### Sidebar Integration
#### [MODIFY] [menu.php (Retail & Barber)](file:///d:/Project%20App/laravelsaga/app/Modules/Retail/Config/menu.php)
- Add "Laporan Penjualan" under the Reporting or Sales section.

## Verification Plan

### Automated Tests
- Mock transactions for various dates and verify `salesOverview` aggregation.
- Verify `topProducts` correctly identifies high-volume items.

### Manual Verification
- View dashboard with seeded data.
- Change date filters and ensure charts update correctly.
- Verify profit calculations match (Total Revenue - Total COGS).
