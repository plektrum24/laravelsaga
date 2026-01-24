# Blueprint Migrasi Saga Toko: Node.js ke Laravel 11

Dokumen ini adalah panduan lengkap satu file untuk memindahkan aplikasi Saga Toko (Node.js/Express) ke **Laravel 11**.

## 1. Arsitektur Folder & Init Project
Struktur folder Node.js saat ini akan dipetakan ke standar Laravel.

### Init Project
```bash
composer create-project laravel/laravel sagatoko
cd sagatoko
composer require laravel/sanctum  # Untuk API Authentication
```

### Mapping Folder
| Node.js Path | Laravel Path | Keterangan |
| :--- | :--- | :--- |
| `backend/routes/tenant/*.js` | `routes/api.php` | API Routes |
| `backend/routes/tenant/*.js` (Logic) | `app/Http/Controllers/Api/*Controller.php` | Controller Logic |
| `backend/sql/tenant-template.sql` | `database/migrations/` | Database Schema |
| `src/*.html` | `resources/views/*.blade.php` | Frontend Views |
| `src/js/*.js` | `resources/js/` | Frontend Logic |
| `src/css/*.css` | `resources/css/` | Styling |
| `public/uploads/` | `storage/app/public/uploads` | User Uploads |

---

## 2. Database Migration (`database/migrations/`)
Buat migration file untuk setiap tabel dari `tenant-template.sql`.

### Perintah Artisan
```bash
php artisan make:migration create_master_tables
php artisan make:migration create_inventory_tables
php artisan make:migration create_transaction_tables
php artisan make:migration create_return_tables
```

### Schema Mapping (Ringkasan Penting)
Pastikan tipe data sesuai dengan decimal presisi yang ada di SQL lama.

**1. Master Data (`create_master_tables`)**
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('prefix', 5)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('units', function (Blueprint $table) {
    $table->id();
    $table->string('name', 50);
    $table->integer('sort_order');
});

Schema::create('branches', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('code', 20)->unique()->nullable();
    $table->text('address')->nullable();
    $table->boolean('is_main')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('suppliers', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('contact_person', 100)->nullable();
    $table->string('phone', 20)->nullable();
    $table->text('address')->nullable();
    $table->timestamps();
});
```

**2. Inventory (`create_inventory_tables`)**
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('sku', 50)->unique();
    $table->string('name', 255);
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('base_unit_id')->nullable()->constrained('units')->nullOnDelete();
    $table->decimal('stock', 15, 4)->default(0); // Decimal penting!
    $table->decimal('min_stock', 15, 4)->default(5);
    $table->string('image_url', 500)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('product_units', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('unit_id')->constrained();
    $table->decimal('conversion_qty', 15, 4)->default(1);
    $table->decimal('buy_price', 15, 2)->default(0);
    $table->decimal('sell_price', 15, 2)->default(0);
    $table->boolean('is_base_unit')->default(false);
    $table->timestamps();
});
```

**3. Transaksi & Returns (`create_return_tables`)**
Mapping tabel `purchase_returns` dan `purchase_return_items` yang baru dibuat.
```php
Schema::create('purchase_returns', function (Blueprint $table) {
    $table->id();
    $table->foreignId('branch_id')->nullable()->constrained('branches');
    $table->foreignId('supplier_id')->constrained();
    $table->string('return_number', 50);
    $table->date('date');
    $table->decimal('total_amount', 15, 2)->default(0);
    $table->enum('status', ['draft', 'completed', 'cancelled'])->default('draft');
    $table->enum('reason', ['expired', 'damaged', 'wrong_item', 'quality_issue', 'other'])->default('other');
    $table->text('notes')->nullable();
    $table->foreignId('created_by')->nullable(); // User ID
    $table->timestamps();
});
```

---

## 3. Business Logic Inventory (Checklist Migrasi Detail)
Bagian ini merangkum *semua* logika bisnis yang ada di backend Node.js. Developer Laravel **WAJIB** mengecek file-file ini satu per satu.

### A. Core Inventory (`ProductController`, `InventoryController`)
*   **Products (`products.js`)**:
    *   CRUD Produk Multi-Satuan (Units).
    *   Logic `product_units`: conversion qty, buy/sell price per unit.
    *   **Stock Calculation**: Total stock = Base Unit Sum.
    *   **Nearest Expiry**: Query khusus untuk mencari expiry date terdekat dari `purchase_items`.

*   **Import/Export (`import.js`, `export.js`)**:
    *   Logic validasi Excel yang kompleks.
    *   Auto-create Master Data (Category/Unit) jika nama tidak ditemukan di DB.
    *   Lazy Migration: Skema tabel otomatis update jika kolom baru (seperti `expiry_date`) dideteksi.

