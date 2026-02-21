# ROADMAP.md

> **Current Phase**: Phase 15: Inventory Audit & Stock Alerts (Complete)
> **Milestone**: v1.4 — Omnichannel Retail Backbone

## Must-Haves (from SPEC)
- [x] Working Local Environment (`.env`, `Composer`, `NPM`)
- [x] Initialized Database with Migrations and Seeders
- [x] Verified Modular Routing & Tenant Scoping (Phase 3 & 4)
- [x] Implementation of Team Karyawan Module (Phase 7)


## Phases

### Phase 1: Environment & Dependency Setup
**Status**: ✅ Complete
**Objective**: Install all necessary dependencies and configure the environment variables for local development.
**Tasks**:
- [x] Run `composer install`
- [x] Run `npm install`
- [x] Create `.env` from `.env.example`
- [x] Generate App Key

### Phase 2: Database & Infrastructure Initialization
**Status**: ✅ Complete
**Objective**: Prepare the database system, run migrations, and populate initial data (Permissions, Roles, Tenants).
**Tasks**:
- [x] Create database `tailadmin_laravel`
- [x] Run `php artisan migrate`
- [x] Run `php artisan db:seed` (if available) or populate Roles/Permissions
- [x] Verify Tenant table structure

### Phase 3: Architecture Deep Dive & Module Mapping
**Status**: ✅ Complete
**Objective**: Detailed mapping of module interactions and multi-tenant middleware to ensure full understanding of data flow.
**Tasks**:
- [x] Review Tenant middleware logic
- [x] Map Retail vs Barber module differences
- [x] Document specific Controller patterns


### Phase 4: System Verification & Proof of Life
**Status**: ✅ Complete
**Objective**: End-to-end verification of existing features.
**Tasks**:
- [x] Run Route Lists (Structural Verification)
- [x] Verify critical user flows (Code Inspection)
- [x] Finalize verification report

### Phase 5: Sidebar: Team Karyawan Menu
**Status**: ✅ Complete
**Objective**: Implement the "Team Karyawan" sidebar menu and its submenus for enhanced employee and sales management visibility.
**Depends on**: Phase 4

**Tasks**:
- [x] Add "Team Karyawan" configuration to `app/Modules/Retail/Config/menu.php`
- [x] Add "Team Karyawan" configuration to `app/Modules/Barber/Config/menu.php`
- [x] Verify menu visibility and layout
- [x] Ensure icons match the existing design system

**Verification**:
- Manual check of the sidebar in the tenant dashboard
- Verify all submenus are correctly listed

### Phase 6: Frontend Asset Management & Hygiene
**Status**: ✅ Complete
**Objective**: Ensure frontend assets are compiled and loaded correctly to prevent unstyled pages. Establish a protocol to run `npm run build` after UI changes.
**Depends on**: Phase 5

**Tasks**:
- [x] Run initial `npm run build` to fix the unstyled dashboard
- [x] Resolve `ERR_CONNECTION_REFUSED` by removing `public/hot`
- [x] Establish a "Build Protocol" (manual for now)
- [x] Verify asset loading in the browser

**Verification**:
- Dashboard is correctly styled in the browser

### Phase 7: Team Karyawan - Module Implementation
**Status**: ✅ Complete
... (rest of Phase 7)

### Phase 8: Employee Salary Logic & Attributes
**Status**: ✅ Complete
... (rest of Phase 8)

### Phase 9: Attendance & Payroll Integration
**Status**: ✅ Complete
... (rest of Phase 9)

### Phase 10: Payroll & Salary UI Implementation
**Status**: ✅ Complete
... (rest of Phase 10)

### Phase 11: Bulk Payroll Generation
**Status**: ✅ Complete
... (rest of Phase 11)

### Phase 12: Payroll Export & Reporting
**Status**: ✅ Complete
... (rest of Phase 12)

### Phase 13: POS Refinement & Transaction Persistence
**Status**: ✅ Complete
**Objective**: Connect the POS frontend to real APIs and implement complete transaction lifecycle including payment processing.
**Depends on**: Phase 12

**Tasks**:
- [x] Connect POS Category filter to real `CategoryController`
- [x] Implement checkout API integration in POS frontend
- [x] Add support for multiple product units in POS selector
- [x] Implement payment success/receipt modal


### Phase 14: Sales Analytics & Reporting Dashboards
**Status**: ✅ Complete
**Objective**: Develop analytical dashboards to visualize sales performance, top products, and profitability trends.
**Depends on**: Phase 13

**Tasks**:
- [x] Implement Daily/Monthly Sales trend charts (ApexCharts/Alpine)
- [x] Create "Top Selling Products" leaderboard
- [x] Add Category sales distribution (Pie chart/Table)
- [x] Implement Gross Profit calculation and visualization
- [x] Add export functionality for sales summaries
 
 
### Phase 15: Inventory Audit & Stock Alerts
**Status**: ✅ Complete
**Objective**: Enhance inventory control with low-stock notifications, comprehensive movement tracking, and manual adjustments.
**Depends on**: Phase 14

**Tasks**:
- [x] Implement Low-Stock notification badges in Header
- [x] Create `StockMovement` model and tracking logic
- [x] Implement "Stock Adjustment" feature with reason codes
- [x] Add "Low Stock" filter and highlighting in Items list
- [x] Dashboard widget for Top Deadstock
