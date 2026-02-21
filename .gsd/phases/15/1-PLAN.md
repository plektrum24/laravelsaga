# Phase 15: Inventory Audit & Stock Alerts

## Objective
Enhance inventory reliability and visibility by tracking every stock movement and alerting users of low-stock items.

## Proposed Changes

### [Backend] Tracking & Adjustments
#### [MODIFY] [TransactionController.php](file:///d:/Project%20App/laravelsaga/app/Http/Controllers/Api/TransactionController.php)
- Add `InventoryMovement` creation logic inside the `store` method loop.
- Type: `out`, Reference: `INV-XXXX`.

#### [MODIFY] [ProductController.php](file:///d:/Project%20App/laravelsaga/app/Http/Controllers/Api/ProductController.php)
- Add `adjustStock(Request $request, Product $product)`:
    - Update `stock` field.
    - Create `InventoryMovement` record (Type: `adjustment`).

#### [MODIFY] [ReportController.php](file:///d:/Project%20App/laravelsaga/app/Http/Controllers/Api/ReportController.php)
- Add `inventoryMovements(Request $request)`: Returns paginated movement logs with product details.

### [Frontend] Dashboard & UI
#### [MODIFY] [sidebar.blade.php](file:///d:/Project%20App/laravelsaga/resources/views/partials/sidebar.blade.php) or [header.blade.php](file:///d:/Project%20App/laravelsaga/resources/views/partials/header.blade.php)
- Add a "Low Stock" notification badge if `Product::whereColumn('stock', '<=', 'min_stock')->count() > 0`.

#### [NEW] [movements.blade.php](file:///d:/Project%20App/laravelsaga/resources/views/pages/inventory/movements.blade.php)
- Table view of `InventoryMovement` records.
- Filters: Date, Product, Type (In/Out/Adjustment).

#### [MODIFY] [inventory/index.blade.php](file:///d:/Project%20App/laravelsaga/resources/views/pages/inventory/index.blade.php)
- Add "Adjust Stock" button/modal.
- Highlight rows where stock is low.

## Verification Plan

### Automated Tests
- Create transaction and verify `inventory_movements` table has a matching entry.
- Call `adjustStock` API and verify both `products.stock` and `inventory_movements` are updated.

### Manual Verification
- Check if low stock items are highlighted in the UI.
- Verify the stock movement log matches recent activity.
