# 📊 SAGATOKOV3 PROJECT ANALYSIS

**Analysis Date**: 23 Januari 2026  
**Project Type**: POS & Inventory Management System  
**Current Stack**: Node.js (Express) + Electron + Vue/Alpine + MySQL  
**Target Migration**: Laravel 12 + Blade + Tailwind  

---

## 🎯 PROJECT OVERVIEW

**Saga Toko V3** adalah sistem POS & Inventory terintegrasi yang dirancang untuk retail, distributor, dan supplier. Sistem ini menggunakan:
- **Desktop App**: Electron + Node.js (dapat bekerja offline)
- **Frontend**: HTML5 + Vue/Alpine.js + Tailwind CSS v4
- **Backend**: Express.js (Node.js)
- **Database**: MySQL/MariaDB dengan multi-tenant support

---

## 📁 PROJECT STRUCTURE

```
sagatokov3/
├── src/                          # Frontend (HTML/CSS/JS)
│   ├── *.html                    # 50+ HTML template pages
│   ├── css/
│   │   ├── style.css             # Global Tailwind + custom styles
│   │   └── print.css             # Print styles for receipts/invoices
│   ├── js/
│   │   ├── index.js              # Main entry point
│   │   ├── app.js                # Core app initialization
│   │   ├── auth-handler.js       # Authentication logic
│   │   ├── services/
│   │   │   ├── api.js            # API client
│   │   │   ├── auth.js           # Auth service
│   │   │   ├── barcode-service.js # Barcode scanning
│   │   │   └── store.js          # State management
│   │   ├── components/           # Alpine.js components
│   │   ├── libs/                 # Third-party libraries
│   │   ├── print-utils.js        # Print functionality
│   │   └── qz-service.js         # Printer integration (QZ Tray)
│   ├── partials/                 # Reusable HTML partials
│   ├── images/                   # Static images/icons
│   └── index.html                # Main app shell
│
├── backend/                      # Node.js Backend
│   ├── server.js                 # Express app setup
│   ├── routes/
│   │   ├── auth.js               # Authentication routes
│   │   ├── license.js            # License management
│   │   ├── setup.js              # Initial setup
│   │   ├── admin/                # Admin routes
│   │   │   ├── analytics.js      # Analytics
│   │   │   ├── tenants.js        # Tenant management
│   │   │   ├── users.js          # User management
│   │   │   └── license.js        # License management
│   │   ├── tenant/               # Tenant-specific routes
│   │   │   ├── branches.js       # Branch management
│   │   │   ├── customers.js      # Customer management
│   │   │   ├── products.js       # Product management (1119 lines!)
│   │   │   ├── purchases.js      # Purchase orders
│   │   │   ├── sales-orders.js   # Sales orders
│   │   │   ├── transfers.js      # Stock transfers
│   │   │   ├── returns.js        # Return management
│   │   │   ├── suppliers.js      # Supplier management
│   │   │   ├── transactions.js   # POS transactions
│   │   │   ├── reports.js        # Reporting
│   │   │   ├── users.js          # User management
│   │   │   ├── settings.js       # Settings
│   │   │   ├── backup.js         # Backup/restore
│   │   │   ├── export.js         # Data export
│   │   │   ├── import.js         # Data import
│   │   │   └── ...
│   │   └── updates.js            # Update management
│   ├── models/
│   │   └── main/
│   │       ├── User.js           # System users
│   │       └── Tenant.js         # Tenant data
│   ├── middleware/
│   │   ├── auth.js               # Authentication middleware
│   │   └── tenantResolver.js     # Tenant context resolution
│   ├── services/                 # Business logic services
│   ├── utils/                    # Utility functions
│   ├── config/                   # Configuration files
│   ├── package.json              # Backend dependencies
│   └── ...
│
├── database/
│   ├── saga_tenant_rj0001.sql    # Sample tenant database (6951 lines!)
│   └── schemas/                  # Migration scripts
│
├── build/                        # Webpack build output
├── package.json                  # Frontend dependencies
├── webpack.config.js             # Webpack configuration
├── main-electron.js              # Electron main process
├── vite.config.js (or similar)   # Frontend build config
└── ...
```

---

## 🖥️ FRONTEND ARCHITECTURE

### HTML Templates (50+ pages)

