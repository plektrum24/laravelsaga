# SAGA POS - Production Deployment Checklist

**Version:** 3.1.0  
**Date:** 2026-02-22  
**Status:** Ready for Production Deployment

---

## 📋 Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Backend Deployment](#backend-deployment)
3. [Mobile App Deployment](#mobile-app-deployment)
4. [Database Deployment](#database-deployment)
5. [Post-Deployment Verification](#post-deployment-verification)
6. [Launch Checklist](#launch-checklist)
7. [Rollback Plan](#rollback-plan)

---

## Pre-Deployment Checklist

### Code Quality ✅

- [ ] All TypeScript/PHP errors resolved
- [ ] No console.log in production code
- [ ] Code review completed
- [ ] Static analysis passed (ESLint, PHPStan)
- [ ] Unit tests passing (>80% coverage)
- [ ] Integration tests passing
- [ ] E2E tests passing
- [ ] No TODO/FIXME comments in code

### Security ✅

- [ ] No hardcoded secrets in code
- [ ] API keys stored in environment variables
- [ ] HTTPS enforced
- [ ] CORS configured correctly
- [ ] Rate limiting enabled
- [ ] SQL injection prevention verified
- [ ] XSS prevention verified
- [ ] CSRF protection enabled
- [ ] Authentication working correctly
- [ ] Authorization working correctly

### Performance ✅

- [ ] App size < 50MB (mobile)
- [ ] Initial load < 2 seconds
- [ ] API response < 300ms
- [ ] Image optimization completed
- [ ] Code splitting implemented
- [ ] Lazy loading implemented
- [ ] Caching configured
- [ ] Database indexes created

### Documentation ✅

- [ ] API documentation updated
- [ ] README files updated
- [ ] Deployment guide reviewed
- [ ] User documentation ready
- [ ] Admin guide ready
- [ ] FAQ document ready
- [ ] Known issues documented

### Testing ✅

- [ ] All features tested manually
- [ ] Regression testing completed
- [ ] Performance testing completed
- [ ] Security testing completed
- [ ] Usability testing completed
- [ ] Accessibility testing completed
- [ ] Cross-browser testing (web)
- [ ] Cross-device testing (mobile)

---

## Backend Deployment

### Environment Setup

- [ ] Production server provisioned
- [ ] PHP 8.2+ installed
- [ ] MySQL 8.0 installed
- [ ] Redis installed
- [ ] Nginx/Apache configured
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] SSH access configured

### Environment Variables

Create `.env` file:

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.sagaposo.com
APP_KEY=base64:your-app-key-here

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saga_pos_prod
DB_USERNAME=saga_user
DB_PASSWORD=strong-password-here

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# Midtrans
MIDTRANS_SERVER_KEY=Mid-server-prod-key-here
MIDTRANS_CLIENT_KEY=Mid-client-prod-key-here
MIDTRANS_IS_PRODUCTION=true

# Firebase
FIREBASE_PROJECT_ID=saga-pos-prod
FIREBASE_API_KEY=your-firebase-api-key

# Sentry
SENTRY_LARAVEL_DSN=https://your-sentry-dsn

# Queue
QUEUE_CONNECTION=redis

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Deployment Steps

**1. Deploy Code:**
```bash
# Navigate to project directory
cd /var/www/laravelsaga

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

**2. Run Migrations:**
```bash
# Run database migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --class=SubscriptionPlansSeeder
php artisan db:seed --class=SystemSettingsSeeder
```

**3. Clear & Cache:**
```bash
# Clear all cache
php artisan optimize:clear

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

**4. Set Permissions:**
```bash
# Set ownership
chown -R www-data:www-data /var/www/laravelsaga

# Set directory permissions
chmod -R 755 /var/www/laravelsaga

# Set storage permissions
chmod -R 775 /var/www/laravelsaga/storage
chmod -R 775 /var/www/laravelsaga/bootstrap/cache
```

**5. Configure Queue Workers:**
```bash
# Start queue workers
php artisan queue:work --daemon --sleep=3 --tries=3

# Or use supervisor for process management
# Create /etc/supervisor/conf.d/laravel-worker.conf
```

**6. Configure Cron:**
```bash
# Add to crontab
* * * * * cd /var/www/laravelsaga && php artisan schedule:run >> /dev/null 2>&1
```

**7. Restart Services:**
```bash
# Restart PHP-FPM
systemctl restart php8.2-fpm

# Restart Nginx
systemctl restart nginx

# Restart queue workers
php artisan queue:restart
```

**8. Verify Deployment:**
```bash
# Check application health
curl https://api.sagaposo.com/api/health

# Check routes
php artisan route:list --path=api

# Check configuration
php artisan config:clear
```

---

## Mobile App Deployment

### iOS App Store

**1. Build Production IPA:**
```bash
# Login to Expo
eas login

# Build for iOS App Store
eas build --platform ios --profile production

# Monitor build
eas build:list
```

**2. App Store Connect Configuration:**
- [ ] App record created
- [ ] Bundle ID: com.sagaposo.mobileapp
- [ ] App name: SAGA POS - Smart Shopping
- [ ] Subtitle: Your Complete Shopping Companion
- [ ] Category: Shopping (Primary), Lifestyle (Secondary)
- [ ] Age rating: 4+
- [ ] Price: Free

**3. Metadata:**
- [ ] Description (4000 characters)
- [ ] Keywords (100 characters)
- [ ] Support URL: https://sagaposo.com/support
- [ ] Marketing URL: https://sagaposo.com
- [ ] Privacy Policy URL: https://sagaposo.com/privacy

**4. Screenshots:**
- [ ] 6.5" Display (1284 x 2778) - 5 screenshots
- [ ] 5.5" Display (1242 x 2208) - 5 screenshots
- [ ] iPad Pro 12.9" (2048 x 2732) - 5 screenshots (optional)

**5. Submit for Review:**
```bash
# Submit via EAS
eas submit --platform ios --latest

# Or manually via Xcode
```

**6. App Review:**
- [ ] Review submitted
- [ ] Review status monitored
- [ ] Feedback addressed (if any)
- [ ] Approval received

**7. Release:**
- [ ] Phased release configured (7 days)
- [ ] Release date set
- [ ] Release notes prepared

---

### Android Google Play

**1. Build Production AAB:**
```bash
# Build for Google Play Store
eas build --platform android --profile production

# Monitor build
eas build:list
```

**2. Google Play Console Configuration:**
- [ ] App record created
- [ ] Package name: com.sagaposo.mobileapp
- [ ] App name: SAGA POS - Smart Shopping
- [ ] Category: Shopping
- [ ] Content rating: Everyone
- [ ] Price: Free

**3. Store Listing:**
- [ ] Short description (80 characters)
- [ ] Full description (4000 characters)
- [ ] App icon (512x512)
- [ ] Feature graphic (1024x500)
- [ ] Screenshots (phone: 2-8, tablet: 2-8)

**4. Content Rating:**
- [ ] Content rating questionnaire completed
- [ ] Rating certificate received

**5. Pricing & Distribution:**
- [ ] Countries selected
- [ ] Pricing set (Free)
- [ ] Google Play for Families (No)

**6. Upload Release:**
```bash
# Submit via EAS
eas submit --platform android --latest

# Or manually via Play Console
```

**7. Review & Release:**
- [ ] Review submitted
- [ ] Review status monitored (1-7 days)
- [ ] Staged rollout configured (10% → 20% → 50% → 100%)
- [ ] Release notes prepared

---

## Database Deployment

### Pre-Migration

- [ ] Database backup created
- [ ] Migration scripts reviewed
- [ ] Rollback plan prepared
- [ ] Downtime window scheduled (if needed)

### Migration Execution

```bash
# Run migrations
php artisan migrate --force

# Verify migrations
php artisan migrate:status

# Seed data
php artisan db:seed --class=SubscriptionPlansSeeder
php artisan db:seed --class=SystemSettingsSeeder
```

### Post-Migration

- [ ] All migrations ran successfully
- [ ] Data seeded correctly
- [ ] No errors in migration log
- [ ] Database performance verified

---

## Post-Deployment Verification

### Backend Verification

**API Endpoints:**
```bash
# Health check
curl https://api.sagaposo.com/api/health

# Authentication
curl -X POST https://api.sagaposo.com/api/mobile/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Products
curl https://api.sagaposo.com/api/mobile/products \
  -H "Authorization: Bearer YOUR_TOKEN"

# Cart
curl https://api.sagaposo.com/api/mobile/cart \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Checklist:**
- [ ] Health endpoint returns 200
- [ ] Login works
- [ ] Product list returns data
- [ ] Cart operations work
- [ ] Checkout flow works
- [ ] Payment initiation works
- [ ] Order creation works
- [ ] Analytics endpoints work
- [ ] Recommendation endpoints work

### Mobile App Verification

**iOS:**
- [ ] App installs from TestFlight
- [ ] App launches without crashes
- [ ] Login works
- [ ] Product browsing works
- [ ] Cart operations work
- [ ] Checkout flow works
- [ ] Payment works (sandbox)
- [ ] Push notifications work
- [ ] All features tested

**Android:**
- [ ] App installs from Play Console
- [ ] App launches without crashes
- [ ] Login works
- [ ] Product browsing works
- [ ] Cart operations work
- [ ] Checkout flow works
- [ ] Payment works (sandbox)
- [ ] Push notifications work
- [ ] All features tested

### Monitoring Setup

**Firebase:**
- [ ] Firebase project configured
- [ ] Analytics enabled
- [ ] Crashlytics enabled
- [ ] Performance monitoring enabled
- [ ] Cloud Messaging configured

**Sentry:**
- [ ] Sentry project configured
- [ ] DSN configured in app
- [ ] Error tracking working
- [ ] Performance monitoring enabled

**Backend:**
- [ ] Log aggregation configured
- [ ] Error tracking configured
- [ ] Performance monitoring configured
- [ ] Uptime monitoring configured
- [ ] Alert thresholds set

---

## Launch Checklist

### T-1 Week (Final Prep)

- [ ] All pre-deployment checks passed
- [ ] Backend deployed to production
- [ ] Mobile apps submitted to stores
- [ ] Monitoring dashboards configured
- [ ] Support team trained
- [ ] Marketing materials ready
- [ ] Press release prepared
- [ ] Launch team briefed

### Launch Day

**Morning (9 AM):**
- [ ] iOS app approved ✅
- [ ] Android app approved ✅
- [ ] Backend verified ✅
- [ ] All systems operational ✅

**Afternoon (2 PM):**
- [ ] Release apps to production
- [ ] Activate marketing campaign
- [ ] Send launch email
- [ ] Post on social media
- [ ] Monitor metrics

**Evening (6 PM):**
- [ ] Review initial metrics
- [ ] Check crash reports
- [ ] Review user feedback
- [ ] Address urgent issues
- [ ] Prepare daily report

### Week 1 Post-Launch

**Daily Tasks:**
- [ ] Review overnight crash reports
- [ ] Check app store reviews
- [ ] Review support tickets
- [ ] Check API error rates
- [ ] Verify all systems operational
- [ ] Update status dashboard
- [ ] Prepare daily report

**Metrics to Track:**
- [ ] Downloads (total & daily)
- [ ] Active users (DAU, WAU, MAU)
- [ ] Crash-free users %
- [ ] App rating
- [ ] User reviews
- [ ] API response times
- [ ] Error rates

---

## Rollback Plan

### iOS Rollback

**If Critical Issues Found:**

1. **Stop Rollout:**
```bash
# In App Store Connect
# Go to your app → Phased Release
# Click "Pause Phased Release" or "Release to Full Availability"
```

2. **Emergency Update:**
```bash
# Fix critical bugs
# Increment build number (3.0.0.2)
eas build --platform ios --profile production

# Submit emergency update
eas submit --platform ios --latest

# Request expedited review
```

3. **Communication:**
- [ ] Notify users via in-app message
- [ ] Update app store description
- [ ] Respond to negative reviews

### Android Rollback

**If Critical Issues Found:**

1. **Halt Rollout:**
```bash
# In Google Play Console
# Go to Production → Active releases
# Click "Manage rollout"
# Click "Halt rollout"
```

2. **Emergency Update:**
```bash
# Fix critical bugs
# Increment version code (30001)
eas build --platform android --profile production

# Submit emergency update
eas submit --platform android --latest
```

3. **Communication:**
- [ ] Notify users via in-app message
- [ ] Update store listing
- [ ] Respond to reviews

### Backend Rollback

**If Backend Issues:**

1. **Rollback Code:**
```bash
cd /var/www/laravelsaga

# Restore previous version
git checkout <previous-tag>

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

2. **Rollback Database:**
```bash
# Rollback last migration
php artisan migrate:rollback --step=1

# Or restore from backup
mysql -u root -p saga_pos_prod < backup-20260222.sql
```

3. **Clear Cache:**
```bash
php artisan optimize:clear
php artisan optimize
```

4. **Restart Services:**
```bash
systemctl restart php8.2-fpm
systemctl restart nginx
php artisan queue:restart
```

5. **Verify:**
```bash
curl https://api.sagaposo.com/api/health
```

---

## Emergency Contacts

### Launch Team

| Role | Name | Email | Phone |
|------|------|-------|-------|
| **Project Manager** | [Name] | pm@sagaposo.com | +62-xxx-xxxx |
| **Technical Lead** | [Name] | tech@sagaposo.com | +62-xxx-xxxx |
| **DevOps Lead** | [Name] | devops@sagaposo.com | +62-xxx-xxxx |
| **QA Lead** | [Name] | qa@sagaposo.com | +62-xxx-xxxx |
| **Support Lead** | [Name] | support@sagaposo.com | +62-xxx-xxxx |

### 24/7 On-Call (Launch Week)

- **Primary:** [Name] - [Phone]
- **Secondary:** [Name] - [Phone]
- **Escalation:** [CTO Name] - [Phone]

### Support Channels

- **Email:** support@sagaposo.com
- **Phone:** +62-xxx-xxxx-xxxx
- **Hours:** Mon-Fri 9:00-17:00 WIB
- **Emergency:** 24/7 during launch week

---

## Success Criteria

### Go/No-Go Decision

**Must Meet All Criteria:**
- [ ] Crash-free users > 95%
- [ ] No critical bugs
- [ ] App rating > 4.0 stars
- [ ] Backend stable (99.9% uptime)
- [ ] Support team ready
- [ ] All tests passing

**If Criteria Not Met:**
- ❌ Pause rollout
- ❌ Fix critical issues
- ❌ Resubmit update
- ❌ Restart rollout

---

*Deployment Checklist v3.1.0 - SAGA POS*  
**Last Updated:** 2026-02-22  
**Status:** Ready for Execution
