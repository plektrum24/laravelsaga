# SAGA POS - Manual Cache Clear Instructions

## ⚠️ PHP Tidak Ditemukan

PHP tidak terdeteksi di PATH sistem. Anda perlu **install PHP** atau **tambahkan PHP ke PATH**.

---

## ✅ Option 1: Install PHP (Recommended)

### Download PHP:
1. Kunjungi: https://windows.php.net/download/
2. Download **PHP 8.2.x (Thread Safe, x64)**
3. Extract ke `C:\php`

### Tambahkan ke PATH:
1. Buka **System Properties** → **Environment Variables**
2. Edit **Path** variable
3. Add: `C:\php`
4. Restart terminal/command prompt

### Test:
```cmd
php -v
```

---

## ✅ Option 2: Gunakan Laragon / XAMPP

Jika Anda punya **Laragon** atau **XAMPP**:

### Laragon:
```cmd
C:\laragon\bin\php\php-8.x.x\php.exe artisan route:clear
```

### XAMPP:
```cmd
C:\xampp\php\php.exe artisan route:clear
```

---

## ✅ Option 3: Manual Delete Cache Files

Jika tidak bisa install PHP, delete manual file cache:

### Delete file ini:
```
d:\Project App\laravelsaga\storage\framework\cache\*
d:\Project App\laravelsaga\storage\framework\views\*
d:\Project App\laravelsaga\bootstrap\cache\*.php
```

**Jangan delete** file `.gitignore` di folder tersebut.

---

## ✅ Option 4: Gunakan Composer Script

Jika Composer sudah terinstall:

```cmd
cd "d:\Project App\laravelsaga"
composer dump-autoload
```

---

## 📝 Setelah PHP Terinstall

Jalankan command ini untuk clear cache:

```cmd
cd "d:\Project App\laravelsaga"

# Clear semua cache
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan optimize:clear

# Test aplikasi
php artisan serve
```

Atau gunakan batch script yang sudah dibuat:
```cmd
clear-cache.bat
```

---

## 🧪 Test Halaman

Setelah cache cleared, test halaman-halaman ini:

| URL | Halaman | Status |
|-----|---------|--------|
| `/inventory` | Current Stock | Should work ✅ |
| `/inventory/receiving` | Goods In | Should work ✅ |
| `/inventory/receiving/history` | Receiving History | Should work ✅ |
| `/inventory/stock-management` | Stock Management | Should work ✅ |
| `/inventory/forecasting` | Target Forecasting | Should work ✅ |
| `/settings/loyalty` | Loyalty Program | Should work ✅ |

---

## 📞 Support

Jika masih ada masalah:
1. Check `storage/logs/laravel.log` untuk error details
2. Pastikan `.env` file sudah ada dan configured
3. Run `php artisan config:cache` setelah PHP terinstall

---

*Created: 2026-03-07*
*Phase 29 Error Fixes*