**Dashboard & Analytics**
- `dashboard.html` - Main dashboard with metrics & charts
- `analytics.html` - Advanced analytics
- `reports.html` - Report center
- `admin-dashboard.html` - Admin panel

**POS System**
- `pos.html` - Point of Sale interface (1395 lines!)
  - Shopping cart
  - Product search/scanner
  - Payment processing
  - Transaction success modal
  - Receipt printing
- `transactions.js` - Transaction history

**Inventory Management**
- `inventory.html` - Stock management
- `stock-management.html` - Detailed stock control
- `stock-reports.html` - Stock analysis
- `transfer-item.html` - Inter-warehouse transfers
- `deadstock.html` - Dead stock analysis
- `goods-in.html` - Receiving goods

**Sales & Orders**
- `sales-orders.html` - Sales order management
- `sales-history.html` - Past sales
- `sales-men.html` - Salesman management

**Customers & Suppliers**
- `customers.html` - Customer database
- `suppliers.html` - Supplier management
- `receivables.html` - Customer receivables
- `supplier-debts.html` - Supplier payables
- `supplier-returns.html` - Supplier returns

**Purchasing**
- `purchases.html` - Purchase orders
- `purchase-reports.html` - Purchase analysis

**Returns & Refunds**
- `returns.html` - Sales returns
- `purchase-returns.html` - Purchase returns

**User & Settings**
- `users.html` - User management
- `profile.html` - User profile
- `settings.html`, `settings-general.html`, `settings-cashier.html`, `settings-printers.html`, `settings-scanner.html`
- `admin-users.html` - Admin user management
- `admin-tenants.html` - Tenant management
- `branches.html` - Branch/warehouse locations

**System**
- `signin.html` - Login page
- `signup.html` - Registration
- `first-run.html` - Initial setup
- `admin-license.html` - License management
- Error pages: `404.html`, etc.

**UI Components Library**
- `alerts.html`, `avatars.html`, `badge.html`, `buttons.html`
- `calendar.html`, `form-elements.html`
- `bar-chart.html`, `line-chart.html`
- `notifications.html`, `basic-tables.html`
- `videos.html`, `images.html`

### CSS & Styling

**File**: `src/css/style.css` (1020 lines)

**Configuration**:
```css
- Font: Outfit (Google Fonts)
- Tailwind CSS v4 with custom theme
- Custom breakpoints: 2xsm (375px), xsm (425px), 3xl (2000px)
- Custom color palette:
  * Brand colors (25 shades)
  * Blue light (25 shades)
  * Gray (25 shades)
  * Success, Warning, Error, Info colors
  * Custom spacing, shadows, borders
- Dark mode support
- Print styles for receipts/invoices
```

**Key Features**:
- Responsive design (mobile-first)
- Dark mode toggle
- Custom grid system
- Animation utilities
- Print-specific styles

### JavaScript Architecture

**Main Entry Point**: `src/js/index.js`

**Services**:
1. **api.js** - API client wrapper
   - Axios-based HTTP client
   - Authentication token handling
   - Request/response interceptors
   - Multi-tenant API support

2. **auth.js** - Authentication
   - Login/logout
   - Token management
   - Session handling
   - Role/permission checking

3. **barcode-service.js** - Barcode scanning
   - QR code scanning
   - Barcode reader integration
   - Product lookup via barcode

4. **store.js** - State management
   - Shopping cart state
   - User session
   - Branch/warehouse context
   - Notification queue

**Components** (Alpine.js-based):
- Reusable component system
- Reactive data binding
- Event handling

**Utilities**:
- `print-utils.js` - Receipt/invoice printing
- `qz-service.js` - Printer integration (QZ Tray for Electron)
- Formatting utilities (currency, dates)
- Validation helpers

**Third-party Libraries**:
```javascript
- Alpine.js (HTML magic framework)
- Axios (HTTP client)
- Chart.js / ApexCharts (Charts)
- SweetAlert2 (Alerts/modals)
- QZ Tray (Receipt printer support)
- html5-qrcode (QR code scanning)
- Prism.js (Code highlighting)
- Flatpickr (Date picker)
- JSVector Map (Maps)
- Swiper (Carousels)
```

---

## 🔌 BACKEND API ARCHITECTURE

### Express.js Server Setup

