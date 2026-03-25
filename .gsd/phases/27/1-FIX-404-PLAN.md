---
phase: 27
plan: 1
wave: 1
---

# Plan 27.1: Fix 404 Errors on Goods In & Returns Pages

## Objective
Fix critical 404 errors on Goods In and Returns pages to restore core inventory functionality.

**Priority:** 🔴 CRITICAL  
**Effort:** Low (2-3 hours)  
**Dependencies:** None

---

## Context
- `.gsd/phases/27/27-OPTIONS.md`
- `routes/web.php`
- `app/Modules/Retail/Config/menu.php`
- `resources/views/pages/inventory/receiving/`
- `resources/views/pages/inventory/returns/`

---

## Tasks

### Task 1: Diagnose 404 Errors
**Type:** `auto` | **Effort:** `low`

**Files:**
- `routes/web.php`
- Browser console / Network tab

**Action:**
1. Check current route definitions:
   ```bash
   php artisan route:list | findstr "goods\|receiving\|returns"
   ```
2. Test URLs in browser:
   - `http://localhost/inventory/receiving`
   - `http://localhost/inventory/receiving/goods-in`
   - `http://localhost/returns`
   - `http://localhost/returns/supplier`
   - `http://localhost/returns/customer`
3. Note which routes return 404
4. Check route name mismatches

**Verify:**
```bash
php artisan route:list --name=receiving
php artisan route:list --name=returns
```

**Done When:**
- All 404 routes identified
- Root cause documented (missing route, wrong path, or name mismatch)

---

### Task 2: Fix Goods In Routes
**Type:** `auto` | **Effort:** `low`

**Files:**
- `routes/web.php` (lines 120-135)
- `resources/views/pages/inventory/receiving/goods-in.blade.php`

**Action:**

1. **Add/fix route in `web.php`:**
   ```php
   // Goods In - Main Page
   Route::get('/inventory/receiving', function () {
       return view('pages.inventory.receiving.goods-in');
   })->name('inventory.receiving.index');

   // Goods In - Create (if separate page needed)
   Route::get('/inventory/receiving/create', function () {
       return view('pages.inventory.receiving.create');
   })->name('inventory.receiving.create');
   ```

2. **Verify view file exists:**
   - Check: `resources/views/pages/inventory/receiving/goods-in.blade.php`
   - If missing, rename from existing file or create

3. **Fix menu route reference:**
   - Check: `app/Modules/Retail/Config/menu.php`
   - Ensure route name matches: `'route' => 'inventory.receiving.index'`

**Verify:**
```bash
# Test route
curl -I http://localhost/inventory/receiving

# Should return HTTP/1.1 200 OK
```

**Done When:**
- `/inventory/receiving` loads without 404
- Menu link works correctly
- View renders without errors

---

### Task 3: Fix Returns Routes
**Type:** `auto` | **Effort:** `low`

**Files:**
- `routes/web.php` (lines 189-201)
- `resources/views/pages/inventory/returns/index.blade.php`

**Action:**

1. **Add/fix route group in `web.php`:**
   ```php
   // Returns (Combined Supplier & Customer Returns)
   Route::prefix('returns')->name('inventory.returns.')->group(function () {
       Route::get('/', function () {
           return view('pages.inventory.returns.index');
       })->name('index');

       Route::get('/supplier', function () {
           return view('pages.inventory.returns.supplier-returns');
       })->name('supplier');

       Route::get('/customer', function () {
           return view('pages.inventory.returns.customer-returns');
       })->name('customer');
   });
   ```

2. **Verify view files exist:**
   - `resources/views/pages/inventory/returns/index.blade.php` ✅ (exists)
   - `resources/views/pages/inventory/returns/supplier-returns.blade.php`
   - `resources/views/pages/inventory/returns/customer-returns.blade.php`

3. **If separate returns pages missing:**
   - Use existing files from `resources/views/pages/inventory/receiving/`
   - Copy/redirect as needed

4. **Fix menu route:**
   - Check: `app/Modules/Retail/Config/menu.php`
   - Update: `'route' => 'inventory.returns.index'`

**Verify:**
```bash
# Test routes
curl -I http://localhost/returns
curl -I http://localhost/returns/supplier
curl -I http://localhost/returns/customer
```

**Done When:**
- `/returns` loads combined returns page
- `/returns/supplier` loads supplier returns
- `/returns/customer` loads customer returns
- Menu navigation works

---

### Task 4: Update Menu Configuration
**Type:** `auto` | **Effort:** `low`

**Files:**
- `app/Modules/Retail/Config/menu.php`

**Action:**

1. **Verify Inventory Control menu:**
   ```php
   [
       'label' => 'Inventory Control',
       'id' => 'inventory_control',
       'submenu' => [
           ['label' => 'Current Stock', 'route' => 'inventory.index'],
           ['label' => 'Stock Management', 'route' => 'inventory.stock-management'],
           ['label' => 'Stock Transfer', 'route' => 'inventory.stock-transfer'],
           ['label' => 'Transfer Analytics', 'route' => 'inventory.stock-transfer-analytics'],
           ['label' => 'Stock Movements', 'route' => 'inventory.movements'],
           ['label' => 'Goods In', 'route' => 'inventory.receiving.index'], // ✅ Fixed
           ['label' => 'Returns', 'route' => 'inventory.returns.index'],    // ✅ Fixed
       ]
   ]
   ```

2. **Test menu rendering:**
   - Clear view cache: `php artisan view:clear`
   - Load application in browser
   - Navigate menu items

**Verify:**
```bash
php artisan route:list --name=inventory
```

**Done When:**
- All menu items have correct route names
- No 404 errors when clicking menu items
- Menu renders without errors

---

### Task 5: End-to-End Testing
**Type:** `checkpoint:human-verify` | **Effort:** `low`

**Action:**

Test complete user flows:

1. **Goods In Flow:**
   - [ ] Click "Goods In" from menu
   - [ ] Page loads without 404
   - [ ] Create new goods-in transaction
   - [ ] View goods-in history
   - [ ] Verify data saves correctly

2. **Returns Flow:**
   - [ ] Click "Returns" from menu
   - [ ] Combined page loads
   - [ ] Switch between Supplier/Customer tabs
   - [ ] Create supplier return
   - [ ] Create customer return
   - [ ] Verify data saves correctly

3. **Navigation:**
   - [ ] All submenu items work
   - [ ] No broken links
   - [ ] Breadcrumbs display correctly

**Verify:**
- Manual testing in browser
- Check browser console for errors
- Check network tab for 404s

**Done When:**
- All flows tested successfully
- No 404 errors in console
- User can complete tasks without errors

---

## Success Criteria

- [ ] `/inventory/receiving` returns 200 OK
- [ ] `/returns` returns 200 OK
- [ ] All submenu items functional
- [ ] No 404 errors in browser console
- [ ] Menu navigation works end-to-end
- [ ] Create/View operations functional for both modules

---

## 🧪 Verification Commands

```bash
# 1. Check routes exist
php artisan route:list --name=receiving
php artisan route:list --name=returns

# 2. Clear caches
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 3. Test URLs (in browser or curl)
curl -I http://localhost/inventory/receiving
curl -I http://localhost/returns

# 4. Check for 404s in browser
# Open browser DevTools → Network tab
# Navigate to both pages
# Verify no 404 status codes
```

---

## 📝 Notes

- Both pages already have view files created
- Issue is likely route definition or naming mismatch
- Returns page already has tab-based UI (supplier/customer)
- Goods In page has modal-based create functionality

---

**Ready for implementation!**
