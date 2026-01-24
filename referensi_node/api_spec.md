# SAGA TOKO API Specification

> **Version:** 1.0.0  
> **Last Updated:** 2024-12-25  
> **Base URL:** `http://localhost:3000/api`  
> **Auth:** Bearer Token (JWT)

---

## 📘 Panduan Dokumentasi

Dokumen ini berisi spesifikasi lengkap API SAGA TOKO untuk keperluan pengembangan aplikasi mobile (Android/iOS) dan integrasi sistem lainnya.

### Struktur Penulisan Endpoint

Setiap endpoint didokumentasikan dengan format:

```
### METHOD /endpoint/path
Deskripsi singkat fungsi endpoint.

**Query Parameters:** (jika ada)
| Param | Type | Default | Description |

**Request:** (untuk POST/PUT/PATCH)
{ JSON body }

**Response:**
{ JSON response }
```

### Simbol Status
- ✅ = Sudah tested & production ready
- ⚠️ = Perlu validasi tambahan
- 🔒 = Memerlukan autentikasi

---

## 🌐 Environment Configuration

### Development
```
BASE_URL = http://localhost:3000/api
WS_URL = ws://localhost:3000
```

### Production (TODO: Update setelah deploy)
```
BASE_URL = https://api.sagatoko.com/api
WS_URL = wss://api.sagatoko.com
```

### Android Network Config
```kotlin
// Retrofit Base URL
const val BASE_URL = "http://10.0.2.2:3000/api/" // Emulator
const val BASE_URL = "http://192.168.x.x:3000/api/" // Physical device
```

---

## 🔐 Authentication Flow

### Login Flow
```
1. POST /auth/login 
   → Kirim email & password
   → Terima { token, user, tenant }

2. Store token di SharedPreferences/EncryptedStorage

3. Semua request berikutnya:
   Header: "Authorization: Bearer <token>"
```

### Token Handling
| Scenario | Action |
|----------|--------|
| 401 Unauthorized | Token expired → Redirect ke Login |
| 403 Forbidden | No access → Show permission denied |
| Token valid | Lanjutkan request |

### Role-Based Access
| Role | Access Level |
|------|-------------|
| `super_admin` | Full access, kelola tenant & user |
| `tenant_owner` | Full tenant access |
| `backoffice` | Backoffice features (reports, products) |
| `cashier` | POS only |

---

## 📦 Response Format Standard

### Success Response
```json
{
  "success": true,
  "message": "Optional success message",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": [
    { "field": "email", "message": "Email tidak valid" }
  ]
}
```

### Pagination Response
```json
{
  "success": true,
  "data": {
    "items": [ ... ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 150,
      "totalPages": 8
    }
  }
}
```

---

## ❌ Error Codes & Handling

| HTTP Code | Meaning | Android Action |
|-----------|---------|----------------|
| 200 | Success | Parse response |
| 201 | Created | Show success, refresh list |
| 400 | Bad Request | Show validation errors |
| 401 | Unauthorized | Clear token, redirect login |
| 403 | Forbidden | Show "Access Denied" |
| 404 | Not Found | Show "Data tidak ditemukan" |
| 500 | Server Error | Show "Gagal terhubung ke server" |

---

## 📱 Android Development Requirements

### Dependencies (build.gradle)
```groovy
// Networking
implementation 'com.squareup.retrofit2:retrofit:2.9.0'
implementation 'com.squareup.retrofit2:converter-gson:2.9.0'
implementation 'com.squareup.okhttp3:logging-interceptor:4.12.0'

// JWT Decode
implementation 'com.auth0.android:jwtdecode:2.0.2'
```

### Request Headers
```kotlin
// Auth Interceptor
class AuthInterceptor(private val token: String): Interceptor {
    override fun intercept(chain: Chain): Response {
        val request = chain.request().newBuilder()
            .addHeader("Authorization", "Bearer $token")
            .addHeader("Content-Type", "application/json")
            .build()
        return chain.proceed(request)
    }
}
```

### File Upload (Multipart)
```kotlin
// Max file size: 5MB
// Allowed formats: .jpg, .png, .xlsx, .pdf
// Field name: "file"

val requestBody = file.asRequestBody("image/*".toMediaType())
val part = MultipartBody.Part.createFormData("file", file.name, requestBody)
```