**File**: `backend/server.js` (192 lines)

**Port**: 3000 (configurable via .env)

**Middleware Stack**:
```
CORS → JSON Parser → URL Encoder → Auth → Tenant Resolver → Routes
```

### Route Structure

#### Authentication Routes (`routes/auth.js`)
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `POST /api/auth/register` - New user registration
- `GET /api/auth/profile` - Current user profile
- `PUT /api/auth/profile` - Update profile
- `POST /api/auth/change-password` - Change password

#### Admin Routes (`routes/admin/`)

**Tenants Management** (`tenants.js`)
- `GET /api/admin/tenants` - List all tenants
- `POST /api/admin/tenants` - Create tenant
- `GET /api/admin/tenants/:id` - Tenant details
- `PUT /api/admin/tenants/:id` - Update tenant
- `DELETE /api/admin/tenants/:id` - Delete tenant

**Users Management** (`users.js`)
- `GET /api/admin/users` - System users
- `POST /api/admin/users` - Create system user
- `PUT /api/admin/users/:id` - Update user
- `DELETE /api/admin/users/:id` - Delete user

**Analytics** (`analytics.js`)
- `GET /api/admin/analytics/overview` - System overview
- `GET /api/admin/analytics/tenants` - Tenant metrics
- `GET /api/admin/analytics/usage` - Usage statistics

#### Tenant Routes (`routes/tenant/`)

**Products** (`products.js` - 1119 lines!) - Core inventory
```javascript
GET    /api/products                    - List products
GET    /api/products/:id                - Product details
POST   /api/products                    - Create product
PUT    /api/products/:id                - Update product
DELETE /api/products/:id                - Delete product
GET    /api/products/search             - Product search
GET    /api/products/expiry             - Expiry date tracking
GET    /api/products/low-stock          - Low stock alerts
GET    /api/products/price-check        - Quick price lookup
GET    /api/products/barcode/:barcode   - Lookup by barcode
```

**Branches** (`branches.js`)
```javascript
GET    /api/branches                    - List branches
POST   /api/branches                    - Create branch
PUT    /api/branches/:id                - Update branch
DELETE /api/branches/:id                - Delete branch
GET    /api/branches/stock/:id          - Branch stock levels
```

**Customers** (`customers.js`)
```javascript
GET    /api/customers                   - List customers
POST   /api/customers                   - Create customer
PUT    /api/customers/:id               - Update customer
DELETE /api/customers/:id               - Delete customer
GET    /api/customers/:id/credit        - Customer credit
GET    /api/customers/:id/history       - Purchase history
POST   /api/customers/:id/credit-limit  - Update credit limit
```

**Suppliers** (`suppliers.js`)
```javascript
GET    /api/suppliers                   - List suppliers
POST   /api/suppliers                   - Create supplier
PUT    /api/suppliers/:id               - Update supplier
DELETE /api/suppliers/:id               - Delete supplier
GET    /api/suppliers/:id/products      - Supplier products
GET    /api/suppliers/:id/debts         - Payable amount
```

**Transactions/POS** (`transactions.js`)
```javascript
POST   /api/transactions                - Create POS transaction
GET    /api/transactions                - Transaction history
GET    /api/transactions/:id            - Transaction details
POST   /api/transactions/:id/refund     - Process refund
GET    /api/transactions/daily          - Daily sales
GET    /api/transactions/reports        - Transaction reports
```

**Sales Orders** (`sales-orders.js`)
```javascript
GET    /api/sales-orders                - List orders
POST   /api/sales-orders                - Create order
PUT    /api/sales-orders/:id            - Update order
DELETE /api/sales-orders/:id            - Cancel order
POST   /api/sales-orders/:id/approve    - Approve order
POST   /api/sales-orders/:id/complete   - Complete order
```

**Purchases** (`purchases.js`)
```javascript
GET    /api/purchases                   - Purchase orders
POST   /api/purchases                   - Create PO
PUT    /api/purchases/:id               - Update PO
POST   /api/purchases/:id/receive       - Receive goods
GET    /api/purchases/:id/items         - PO items
POST   /api/purchases/:id/invoice       - Supplier invoice
```

