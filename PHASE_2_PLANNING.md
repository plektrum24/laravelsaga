# SAGA POS - Phase 2: Backend Architecture Planning

**Status**: Ready to Begin  
**Previous Phase**: Phase 1 ✅ Complete  
**Target**: Database migrations, Eloquent models, API endpoints, authentication  
**Estimated Duration**: 2-3 weeks  

---

## 📋 Phase 2 Overview

Phase 2 focuses on building the backend infrastructure to connect frontend services with persistent data storage. This includes:

1. **Database Design** - Schema and relationships
2. **Migrations** - 28+ database tables
3. **Eloquent Models** - 28+ models with relationships
4. **API Routes & Controllers** - 150+ endpoints
5. **Authentication** - Laravel Sanctum integration
6. **Authorization** - Role-based access control middleware
7. **Validation** - Comprehensive form validation
8. **Services** - Business logic layer

---

## 🗄️ Database Schema Design

### Core Tables (11)

#### 1. Users Table
```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  avatar VARCHAR(255),
  is_active BOOLEAN DEFAULT true,
  last_login_at TIMESTAMP,
  email_verified_at TIMESTAMP,
  remember_token VARCHAR(100),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

#### 2. Tenants Table
```sql
CREATE TABLE tenants (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  logo VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(20),
  address TEXT,
  city VARCHAR(100),
  province VARCHAR(100),
  zip_code VARCHAR(10),
  country VARCHAR(100),
  is_active BOOLEAN DEFAULT true,
  subscription_plan VARCHAR(50), -- basic, professional, enterprise
  subscription_end_date DATE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

#### 3. Roles Table
```sql
CREATE TABLE roles (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE KEY (tenant_id, name),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

#### 4. Permissions Table
```sql
CREATE TABLE permissions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) UNIQUE NOT NULL,
  description TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

#### 5. Role Permissions Table
```sql
CREATE TABLE role_permissions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  role_id BIGINT NOT NULL,
  permission_id BIGINT NOT NULL,
  created_at TIMESTAMP,
  UNIQUE KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id),
  FOREIGN KEY (permission_id) REFERENCES permissions(id)
);
```