### B. Transaksi Penjualan (`TransactionController`)
*   **POS Transaction (`transactions.js`)**:
    *   Dua level stok: `products.stock` (Global) dan `branch_stock` (Per Cabang).
    *   FIFO Stock Deduction: Mengambil stok dari batch `purchase_items` terlama (First In First Out).
    *   **Shift System**: Transaksi harus terikat dengan `shift_id` yang aktif.
    *   Locking: Validasi stok tidak boleh minus sebelum insert.

### C. Pembelian & Supplier (`PurchaseController`)
*   **Goods In (`purchases.js`)**:
    *   Nambah stok global & branch stock.
    *   Membuat batch baru di `purchase_items` (penting untuk Expiry Date tracking).
    *   Update HPP (Harga Pokok Penjualan) atau Last Buy Price di master product.

*   **Supplier Returns (`PurchaseReturnController`)**:
    *   **Validasi Batch**: User harus memilih dari batch mana barang dikembalikan.
    *   **Stock Reversal**:
        *   Saat `completed`: Kurangi stok batch, kurangi stok global.
        *   Saat `cancelled`: Kembalikan stok ke batch asal.

### D. Keuangan & Laporan (`ReportController`)
*   **Laporan**:
    *   Sales Summary (Harian/Bulanan).
    *   Profit & Loss (Omzet - HPP).
    *   Stock Value (Nilai Aset).
    *   Shift closing report (Uang fisik vs Sistem).

### E. Fitur Pendukung
*   **Backup System (`backup.js`)**: Scheduler otomatis mysqldump per tenant.
*   **Notification (`notifications.js`)**: Stok menipis, barang expired dalam 30 hari.
*   **Multi-tenant Middleware**: Logic switching database (`USE tenant_db`) harus diubah jadi Laravel Dynamic Database Connection.

---

## 4. Route & Controller Mapping
Pindahkan logika dari `backend/routes/tenant/*.js` ke Controller Laravel.

### `routes/api.php`
Gunakan Route Groups untuk mengelompokkan API yang butuh login.
```php
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseReturnController;
// ... import lainnya

Route::middleware('auth:sanctum')->group(function () {
    // Products
    Route::apiResource('products', ProductController::class);
    
    // Purchase Returns (Fitur Baru)
    Route::get('purchase-returns/batches/{productId}', [PurchaseReturnController::class, 'getBatches']);
    Route::apiResource('purchase-returns', PurchaseReturnController::class);
    Route::patch('purchase-returns/{id}/complete', [PurchaseReturnController::class, 'complete']);
    Route::patch('purchase-returns/{id}/cancel', [PurchaseReturnController::class, 'cancel']);
    
    // Suppliers, Transactions, dll...
});
```

---

## 5. Frontend Migration (Blade & Vue/Alpine)
Aplikasi saat ini menggunakan HTML + Alpine.js. Ini **sangat mudah** dipindahkan ke Blade.

### Layout Utama (`resources/views/layouts/app.blade.php`)
Pindahkan isi `sidebar-saga.html` dan struktur dasar HTML ke sini.
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Masukkan CSS & Font di sini -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    @include('layouts.sidebar')
    
    <div class="main-content">
        @yield('content')
    </div>
</body>
</html>
```

### Halaman Return (`resources/views/supplier-returns/index.blade.php`)
Copy-paste isi `body` dari `src/supplier-returns.html` ke section content.
**PENTING**: Ubah endpoint `fetch('/api/...')` di script Alpine.js agar sesuai dengan route Laravel (biasanya sama saja `/api/...`).

Contoh adaptasi script:
```javascript
// Di Node.js
fetch('/api/purchase-returns')

// Di Laravel (Laravel otomatis proteksi CSRF untuk web, tapi untuk API pakai token Sanctum)
// Pastikan script axios/fetch menyertakan header:
headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token'),
    'Accept': 'application/json'
}
```

---

## 6. Authentication (Multi-Tenant)
Node.js saat ini pakai manual JWT. Laravel punya **Sanctum** yang jauh lebih aman.

1.  **Login**: Buat `AuthController` yang me-return `plainTextToken`.
2.  **Multi-Tenant**:
    *   Jika 1 database per tenant: Buat Middleware global yang switch koneksi database berdasarkan user login.
    *   Laravel support multiple DB connection natively di `config/database.php`.

## Checklist Migrasi
1.  [ ] Setup Laravel 11 Project.
2.  [ ] Copy `tenant-template.sql` jadi Migrations.
3.  [ ] Run `php artisan migrate`.
4.  [ ] Generate Models (Product, Transaction, dll).
5.  [ ] **Implementasi Logic Poin 3 (Inventory, Transaksi, Return, dll).**
6.  [ ] Copy Paste HTML ke `resources/views`.
7.  [ ] Pindahkan Asset (CSS/Images) ke `public`.
8.  [ ] Update endpoint frontend URL jika ada perubahan prefix.
