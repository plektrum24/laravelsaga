# Phase 22: Deployment Guide

**SAGA POS - SaaS Management Platform**  
**Version:** 1.0.0  
**Date:** 2026-02-22

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Environment Setup](#environment-setup)
3. [Installation Steps](#installation-steps)
4. [Configuration](#configuration)
5. [Database Migration](#database-migration)
6. [Testing](#testing)
7. [Go-Live Checklist](#go-live-checklist)
8. [Post-Deployment](#post-deployment)
9. [Monitoring](#monitoring)
10. [Rollback Procedure](#rollback-procedure)

---

## Prerequisites

### Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 8.2 | 8.3 |
| MySQL | 8.0 | 8.0+ |
| RAM | 4 GB | 8 GB |
| Storage | 20 GB | 50 GB+ |
| PHP Extensions | mbstring, pdo, openssl | +redis, +imagick |

### Required Services

- **Web Server:** Nginx or Apache
- **Database:** MySQL 8.0+
- **Cache:** Redis (optional but recommended)
- **Queue:** Redis or database
- **Cron:** For scheduled tasks

### External Services

- **Payment Gateway:** Midtrans account
- **Email Service:** SMTP or API (Mailgun, SendGrid)
- **SSL Certificate:** Let's Encrypt or commercial

---

## Environment Setup

### Step 1: Clone Repository

```bash
cd /var/www
git clone https://github.com/your-org/laravelsaga.git
cd laravelsaga
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install JavaScript dependencies
npm install

# Build frontend assets
npm run build
```

### Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

---

## Configuration

### Edit .env File

```bash
nano .env
```

### Application Settings

```env
APP_NAME="SAGA POS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saga_pos
DB_USERNAME=saga_user
DB_PASSWORD=your_secure_password
```

### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@sagaposo.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Midtrans Configuration

```env
# Sandbox (Testing)
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false

# Production (use these when going live)
# MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxx
# MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxx
# MIDTRANS_IS_PRODUCTION=true
```

### Queue Configuration

```env
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Session & Cache

```env
SESSION_DRIVER=redis
SESSION_LIFETIME=120

CACHE_DRIVER=redis
```

---

## Database Migration

### Step 1: Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE saga_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'saga_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON saga_pos.* TO 'saga_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 2: Run Migrations

```bash
php artisan migrate --force
```

### Step 3: Seed Initial Data

```bash
# Seed subscription plans
php artisan db:seed --class=SubscriptionPlansSeeder

# Seed system settings
php artisan db:seed --class=SystemSettingsSeeder

# Seed default super admin (optional)
php artisan db:seed --class=UserSeeder
```

### Step 4: Verify Migrations

```bash
php artisan tinker
```

```php
// Check subscription plans
\App\Models\SubscriptionPlan::count();
// Should return: 4

// Check system settings
\App\Models\SystemSetting::count();
// Should return: 20+
```

---

## Storage & Permissions

### Create Storage Links

```bash
php artisan storage:link
```

### Set Permissions

```bash
# Set ownership
chown -R www-data:www-data /var/www/laravelsaga

# Set directory permissions
chmod -R 755 /var/www/laravelsaga

# Set storage permissions
chmod -R 775 /var/www/laravelsaga/storage
chmod -R 775 /var/www/laravelsaga/bootstrap/cache
```

---

## Web Server Configuration

### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/laravelsaga/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "no-referrer-when-downgrade";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # File upload size
    client_max_body_size 50M;
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

### Apache Configuration

```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/laravelsaga/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/your-domain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/your-domain.com/privkey.pem

    <Directory /var/www/laravelsaga/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

---

## Queue & Scheduler Setup

### Configure Supervisor

Install supervisor:
```bash
apt-get install supervisor
```

Create configuration file:
```bash
nano /etc/supervisor/conf.d/laravel-worker.conf
```

Add configuration:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravelsaga/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laravelsaga/storage/logs/worker.log
stopwaitsecs=3600
```

Start workers:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start laravel-worker:*
```

### Configure Cron

Edit crontab:
```bash
crontab -e
```

Add Laravel scheduler:
```bash
* * * * * cd /var/www/laravelsaga && php artisan schedule:run >> /dev/null 2>&1
```

Verify scheduled tasks:
```bash
php artisan schedule:list
```

Expected output:
```
0 2 * * * php artisan saas:process-recurring ....... Daily at 02:00
0 3 * * * php artisan saas:check-overdue ....... Daily at 03:00
0 1 1 * * php artisan saas:process-recurring ....... Monthly on 1st at 01:00
```

---

## Testing

### Pre-Deployment Testing

```bash
# Run tests
php artisan test

# Check configuration
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verify routes
php artisan route:list --path=admin
php artisan route:list --path=tenant
```

### Functional Testing Checklist

**Super Admin:**
- [ ] Login as super admin
- [ ] Access dashboard
- [ ] View tenant list
- [ ] Create subscription plan
- [ ] View invoices
- [ ] View support tickets

**Tenant Portal:**
- [ ] Register new tenant
- [ ] Login to tenant portal
- [ ] View subscription
- [ ] Change plan
- [ ] View invoices
- [ ] Download invoice PDF
- [ ] Create support ticket

**Payment Flow:**
- [ ] Initiate payment (sandbox)
- [ ] Complete payment in Midtrans
- [ ] Verify invoice status update
- [ ] Check payment webhook

---

## Go-Live Checklist

### Technical Checklist

- [ ] All migrations run successfully
- [ ] Seeders executed
- [ ] Frontend assets built
- [ ] Storage link created
- [ ] Permissions set correctly
- [ ] SSL certificate installed
- [ ] Web server configured
- [ ] PHP-FPM configured
- [ ] Queue workers running
- [ ] Cron job configured
- [ ] Redis running (if used)

### Configuration Checklist

- [ ] `.env` configured for production
- [ ] `APP_DEBUG=false`
- [ ] Database credentials set
- [ ] Mail settings configured
- [ ] Midtrans API keys set
- [ ] Session driver configured
- [ ] Cache driver configured

### Security Checklist

- [ ] HTTPS enabled
- [ ] Security headers set
- [ ] Directory listing disabled
- [ ] `.env` file not web-accessible
- [ ] `storage/app` not web-accessible
- [ ] Strong database passwords
- [ ] API keys secured

### Business Checklist

- [ ] Subscription plans seeded
- [ ] System settings configured
- [ ] Support email configured
- [ ] Payment gateway tested
- [ ] Email notifications tested
- [ ] Super admin user created

---

## Post-Deployment

### Clear Cache

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Verify Deployment

```bash
# Check application health
curl https://your-domain.com/up

# Test login page
curl -I https://your-domain.com/signin

# Check SSL
curl -vI https://your-domain.com
```

### Monitor Logs

```bash
# Watch error logs
tail -f storage/logs/laravel.log

# Check worker logs
tail -f storage/logs/worker.log

# Check Nginx/Apache logs
tail -f /var/log/nginx/error.log
```

### Setup Monitoring

**Application Monitoring:**
- Laravel Telescope (development)
- Sentry or Bugsnag (production)
- New Relic or DataDog (optional)

**Server Monitoring:**
- CPU usage
- Memory usage
- Disk space
- Queue size

---

## Monitoring

### Daily Checks

```bash
# Check disk space
df -h

# Check queue size
php artisan queue:count

# Check failed jobs
php artisan queue:failed

# Check application health
php artisan about
```

### Weekly Tasks

```bash
# Clear old sessions
php artisan session:flush

# Clear old cache
php artisan cache:clear

# Optimize autoloader
composer dump-autoload --optimize
```

### Monthly Tasks

```bash
# Prune old notifications
php artisan notifications:table

# Archive old data
# (custom script if needed)

# Review error logs
cat storage/logs/laravel-$(date +%Y-%m).log | grep ERROR
```

---

## Rollback Procedure

### If Deployment Fails

**Step 1: Stop Queue Workers**
```bash
supervisorctl stop laravel-worker:*
```

**Step 2: Restore Database**
```bash
# If you have a backup
mysql -u root -p saga_pos < backup-$(date +%Y%m%d).sql
```

**Step 3: Restore Code**
```bash
git checkout previous-tag
composer install --optimize-autoloader --no-dev
npm run build
```

**Step 4: Clear Cache**
```bash
php artisan optimize:clear
```

**Step 5: Restart Services**
```bash
supervisorctl start laravel-worker:*
systemctl restart php8.2-fpm
systemctl restart nginx
```

### Rollback Migrations

**Warning:** Only rollback if absolutely necessary.

```bash
# Rollback last batch
php artisan migrate:rollback

# Rollback specific number of batches
php artisan migrate:rollback --step=3

# Wipe all migrations (dangerous!)
php artisan migrate:wipe
php artisan migrate
```

---

## Support & Maintenance

### Contact Information

**Technical Lead:** [Name] - [Email]  
**System Admin:** [Name] - [Email]  
**On-Call:** [Phone Number]

### Documentation Links

- API Documentation: `/docs/api`
- User Guide: `/docs/user-guide`
- Admin Guide: `/docs/admin-guide`

### Emergency Procedures

**Site Down:**
1. Check server status
2. Check error logs
3. Restart services
4. Rollback if needed
5. Notify stakeholders

**Payment Issues:**
1. Check Midtrans status
2. Verify API keys
3. Check webhook logs
4. Contact Midtrans support

**Data Loss:**
1. Stop all writes
2. Restore from backup
3. Verify data integrity
4. Notify affected users

---

*Deployment Guide v1.0.0 - SAGA POS Phase 22*