### Offline Support Recommendations
- Cache GET responses locally (Room Database)
- Queue POST requests when offline (WorkManager)
- Sync when connection restored

---

## 🧪 Testing dengan Postman

### Import Collection
1. Import file `postman_collection.json` (TODO: generate)
2. Set environment variable `base_url` dan `token`
3. Run collection untuk test semua endpoint

### Environment Variables
```json
{
  "base_url": "http://localhost:3000/api",
  "token": "eyJhbGciOiJIUzI1..."
}
```

---

## 📋 API Endpoints Index

| Section | Endpoints | Auth Required |
|---------|-----------|---------------|
| Authentication | /auth/* | ❌ (login), ✅ (others) |
| Products | /products/* | ✅ Tenant |
| Transactions | /transactions/* | ✅ Tenant |
| Reports | /reports/* | ✅ Tenant |
| Admin Tenants | /admin/tenants/* | 🔒 Super Admin |
| Admin Users | /admin/users/* | 🔒 Super Admin |
| Admin Analytics | /admin/analytics/* | 🔒 Super Admin |

---

## Authentication

### POST /auth/login
Login user dan dapatkan token.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI...",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "name": "John Doe",
      "role": "tenant_owner",
      "tenant_id": 1
    },
    "tenant": {
      "id": 1,
      "name": "Toko ABC",
      "code": "ABC001",
      "logo_url": "/uploads/logo.png"
    },
    "redirectPath": "/dashboard.html"
  }
}
```

### GET /auth/me
Get current user info. **Requires Token**

**Response:**
```json
{
  "success": true,
  "data": {
    "user": { "id": 1, "email": "user@example.com", "name": "John Doe", "role": "tenant_owner" },
    "tenant": { "id": 1, "name": "Toko ABC", "code": "ABC001" }
  }
}
```

### POST /auth/change-password
Change password. **Requires Token**

**Request:**
```json
{
  "currentPassword": "oldpass123",
  "newPassword": "newpass456"
}
```

---

## Products

### GET /products
Get all products with filters and pagination.

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| page | int | 1 | Page number |
| limit | int | 50 | Items per page |
| sort | string | name_asc | Sort: name_asc, name_desc, stock_asc, stock_desc, price_asc, price_desc |
| category_id | int | - | Filter by category |
| search | string | - | Search by name/SKU |
| low_stock | bool | false | Show only low stock items |

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": 1,
        "sku": "MA-2024-1",
        "name": "Indomie Goreng",
        "category_id": 2,
        "category_name": "Makanan",
        "base_unit_id": 7,
        "base_unit_name": "Pcs",
        "stock": 100,
        "min_stock": 10,
        "is_active": true,
        "units": [
          {
            "id": 1,
            "unit_id": 7,
            "unit_name": "Pcs",
            "conversion_qty": 1,
            "buy_price": 2500,
            "sell_price": 3000,
            "is_base_unit": true
          },
          {
            "id": 2,
            "unit_id": 1,
            "unit_name": "Dus",
            "conversion_qty": 40,
            "buy_price": 95000,
            "sell_price": 110000,
            "is_base_unit": false
          }
        ]
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 150,
      "totalPages": 8
    }
  }
}
```

### GET /products/:id
Get single product by ID.

### POST /products
Create new product.

**Request:**
```json
{
  "name": "Indomie Goreng",
  "category_id": 2,
  "base_unit_id": 7,
  "stock": 100,
  "min_stock": 10,
  "units": [
    {
      "unit_id": 7,
      "conversion_qty": 1,
      "buy_price": 2500,
      "sell_price": 3000,
      "is_base_unit": true
    }
  ]
}
```

### PUT /products/:id
Update product.

### DELETE /products/:id
Delete product.

### DELETE /products/delete-all
Delete all products. **Danger!**

### GET /products/units
Get all available units.

**Response:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Dus", "sort_order": 1 },
    { "id": 7, "name": "Pcs", "sort_order": 7 }
  ]
}
```

### GET /products/categories
Get all categories with product count.

**Response:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "General", "prefix": "GE", "is_active": true, "product_count": 25 },
    { "id": 2, "name": "Makanan", "prefix": "MA", "is_active": true, "product_count": 42 }
  ]
}
```

### POST /products/categories
Create new category.

**Request:**
```json
{
  "name": "Elektronik",
  "prefix": "EL"
}
```

### PUT /products/categories/:id
Update category.

### DELETE /products/categories/:id
Delete category.

### GET /products/generate-sku/:category_id
Generate SKU for category.

**Response:**
```json
{
  "success": true,
  "data": {
    "sku": "MA-2024-25",
    "prefix": "MA",
    "year": 2024,
    "number": 25
  }
}
```

### GET /products/low-stock
Get products with low stock.

### POST /products/adjust-stock/:id
Adjust stock for a specific product.

**Request:**
```json
{
  "type": "add",       // or "subtract"
  "quantity": 10,
  "reason": "Restocking"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Stock ditambah 10",
  "data": {
    "previousStock": 5,
    "newStock": 15,
    "adjustment": 10,
    "type": "add",
    "reason": "Restocking"
  }
}
```

### POST /products/reset-all-stock
Reset all product stock to 0. **Danger!**

**Response:**
```json
{
  "success": true,
  "message": "Stock for 150 products reset to 0"
}
```

---

## Transactions

### GET /transactions
Get transactions with filters.

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| page | int | Page number |
| limit | int | Items per page |
| status | string | completed, pending, cancelled |
| date_from | date | Start date (YYYY-MM-DD) |
| date_to | date | End date |
| shift_id | int | Filter by shift |

**Response:**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": 1,
        "shift_id": 1,
        "invoice_number": "INV-20241219-0001",
        "subtotal": 50000,
        "discount": 0,
        "tax": 0,
        "total_amount": 50000,
        "payment_method": "cash",
        "payment_amount": 100000,
        "change_amount": 50000,
        "status": "completed",
        "created_at": "2024-12-19T10:30:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

### GET /transactions/:id
Get transaction detail with items.

**Response:**
```json
{
  "success": true,
  "data": {
    "transaction": { ... },
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Indomie Goreng",
        "quantity": 5,
        "unit_price": 3000,
        "subtotal": 15000
      }
    ]
  }
}
```

### POST /transactions
Create new transaction.

**Request:**
```json
{
  "shift_id": 1,
  "items": [
    { "product_id": 1, "quantity": 5, "unit_price": 3000 }
  ],
  "subtotal": 15000,
  "discount": 0,
  "tax": 0,
  "total_amount": 15000,
  "payment_method": "cash",
  "payment_amount": 20000
}
```

### PATCH /transactions/:id/cancel
Cancel transaction (restore stock).

### DELETE /transactions/:id ✅ NEW (10-Jan-2026)
Delete a transaction permanently.

**Response:**
```json
{
  "success": true,
  "message": "Transaction deleted successfully"
}
```

> ⚠️ **Warning:** This is permanent. Transaction items are also deleted via CASCADE.

---

## Shifts

### GET /transactions/shifts/current
Get current open shift for user.

### POST /transactions/shifts/open
Open new shift.

**Request:**
```json
{
  "opening_cash": 500000
}
```

### POST /transactions/shifts/:id/close
Close shift.

**Request:**
```json
{
  "closing_cash": 750000,
  "notes": "Normal shift"
}
```

---

## Reports

### GET /reports/dashboard
Get dashboard statistics.

**Response:**
```json
{
  "success": true,
  "data": {
    "today": { "orders": 25, "sales": 1500000 },
    "lowStockCount": 5,
    "activeProducts": 150,
    "weeklyTrend": [
      { "date": "2024-12-13", "total": 1200000 },
      { "date": "2024-12-14", "total": 980000 }
    ]
  }
}
```

### GET /reports/sales
Get sales report.

**Query Parameters:**
| Param | Type | Default |
|-------|------|---------|
| date_from | date | - |
| date_to | date | - |
| group_by | string | day (day/month/year) |

### GET /reports/profit-loss
Get profit and loss report.

### GET /reports/top-products
Get top selling products.

---

## Export

### GET /export/products/excel
Download products as Excel (24 columns, horizontal format).

### GET /export/products/pdf
Download products as PDF.

### GET /export/stock/excel
Download stock report as Excel with status color coding.

### GET /export/stock/pdf
Download stock report as PDF with summary.

### GET /export/template/products
Download Excel import template.

---

## Import

### POST /import/products
Import products from Excel file.

**Form Data:**
- `file`: Excel file (.xlsx)

**Response:**
```json
{
  "success": true,
  "message": "Imported 50 products",
  "data": {
    "created": 50,
    "errors": ["Row 5: JENIS 'Unknown' tidak ditemukan"]
  }
}
```

---

## Backup

### GET /backup/download
Download full database backup as JSON.

### POST /backup/restore
Restore database from JSON backup.

### GET /backup/info
Get database statistics.

---

## Admin - Tenants (Super Admin Only)

> Base URL: `/api/admin/tenants`
> Requires: `super_admin` role

### GET /admin/tenants
Get all tenants with user count.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "BKT0001",
      "name": "SAGA TOKO",
      "address": "Jl. Contoh No. 1",
      "phone": "08123456789",
      "status": "active",
      "database_name": "saga_tenant_bkt0001",
      "user_count": 5
    }
  ]
}
```

### GET /admin/tenants/:id
Get tenant by ID with users list.

### POST /admin/tenants
Create new tenant with database and owner user.

**Request:**
```json
{
  "name": "Toko XYZ",
  "code": "XYZ001",
  "address": "Jl. Contoh No. 1",
  "phone": "08123456789",
  "ownerEmail": "owner@xyz.com",
  "ownerName": "John Doe",
  "ownerPassword": "password123"
}
```

### PUT /admin/tenants/:id
Update tenant (name, address, phone).

**Request:**
```json
{
  "name": "Updated Name",
  "address": "New Address",
  "phone": "08111222333"
}
```

### PATCH /admin/tenants/:id/status
Change tenant status.

**Request:**
```json
{
  "status": "suspended"
}
```
> Status: `active`, `suspended`, `inactive`

### POST /admin/tenants/:id/reset-password
Reset tenant owner password.

**Request:**
```json
{
  "newPassword": "newpassword123"
}
```

### DELETE /admin/tenants/:id
Soft delete tenant (set status to inactive).

---

## Admin - Users (Super Admin Only)

> Base URL: `/api/admin/users`
> Requires: `super_admin` role

### GET /admin/users
Get all users.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "email": "user@example.com",
      "name": "John Doe",
      "role": "tenant_owner",
      "tenant_id": 1,
      "is_active": true
    }
  ]
}
```

### POST /admin/users
Create new user.

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "cashier",
  "tenant_id": 1
}
```
> Role: `super_admin`, `tenant_owner`, `backoffice`, `cashier`

### PUT /admin/users/:id
Update user.

**Request:**
```json
{
  "name": "Updated Name",
  "email": "newemail@example.com",
  "role": "backoffice",
  "tenant_id": 2,
  "is_active": false
}
```

### DELETE /admin/users/:id
Delete user.

---

## Admin - Analytics (Super Admin Only)

> Base URL: `/api/admin/analytics`
> Requires: `super_admin` role

### GET /admin/analytics/overview
Get global statistics.

**Response:**
```json
{
  "success": true,
  "data": {
    "tenants": { "total": 5, "active": 4 },
    "users": { "total": 25 }
  }
}
```

### GET /admin/analytics/revenue
Get combined revenue from all tenants.

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| period | string | week | `week`, `month`, `year` |

**Response:**
```json
{
  "success": true,
  "data": {
    "totalRevenue": 15000000,
    "totalTransactions": 250,
    "revenueByTenant": [
      {
        "tenant_id": 1,
        "tenant_name": "SAGA TOKO",
        "revenue": 10000000,
        "transactions": 150
      }
    ],
    "dailyRevenue": [
      { "date": "2024-12-20", "amount": 500000 }
    ],
    "topProducts": [
      { "name": "Indomie Goreng", "quantity": 100 },
      { "name": "Aqua 600ml", "quantity": 80 }
    ],
    "period": "week"
  }
}
```

### GET /admin/analytics/tenants-map
Get tenant locations for map display.

**Response:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "SAGA TOKO", "code": "BKT0001", "address": "Jl. Contoh", "status": "active" }
  ]
}
```

---

## Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": [
    { "field": "email", "message": "Valid email is required" }
  ]
}
```

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 500 | Server Error |

