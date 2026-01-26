# STANDARD OPERATING PROCEDURE (SOP) - SETELAH PULL
Setiap kali Anda menjalankan `git pull origin main` di server/lokal, jalankan perintah berikut secara berurutan agar aplikasi tidak error.

### 1. Update Library (Jika ada penambahan plugin)
```bash
composer install --no-dev --optimize-autoloader
```

### 2. Update Database (Jika ada tabel baru)
```bash
php artisan migrate --force
```

### 3. Bersihkan Cache (WAJIB dilakukan setiap update)
Agar Laravel membaca route dan config terbaru.
```bash
php artisan optimize:clear
```
*(Atau di server production bisa pakai `php artisan config:cache && php artisan route:cache` agar lebih cepat)*

### 4. Build Frontend (Opsional, jika ada perubahan CSS/JS)
Hanya jika server Anda mendukung Node.js, jika tidak, build harus dilakukan di lokal lalu di-push.
```bash
npm run build
```

---

### RINGKASAN (Copy-Paste Semua)
Anda bisa langsung copy semua baris ini ke terminal untuk menjalankannya sekaligus:

```bash
composer install --no-dev
php artisan migrate --force
php artisan optimize:clear
```