#### 6. User Roles Table
```sql
CREATE TABLE user_roles (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  role_id BIGINT NOT NULL,
  created_at TIMESTAMP,
  UNIQUE KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

#### 7. Branches Table
```sql
CREATE TABLE branches (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  code VARCHAR(50) UNIQUE NOT NULL,
  location VARCHAR(255),
  address TEXT,
  phone VARCHAR(20),
  email VARCHAR(100),
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

#### 8. Products Table
```sql
CREATE TABLE products (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  sku VARCHAR(100) UNIQUE NOT NULL,
  barcode VARCHAR(100) UNIQUE,
  category_id BIGINT,
  unit_id BIGINT,
  cost_price DECIMAL(15,2),
  selling_price DECIMAL(15,2),
  discount_price DECIMAL(15,2),
  tax_rate DECIMAL(5,2),
  image VARCHAR(255),
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  deleted_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (unit_id) REFERENCES units(id)
);
```

#### 9. Customers Table
```sql
CREATE TABLE customers (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100),
  email VARCHAR(100),
  phone VARCHAR(20),
  address TEXT,
  city VARCHAR(100),
  province VARCHAR(100),
  zip_code VARCHAR(10),
  country VARCHAR(100),
  customer_type VARCHAR(50), -- individual, business
  tax_id VARCHAR(50),
  credit_limit DECIMAL(15,2),
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

#### 10. Sales Orders Table
```sql
CREATE TABLE sales_orders (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT NOT NULL,
  order_number VARCHAR(50) UNIQUE NOT NULL,
  customer_id BIGINT,
  user_id BIGINT NOT NULL,
  order_date DATE NOT NULL,
  subtotal DECIMAL(15,2),
  discount DECIMAL(15,2),
  discount_percent DECIMAL(5,2),
  tax_amount DECIMAL(15,2),
  total DECIMAL(15,2) NOT NULL,
  payment_method VARCHAR(50), -- cash, card, transfer
  payment_status VARCHAR(50), -- pending, paid, partial
  order_status VARCHAR(50), -- draft, completed, cancelled
  notes TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  FOREIGN KEY (customer_id) REFERENCES customers(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 11. Inventory Table
```sql
CREATE TABLE inventory (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id BIGINT NOT NULL,
  branch_id BIGINT NOT NULL,
  quantity_on_hand INT NOT NULL,
  quantity_reserved INT DEFAULT 0,
  quantity_available INT GENERATED ALWAYS AS (quantity_on_hand - quantity_reserved),
  reorder_level INT,
  reorder_qty INT,
  last_count_date DATE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE KEY (product_id, branch_id),
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id)
);
```

### Supporting Tables (17)

#### 12-13. Categories & Units
```sql
CREATE TABLE categories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

CREATE TABLE units (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(50) NOT NULL, -- pcs, kg, liter
  abbreviation VARCHAR(10),
  created_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

#### 14-15. Sales Order Items & Payments
```sql
CREATE TABLE sales_order_items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  sales_order_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(15,2) NOT NULL,
  discount DECIMAL(15,2),
  tax_amount DECIMAL(15,2),
  subtotal DECIMAL(15,2),
  created_at TIMESTAMP,
  FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE payments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  sales_order_id BIGINT NOT NULL,
  payment_method VARCHAR(50),
  amount DECIMAL(15,2) NOT NULL,
  reference_number VARCHAR(100),
  payment_date DATETIME,
  notes TEXT,
  created_at TIMESTAMP,
  FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id)
);
```

#### 16-21. Purchase Management Tables
```sql
CREATE TABLE suppliers (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  address TEXT,
  city VARCHAR(100),
  contact_person VARCHAR(100),
  payment_terms VARCHAR(50),
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

CREATE TABLE purchase_orders (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  po_number VARCHAR(50) UNIQUE NOT NULL,
  supplier_id BIGINT NOT NULL,
  order_date DATE,
  delivery_date DATE,
  subtotal DECIMAL(15,2),
  tax_amount DECIMAL(15,2),
  total DECIMAL(15,2),
  status VARCHAR(50),
  created_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE purchase_order_items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  purchase_order_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  quantity INT NOT NULL,
  unit_cost DECIMAL(15,2),
  subtotal DECIMAL(15,2),
  FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE inventory_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id BIGINT NOT NULL,
  branch_id BIGINT,
  quantity_change INT,
  type VARCHAR(50), -- in, out, adjustment, return
  reference_id BIGINT,
  reference_type VARCHAR(50), -- sales_order, purchase_order
  notes TEXT,
  created_by BIGINT,
  created_at TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### 22-28. Reporting & Settings Tables
```sql
CREATE TABLE reports (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  type VARCHAR(50), -- sales, inventory, customer, supplier
  filters JSON,
  created_by BIGINT,
  is_favorite BOOLEAN DEFAULT false,
  created_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE settings (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  key VARCHAR(100),
  value LONGTEXT,
  UNIQUE KEY (tenant_id, key),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

CREATE TABLE audit_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  user_id BIGINT,
  action VARCHAR(100),
  model_type VARCHAR(100),
  model_id BIGINT,
  changes JSON,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE activity_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT,
  activity VARCHAR(255),
  entity_type VARCHAR(100),
  entity_id BIGINT,
  created_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE notifications (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  user_id BIGINT,
  title VARCHAR(255),
  message TEXT,
  type VARCHAR(50), -- info, warning, error, success
  is_read BOOLEAN DEFAULT false,
  created_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE printer_settings (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  name VARCHAR(100),
  device_id VARCHAR(100),
  printer_type VARCHAR(50), -- receipt, label
  is_default BOOLEAN DEFAULT false,
  created_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id)
);
```

---

## 📝 Eloquent Models (28 Total)

### Core Models
```
User
Tenant
Role
Permission
Branch
Product
Customer
SalesOrder
SalesOrderItem
Supplier
PurchaseOrder
PurchaseOrderItem
Payment
Inventory
InventoryLog
```

### Supporting Models
```
Category
Unit
Report
Setting
AuditLog
ActivityLog
Notification
PrinterSetting
```

### Model Relationships Summary

**User**:
- `belongsTo(Tenant)`
- `belongsToMany(Role)` through user_roles
- `hasMany(SalesOrder)`
- `hasMany(InventoryLog)`

**Tenant**:
- `hasMany(User)`
- `hasMany(Branch)`
- `hasMany(Product)`
- `hasMany(Role)`

**Product**:
- `belongsTo(Tenant)`
- `belongsTo(Category)`
- `belongsTo(Unit)`
- `hasMany(SalesOrderItem)`
- `hasMany(InventoryLog)`
- `hasMany(Inventory)` through branch

**SalesOrder**:
- `belongsTo(Tenant)`
- `belongsTo(Customer)`
- `belongsTo(User)` creator
- `hasMany(SalesOrderItem)`
- `hasMany(Payment)`

---

## 🔌 API Endpoints (150+)

### Authentication Endpoints (8)
```
POST   /api/auth/register           → User registration
POST   /api/auth/login              → User login
POST   /api/auth/logout             → User logout
GET    /api/auth/me                 → Get current user
POST   /api/auth/refresh            → Refresh token
POST   /api/auth/change-password    → Change password
POST   /api/auth/forgot-password    → Password reset request
POST   /api/auth/reset-password     → Password reset
```

### Products Endpoints (12)
```
GET    /api/products                → List (with filtering)
POST   /api/products                → Create
GET    /api/products/:id            → Get single
PUT    /api/products/:id            → Update
DELETE /api/products/:id            → Delete
POST   /api/products/:id/restore    → Restore deleted
GET    /api/products/barcode/:code  → Find by barcode
GET    /api/products/sku/:sku       → Find by SKU
POST   /api/products/bulk-import    → Bulk import
POST   /api/products/:id/image      → Upload image
GET    /api/categories              → List categories
GET    /api/units                   → List units
```

### Customers Endpoints (8)
```
GET    /api/customers               → List
POST   /api/customers               → Create
GET    /api/customers/:id           → Get single
PUT    /api/customers/:id           → Update
DELETE /api/customers/:id           → Delete
GET    /api/customers/:id/orders    → Get customer orders
GET    /api/customers/:id/balance   → Get account balance
POST   /api/customers/bulk-import   → Bulk import
```

### Sales Endpoints (15)
```
GET    /api/sales                   → List orders
POST   /api/sales                   → Create order
GET    /api/sales/:id               → Get order details
PUT    /api/sales/:id               → Update order
DELETE /api/sales/:id               → Cancel order
POST   /api/sales/:id/items         → Add items
PUT    /api/sales/:id/items/:item   → Update item
DELETE /api/sales/:id/items/:item   → Remove item
POST   /api/sales/:id/print         → Print receipt
POST   /api/sales/:id/email         → Email invoice
GET    /api/sales/search            → Search orders
POST   /api/sales/:id/payments      → Add payment
GET    /api/sales/:id/payments      → Get payments
POST   /api/sales/:id/complete      → Mark complete
```

### Inventory Endpoints (12)
```
GET    /api/inventory               → List inventory
GET    /api/inventory/:product      → Get stock
PUT    /api/inventory/:product      → Update stock
POST   /api/inventory/:product/adj  → Stock adjustment
GET    /api/inventory/low-stock     → Low stock items
GET    /api/inventory/out-of-stock  → Out of stock items
POST   /api/inventory/count         → Stock count
GET    /api/inventory/logs          → Inventory logs
POST   /api/inventory/transfer      → Transfer between branches
```

### Reports Endpoints (10)
```
GET    /api/reports/sales           → Sales report
GET    /api/reports/inventory       → Inventory report
GET    /api/reports/customers       → Customer report
GET    /api/reports/suppliers       → Supplier report
GET    /api/reports/profitability   → Profit analysis
GET    /api/reports/sales-by-item   → Item sales analysis
GET    /api/reports/sales-by-month  → Monthly trends
POST   /api/reports/custom          → Custom report
GET    /api/reports/:id             → Get saved report
DELETE /api/reports/:id             → Delete report
```

### And more for:
- **Suppliers** (10 endpoints)
- **Purchase Orders** (12 endpoints)
- **Payments** (8 endpoints)
- **Users & Roles** (15 endpoints)
- **Settings** (8 endpoints)
- **Branches** (8 endpoints)
- **Audit Logs** (6 endpoints)

---

## 🔐 Authentication Strategy

### Using Laravel Sanctum
```php
// routes/api.php

Route::middleware('guest:sanctum')->group(function () {
    Route::post('auth/register', AuthController::register);
    Route::post('auth/login', AuthController::login);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', AuthController::me);
    Route::post('auth/logout', AuthController::logout);
    Route::post('auth/change-password', AuthController::changePassword);
    
    // All protected routes...
});
```

### Role-Based Middleware
```php
// app/Http/Middleware/CheckRole.php

public function handle($request, Closure $next, ...$roles) {
    if (!auth()->user()->hasRole($roles)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    return $next($request);
}

// Usage:
Route::middleware(['auth:sanctum', 'role:admin,manager'])->group(function () {
    // Routes...
});
```

---

## 📚 File Structure for Phase 2

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── CustomerController.php
│   │   ├── SalesOrderController.php
│   │   ├── InventoryController.php
│   │   ├── ReportController.php
│   │   ├── UserController.php
│   │   ├── RoleController.php
│   │   ├── PermissionController.php
│   │   └── ... (15+ controllers total)
│   ├── Middleware/
│   │   ├── CheckRole.php
│   │   ├── CheckPermission.php
│   │   ├── CheckTenant.php
│   │   └── Authenticate.php
│   └── Requests/
│       ├── StoreProductRequest.php
│       ├── UpdateProductRequest.php
│       ├── StoreSalesOrderRequest.php
│       └── ... (validation classes)
├── Models/
│   ├── User.php
│   ├── Tenant.php
│   ├── Product.php
│   ├── Customer.php
│   ├── SalesOrder.php
│   ├── SalesOrderItem.php
│   ├── Inventory.php
│   ├── Role.php
│   ├── Permission.php
│   └── ... (28 models total)
├── Services/
│   ├── SalesService.php
│   ├── InventoryService.php
│   ├── ReportService.php
│   ├── ProductService.php
│   ├── CustomerService.php
│   └── ... (business logic)
├── Exceptions/
│   ├── InvalidTenantException.php
│   ├── InsufficientStockException.php
│   └── ...
└── Traits/
    ├── MultiTenantModel.php
    ├── Loggable.php
    └── ...

database/
├── migrations/
│   ├── 2024_01_01_000001_create_tenants_table.php
│   ├── 2024_01_01_000002_create_users_table.php
│   ├── ... (28 migrations)
│   └── 2024_01_01_000100_create_notifications_table.php
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── TenantSeeder.php
│   ├── UserSeeder.php
│   ├── ProductSeeder.php
│   ├── RolePermissionSeeder.php
│   └── ...
└── factories/
    ├── UserFactory.php
    ├── ProductFactory.php
    ├── CustomerFactory.php
    └── ...

routes/
├── api.php          # All API routes (150+ endpoints)
└── web.php          # Web routes (frontend pages)
```

---

## 🎯 Phase 2 Implementation Order

### Week 1: Foundation
- [x] Database schema design (DONE ✓)
- [ ] Create 28 migrations
- [ ] Create 28 Eloquent models with relationships
- [ ] Setup Laravel Sanctum authentication

### Week 2: Core APIs
- [ ] Authentication endpoints (login, register, logout)
- [ ] Product management endpoints
- [ ] Customer management endpoints
- [ ] Basic inventory endpoints

### Week 3: Advanced Features
- [ ] Sales order management (complete flow)
- [ ] Payment processing
- [ ] Reporting endpoints
- [ ] Role-based authorization

### Week 4: Polish & Testing
- [ ] Form validation rules
- [ ] Error handling
- [ ] API testing
- [ ] Documentation

---

## ✅ Success Criteria

Phase 2 is complete when:

1. ✅ All 28 migrations created and running without errors
2. ✅ All 28 models with proper relationships
3. ✅ Authentication working (Sanctum tokens)
4. ✅ 150+ API endpoints functional
5. ✅ All frontend services connected and working
6. ✅ Comprehensive API documentation
7. ✅ Test data populated (seeders)
8. ✅ All endpoints tested and validated
9. ✅ Error handling implemented
10. ✅ Performance optimized (pagination, caching)

---

## 🚀 Ready to Begin?

When ready, proceed with:
1. Create `database/migrations/2024_01_01_000001_create_tenants_table.php`
2. Create `database/migrations/2024_01_01_000002_create_users_table.php`
3. Continue with remaining migrations...
4. Create corresponding Eloquent models
5. Define relationships between models
6. Implement API endpoints

**All frontend is complete and ready for backend connection!**