**Stock Transfers** (`transfers.js`)
```javascript
POST   /api/transfers                   - Create transfer
GET    /api/transfers                   - Transfer history
PUT    /api/transfers/:id               - Update transfer
POST   /api/transfers/:id/approve       - Approve transfer
POST   /api/transfers/:id/complete      - Complete transfer
GET    /api/transfers/:id/status        - Transfer status
```

**Returns** (`returns.js`)
```javascript
POST   /api/returns                     - Sales return
GET    /api/returns                     - Return history
POST   /api/returns/:id/approve         - Approve return
GET    /api/returns/items               - Return items
```

**Purchase Returns** (`purchase-returns.js`)
```javascript
POST   /api/purchase-returns            - Supplier return
GET    /api/purchase-returns            - Return history
PUT    /api/purchase-returns/:id        - Update
```

**Reports** (`reports.js`)
```javascript
GET    /api/reports/sales               - Sales report
GET    /api/reports/inventory           - Inventory report
GET    /api/reports/profit-loss         - P&L report
GET    /api/reports/daily-sales         - Daily summary
GET    /api/reports/weekly-sales        - Weekly summary
GET    /api/reports/monthly-sales       - Monthly summary
GET    /api/reports/by-category         - Category analysis
GET    /api/reports/by-customer         - Customer analysis
GET    /api/reports/receivables         - Customer debts
GET    /api/reports/payables            - Supplier debts
```

**Salesmen** (`salesmen.js`)
```javascript
GET    /api/salesmen                    - Salesman list
POST   /api/salesmen                    - Create salesman
PUT    /api/salesmen/:id                - Update salesman
GET    /api/salesmen/:id/sales          - Salesman sales
```

**Debts Management** (`debts.js`)
```javascript
GET    /api/debts/customer              - Customer debts
GET    /api/debts/supplier              - Supplier debts
POST   /api/debts/:id/payment           - Record payment
GET    /api/debts/:id/aging             - Aging analysis
```

**Settings** (`settings.js`)
```javascript
GET    /api/settings                    - All settings
PUT    /api/settings/:key               - Update setting
POST   /api/settings/cashier            - Cashier settings
POST   /api/settings/printer            - Printer config
POST   /api/settings/scanner            - Scanner config
```

**Users** (`users.js`)
```javascript
GET    /api/users                       - Tenant users
POST   /api/users                       - Create user
PUT    /api/users/:id                   - Update user
DELETE /api/users/:id                   - Delete user
POST   /api/users/:id/role              - Assign role
GET    /api/users/:id/permissions       - User permissions
```

**Notifications** (`notifications.js`)
```javascript
GET    /api/notifications               - User notifications
POST   /api/notifications/:id/read      - Mark as read
DELETE /api/notifications/:id           - Delete notification
GET    /api/notifications/low-stock     - Stock alerts
```

**Backup & Restore** (`backup.js`)
```javascript
POST   /api/backup                      - Create backup
GET    /api/backup/list                 - List backups
POST   /api/backup/:id/restore          - Restore backup
GET    /api/backup/schedule             - Scheduled backups
```

**Import & Export** (`export.js`, `import.js`)
```javascript
POST   /api/export/products             - Export products
POST   /api/export/customers            - Export customers
POST   /api/export/transactions         - Export transactions
POST   /api/import/products             - Import products
POST   /api/import/customers            - Import customers
GET    /api/import/template             - Download template
```

**File Upload** (`upload.js`)
```javascript
POST   /api/upload/product-image        - Upload product image
POST   /api/upload/bulk-import          - Bulk import
```

---

## 🗄️ DATABASE SCHEMA

### Table Structure (dari SQL dump - 6951 lines)

**Master Data Tables**:
1. **branches** - Warehouse/branch locations
2. **branch_stock** - Stock per branch
3. **products** - Master products
4. **product_units** - Product unit conversions
5. **categories** - Product categories
6. **suppliers** - Supplier master
7. **customers** - Customer master
8. **users** - Branch users

**Transaction Tables**:
9. **transactions** - POS sales
10. **transaction_items** - POS line items
11. **invoices** - Invoice/receipt data
12. **sales_orders** - Sales order header
13. **sales_order_items** - Sales order details
14. **purchases** - Purchase orders
15. **purchase_items** - PO details with expiry tracking
16. **returns** - Sales returns
17. **purchase_returns** - Supplier returns

