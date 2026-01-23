# ✅ DEPLOYMENT CHECKLIST - HOSTINGER

**Status: SIAP DEPLOY**  
**Tanggal**: 23 Januari 2026  
**Aplikasi**: Laravel Saga Dashboard

---

## 1️⃣ TEKNOLOGI APLIKASI

### Backend
- ✅ **Framework**: Laravel 12.26.4 (Latest)
- ✅ **PHP Version**: ^8.2
- ✅ **Package Manager**: Composer
- ✅ **Server**: Built-in PHP server atau Apache/Nginx

### Frontend
- ✅ **HTML/CSS/JavaScript**: Native (tidak menggunakan Node.js server di production)
- ✅ **Build Tool**: Vite v7.1.3 (hanya untuk development/build, bukan server)
- ✅ **CSS Framework**: Tailwind CSS v4.1.12
- ✅ **Assets Build**: Production-ready (ada di `/public/build/`)
- ℹ️ **Node.js**: Hanya untuk development (tidak diperlukan di production)

### Database
- ✅ **Database**: MySQL / MariaDB
- ✅ **Configuration**:
  - Host: 127.0.0.1
  - Port: 3306
  - Database: db_laravelsaga
  - User: root (bisa disesuaikan di production)

---

## 2️⃣ STRUKTUR PROJECT - LARAVEL STANDAR

```
laravelsaga/                 ← Root folder aplikasi
├── artisan                  ✅ Ada
├── composer.json            ✅ Ada
├── composer.lock            ✅ Ada
├── package.json             ✅ Ada
├── .env.example             ✅ Ada (untuk referensi)
├── app/                     ✅ Folder aplikasi
├── bootstrap/               ✅ Bootstrap files
├── config/                  ✅ Configuration files
├── database/                ✅ Database migrations & seeders
├── public/                  ✅ Folder public (root dari web server)
│   ├── index.php            ✅ Entry point aplikasi
│   ├── build/               ✅ Production assets (CSS/JS compiled)
│   └── images/              ✅ Static images
├── resources/               ✅ Development resources (views, CSS, JS)
├── routes/                  ✅ Route definitions
├── storage/                 ✅ Logs, cache, sessions
└── vendor/                  ✅ Composer dependencies (di local, jangan upload)
```

---

## 3️⃣ FILE PENTING UNTUK PRODUCTION

### Harus Upload
- ✅ Semua file aplikasi (app/, config/, resources/, routes/, dll)
- ✅ Folder `public/` (public_html di Hostinger)
- ✅ `.env` (dengan konfigurasi production)
- ✅ `artisan`
- ✅ `composer.json` dan `composer.lock`
- ✅ `bootstrap/`
- ✅ `database/migrations/`

### JANGAN Upload
- ❌ `node_modules/` (akan di-install di local saat build)
- ❌ `vendor/` (akan di-install via composer install --no-dev)
- ❌ `.git/` (repository history)
- ❌ `storage/logs/*` (logs dari production)
- ❌ `storage/framework/cache/*`

---

## 4️⃣ PRODUCTION REQUIREMENTS

### Hostinger Server Harus Memiliki
- ✅ **PHP 8.2+** (check versi Hostinger)
- ✅ **Composer** (di server Hostinger)
- ✅ **MySQL/MariaDB** (database)
- ✅ **PHP Extensions**: 
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - JSON
  - BCMath
  - Ctype
  - Fileinfo

### Server Configuration
- ✅ **Document Root**: Tunjuk ke `/public` folder
- ✅ **URL Rewrite**: Enable mod_rewrite (Apache) atau equiv (Nginx)
- ✅ **File Permissions**: 
  - `storage/` → 775
  - `bootstrap/cache/` → 775
- ✅ **APP_KEY**: Sudah generate ✅

---

## 5️⃣ LANGKAH DEPLOYMENT KE HOSTINGER

### Step 1: Upload Files via FTP/SSH
```bash
# Upload semua folder/file KECUALI:
# - node_modules/
# - vendor/
# - .git/
# - storage/logs/*
```

### Step 2: Setup di Hostinger
```bash
# SSH ke server Hostinger
cd public_html

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

### Step 3: Konfigurasi .env Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
DB_CONNECTION=mysql
DB_HOST=localhost (atau hostname dari Hostinger)
DB_DATABASE=nama_database
DB_USERNAME=username_db
DB_PASSWORD=password_db
```

### Step 4: File Permissions (Final)
```bash
chmod 644 .env
chmod 755 public/
chmod 755 bootstrap/
```

---

## 6️⃣ PRODUCTION BUILD ASSETS

✅ **Status**: Sudah ready  
- Production CSS/JS sudah di-compile di `/public/build/`
- Manifest file ready: `/public/build/manifest.json`
- Tidak perlu Vite/npm di production

---

## 7️⃣ DEPENDENCIES CHECK

### PHP Dependencies (Composer)
```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "laravel/tinker": "^2.10.1"
  }
}
```
✅ All good

### Node Dependencies (Hanya di Development)
```json
{
  "scripts": {
    "build": "vite build",
    "dev": "vite"
  },
  "devDependencies": {
    "vite": "^7.0.4",
    "@tailwindcss/vite": "^4.1.12",
    "laravel-vite-plugin": "^2.0.0"
  }
}
```
✅ Hanya untuk development, tidak perlu di production

---

## 8️⃣ DATABASE REQUIREMENTS

✅ MySQL/MariaDB harus support:
- InnoDB engine
- UTF-8mb4 charset
- Foreign keys

Default migration files sudah ready:
- `create_users_table.php` ✅
- `create_cache_table.php` ✅
- `create_jobs_table.php` ✅

---

## 9️⃣ SECURITY CHECKLIST

Sebelum production:
- [ ] Set `APP_DEBUG=false` di `.env`
- [ ] Set `APP_ENV=production` di `.env`
- [ ] Update `APP_URL` dengan domain production
- [ ] Generate secure `APP_KEY` (sudah done ✅)
- [ ] Update database credentials
- [ ] Set proper file permissions (755 untuk folder, 644 untuk file)
- [ ] Enable HTTPS di domain

---

## 🔟 FINAL NOTES

**APLIKASI INI SUDAH SIAP DEPLOY KE HOSTINGER ✅**

Teknologi dan struktur sesuai dengan requirements Hostinger:
1. Backend Laravel ✅
2. Frontend pure HTML/CSS/JS (bukan Node.js server) ✅
3. Database MySQL ✅
4. Struktur project standar Laravel ✅
5. Production assets sudah di-build ✅

---

**Kontak Developer Hostinger jika ada pertanyaan tentang:**
- PHP version yang tersedia
- MySQL/MariaDB access
- SSH/FTP access untuk upload
- Server-side configuration (htaccess, nginx config)