**Inventory Tables**:
18. **stock_movements** - Stock in/out logs
19. **stock_adjustments** - Physical count adjustments
20. **transfers** - Inter-warehouse transfers
21. **transfer_items** - Transfer details

**Financial Tables**:
22. **payments** - Payment records
23. **payment_debts** - Outstanding debts
24. **profit_logs** - Profit tracking

**Configuration Tables**:
25. **settings** - System settings
26. **activity_logs** - Audit trail
27. **accounting_accounts** - Chart of accounts
28. **accounting_journal** - Journal entries

### Sample Data

Database includes:
- Multiple branches (e.g., "Pusat", "RJ Plastik 2")
- 100+ products with SKU, barcode, pricing
- Stock tracking with min/max levels
- Sample transactions and sales orders

---

## 🎨 UI/UX FEATURES

### Design System

**Color Palette**:
- Primary Brand: Blue-based (#465FFF)
- Accent: 25 shades per color family
- Neutrals: Complete gray scale
- Status: Green (success), Red (error), Yellow (warning), Blue (info)

**Typography**:
- Font: Outfit (Google Fonts)
- Responsive sizes from 12px to 72px
- Multiple line-height options

**Layout**:
- Grid-based system
- Sidebar navigation
- Top header bar
- Responsive cards
- Mobile-optimized forms

### Key UI Components

1. **Dashboard**
   - Sales metrics cards
   - Charts (line, bar, pie)
   - Recent transactions table
   - Quick actions
   - Notifications

2. **POS Interface**
   - Product grid/list with search
   - Shopping cart
   - Item quantity adjuster
   - Discount calculator
   - Payment methods selector
   - Receipt printer
   - Transaction success modal

3. **Inventory**
   - Stock level indicators
   - Low stock alerts
   - Warehouse selector
   - Product search
   - Batch management

4. **Tables**
   - Sortable columns
   - Pagination
   - Row selection
   - Bulk actions
   - Search/filter

5. **Forms**
   - Input validation
   - Date pickers
   - Dropdown selects
   - File uploads
   - Multi-field sections

6. **Modals/Dialogs**
   - Confirmation dialogs
   - Data entry forms
   - Success/error messages
   - Alerts (SweetAlert2)

---

## 📊 KEY FEATURES

### POS Module
✅ Real-time product search  
✅ Barcode/QR code scanning  
✅ Shopping cart management  
✅ Multiple payment methods  
✅ Discount & tax calculation  
✅ Receipt printing (receipt printer integration)  
✅ Transaction history  
✅ Refund/return processing  
✅ Daily sales report  

### Inventory Module
✅ Stock level tracking per branch  
✅ Low stock alerts  
✅ Stock adjustment (physical count)  
✅ Inter-warehouse transfers  
✅ Stock movement history  
✅ Expiry date tracking (for perishables)  
✅ Deadstock analysis  
✅ Multiple product units support  

### Sales Management
✅ Sales order creation & approval  
✅ Customer management  
✅ Customer credit limits  
✅ Purchase history per customer  
✅ Sales returns management  
✅ Salesman tracking  
✅ Customer receivables  

### Purchasing
✅ Purchase order creation  
✅ Supplier management  
✅ Goods receiving  
✅ Purchase returns  
✅ Supplier payables  
✅ Payment tracking  

### Reporting & Analytics
✅ Daily/weekly/monthly sales reports  
✅ Sales by product/category/customer  
✅ Profit & loss analysis  
✅ Inventory valuation  
✅ Customer debt aging  
✅ Supplier payment aging  
✅ Stock movement reports  
✅ Export to Excel/PDF  

### System Features
✅ Multi-branch support  
✅ User roles & permissions  
✅ Activity logging/audit trail  
✅ Backup & restore  
✅ Data import/export  
✅ Settings management  
✅ Print configuration  
✅ Scanner configuration  
✅ License management  
✅ Multi-tenant support  

---

## 🔒 AUTHENTICATION & AUTHORIZATION

**Method**: JWT Token-based

**Flows**:
1. Login → Get JWT token → Store in localStorage
2. API requests include Authorization header
3. Token validation on backend
4. Tenant context resolution
5. Role-based access control

**User Roles**:
- Admin (system-wide)
- Manager (tenant-level)
- Cashier (POS operations)
- Salesman (sales tracking)
- Warehouse Staff (inventory)

---

## 🚀 TECHNOLOGY STACK

### Frontend
- **HTML5** (semantic markup)
- **CSS3** (Tailwind CSS v4 + custom)
- **JavaScript** (ES6+)
- **Alpine.js** (reactive components)
- **Webpack** (bundling)

### Backend
- **Node.js** (runtime)
- **Express.js** (web framework)
- **MySQL/MariaDB** (database)
- **JWT** (authentication)

### Desktop App
- **Electron** (desktop wrapper)
- **Electron-builder** (packaging)
- **QZ Tray** (receipt printer support)

### Build Tools
- **Webpack** (module bundler)
- **Babel** (transpiler)
- **Prettier** (code formatting)
- **PostCSS** (CSS processing)

### Libraries
- Axios, Chart.js, SweetAlert2, html5-qrcode, QZ Tray, Flatpickr, JSVector Map, Swiper, Prism.js

---

## 📦 DEPENDENCIES

### Frontend (`package.json`)
```json
{
  "devDependencies": {
    "webpack": "^5.x",
    "babel": "^7.x",
    "tailwindcss": "^4.x",
    "electron": "^39.x",
    "postcss": "^8.x",
    "prettier": "^3.x"
  },
  "dependencies": {
    "axios": "^1.x",
    "alpinejs": "^3.x"
  }
}
```

### Backend
```javascript
- express
- cors
- dotenv
- mysql2 (or sequelize)
- jsonwebtoken
- express-validator
```

---

## 🔄 WORKFLOW EXAMPLES

### POS Transaction Flow
```
1. User searches/scans product
2. Product added to cart
3. Quantity adjusted
4. Discount applied (if any)
5. Customer selected
6. Payment method chosen
7. Amount tendered entered
8. Change calculated
9. Transaction saved to DB
10. Receipt printed
11. Success modal shown
12. Transaction logged
```

### Purchase Order Flow
```
1. Supplier selected
2. Products added to PO
3. Quantities entered
4. Prices auto-filled from supplier
5. PO created (draft status)
6. Manager approval required
7. PO sent (status = sent)
8. Goods received (partial/full)
9. Received items added to stock
10. Invoice matching
11. Payment processed
12. PO closed
```

### Stock Transfer Flow
```
1. From-branch selected
2. To-branch selected
3. Products added
4. Quantities entered
5. Transfer created
6. Manager approval
7. Transferred (status = in-transit)
8. Received at destination
9. Stock updated both branches
10. Transfer completed
```

---

## ✨ MIGRATION STRATEGY FOR LARAVEL

### Phase 1: Foundation
1. Create database migrations from SQL schema
2. Build Eloquent models
3. Setup Blade templates (convert HTML)
4. Copy CSS & styling

### Phase 2: Backend
5. Create controllers from Express routes
6. Build services & business logic
7. Implement API routes
8. Setup middleware (auth, tenant)

### Phase 3: Frontend
9. Convert Alpine.js to Livewire/Alpine (choose one)
10. Implement JS services
11. Copy components
12. Test all pages

### Phase 4: Advanced
13. Setup queues (if needed)
14. Implement caching
15. Build admin panel
16. Testing & optimization

---

## 📋 CONVERSION CHECKLIST

### HTML → Blade
- [ ] Dashboard → dashboard.blade.php
- [ ] POS → pos.blade.php
- [ ] Inventory → inventory.blade.php
- [ ] ... (48+ more templates)

### Routes → Laravel Routes
- [ ] Auth routes (login, register, profile)
- [ ] Product CRUD
- [ ] Customer CRUD
- [ ] ... (20+ route groups)

### Services
- [ ] API client → HTTP client (Guzzle)
- [ ] Store → Session/Cache
- [ ] Auth → Laravel Auth
- [ ] Barcode → Library integration

### Styling
- [ ] Import Tailwind config
- [ ] Copy print.css
- [ ] Dark mode setup
- [ ] Custom components

---

**Next Steps**:
1. Start with database migrations
2. Build Models
3. Convert HTML → Blade (templating)
4. Copy CSS/styling
5. Port routes & controllers
6. Implement business logic
7. Test thoroughly

---

**Document Status**: Complete analysis of sagatokov3  
**Ready for Implementation**: YES ✅
