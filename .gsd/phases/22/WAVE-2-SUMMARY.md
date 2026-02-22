# Phase 22 - Wave 2 Summary: Payment Gateway & PDF Invoicing

**Date:** 2026-02-22
**Status:** ✅ COMPLETE
**Milestone:** v2.0 — SaaS Platform

---

## 📋 Objective

Implement payment processing, PDF invoice generation, and automated recurring billing system.

---

## ✅ Deliverables

### 1. Services (2 files)

| Service | Purpose |
|---------|---------|
| `InvoiceService.php` | PDF generation, email, recurring invoices |
| `PaymentGatewayService.php` | Midtrans integration, payment processing |

**InvoiceService Features:**
- PDF invoice generation using DomPDF
- Invoice download with caching
- Recurring invoice generation
- Overdue invoice marking
- Payment processing
- Email notifications

**PaymentGatewayService Features:**
- Midtrans subscription creation
- Payment transaction creation
- Payment status checking
- Notification/callback handling
- Payment cancellation & refunds
- Snap token generation

---

### 2. Notifications (3 files)

| Notification | Purpose |
|-------------|---------|
| `InvoiceSent.php` | Email when invoice is generated |
| `SubscriptionExpiring.php` | Reminder before subscription expires |
| `TicketStatusUpdated.php` | Support ticket status changes |

**Email Templates:**
- Invoice sent with payment link
- Subscription expiry reminders (7, 3, 1 days)
- Ticket status update notifications

---

### 3. Console Commands (2 files)

| Command | Schedule | Purpose |
|---------|----------|---------|
| `ProcessRecurringPayments` | Daily 02:00 | Process renewals, generate invoices |
| `CheckOverdueInvoices` | Daily 03:00 | Mark overdue invoices |

**Scheduled Tasks:**
```php
// Daily processing
Schedule::command('saas:process-recurring')->dailyAt('02:00');
Schedule::command('saas:check-overdue')->dailyAt('03:00');

// Monthly processing
Schedule::command('saas:process-recurring')->monthlyOn(1, '01:00');
```

**ProcessRecurringPayments does:**
1. Process expiring subscriptions (auto-renew)
2. Process expired trials
3. Generate renewal invoices (7 days before expiry)
4. Mark overdue invoices
5. Send expiry reminders (7, 3, 1 days)

---

### 4. PDF Templates (1 file)

| Template | Purpose |
|----------|---------|
| `pdfs/invoices/standard.blade.php` | Professional invoice PDF |

**PDF Features:**
- Professional invoice design
- Tenant billing information
- Subscription details
- Itemized charges
- Tax and discount support
- Status watermark (PAID, OVERDUE, etc.)
- Payment instructions
- Company branding

**PDF Layout:**
```
┌─────────────────────────────────────┐
│  SAGA POS        INVOICE #INV-xxx   │
├─────────────────────────────────────┤
│  Bill To:        Invoice Details:   │
│  Tenant Name     Date: 22 Feb 2026  │
│  email@test.com  Due: 08 Mar 2026   │
│                  Status: SENT       │
├─────────────────────────────────────┤
│  Description                    Amount
│  Pro Subscription            Rp 299,000
├─────────────────────────────────────┤
│  Subtotal:                   Rp 299,000
│  Total:                      Rp 299,000
├─────────────────────────────────────┤
│  Payment Instructions...            │
│  Thank you for your business!       │
└─────────────────────────────────────┘
```

---

### 5. Controllers (Updated/Created)

| Controller | Changes |
|------------|---------|
| `SuperAdmin/InvoiceController.php` | Added PDF download, stats service integration |
| `Tenant/InvoiceController.php` | Added PDF download, payment initiation |
| `PaymentCallbackController.php` | NEW - Midtrans callbacks, payment status |

**PaymentCallbackController Methods:**
- `midtransCallback()` - Handle payment notifications
- `finish()` - Payment redirect callback
- `status()` - Check payment status
- `initiate()` - Start payment process
- `cancel()` - Cancel pending payment

---

### 6. API Routes (10 new endpoints)

**Super Admin:**
```
GET  /admin/invoices/{invoice}/pdf         - Download invoice PDF
POST /admin/invoices/generate-recurring    - Generate renewal invoices
```

**Tenant Portal:**
```
GET  /tenant/invoices/{invoice}/pdf        - Download invoice PDF
POST /tenant/invoices/{invoice}/pay        - Initiate payment
```

**Payment Callbacks (Public):**
```
POST /payments/callback/midtrans           - Midtrans webhook
GET  /payments/callback/finish             - Payment redirect
GET  /payments/status/{invoiceNumber}      - Check payment status
POST /payments/initiate                    - Start payment (auth required)
POST /payments/cancel                      - Cancel payment (auth required)
```

**Total New Routes:** 10

---

### 7. Configuration

**Midtrans Configuration** (to be added to `config/services.php`):
```php
'midtrans' => [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
],
```

**Environment Variables** (`.env`):
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxx
MIDTRANS_IS_PRODUCTION=false
```

---

## 📊 Statistics

| Metric | Count |
|--------|-------|
| Services Created | 2 |
| Notifications Created | 3 |
| Console Commands | 2 |
| PDF Templates | 1 |
| Controllers Updated | 3 |
| API Routes Added | 10 |
| Scheduled Tasks | 3 |
| Lines of Code | ~1,500+ |

---

## 🔧 Technical Highlights

### 1. PDF Generation
- Uses DomPDF (already installed via `barryvdh/laravel-dompdf`)
- Professional invoice template
- Cached PDF storage
- Auto-regenerate on status change
- Watermark for PAID invoices

### 2. Payment Gateway Integration
- Midtrans integration (existing from Phase 19)
- Support for multiple payment methods:
  - Credit/Debit Cards
  - Bank Transfer
  - GoPay
  - ShopeePay
- Snap popup for card payments
- Webhook handling for async payments

### 3. Recurring Billing
- Automatic renewal 7 days before expiry
- Daily scheduled processing
- Invoice auto-generation
- Email notifications
- Subscription status management

### 4. Notification System
- Laravel Notifications framework
- Mail + Database channels
- Customizable email templates
- In-app notification storage

### 5. Payment Security
- Signature verification (optional)
- Transaction status verification
- Fraud status checking
- Secure callback handling

---

## ⚠️ Configuration Required

### 1. Midtrans Setup
1. Sign up at https://midtrans.com
2. Get API keys from dashboard
3. Add to `.env`:
```env
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
```

### 2. Cron Job Setup
Add to server crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Email Configuration
Configure mail settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### 4. PDF Storage
Ensure storage link exists:
```bash
php artisan storage:link
```

---

## 🧪 Testing Checklist

### PDF Generation
- [ ] Generate PDF for draft invoice
- [ ] Generate PDF for paid invoice (with watermark)
- [ ] Download PDF from API
- [ ] Verify PDF content accuracy

### Payment Processing
- [ ] Initiate payment with Midtrans
- [ ] Receive webhook callback
- [ ] Verify invoice status update
- [ ] Test payment cancellation
- [ ] Test refund processing

### Scheduled Commands
- [ ] Run `php artisan saas:process-recurring`
- [ ] Run `php artisan saas:check-overdue`
- [ ] Verify invoices generated
- [ ] Verify subscriptions renewed
- [ ] Verify reminders sent

### Notifications
- [ ] Send invoice email
- [ ] Send expiry reminder
- [ ] Send ticket update notification
- [ ] Verify email delivery

---

## 📝 Files Created/Modified

### Created (10 files):
```
app/Services/
  - InvoiceService.php
  - PaymentGatewayService.php

app/Notifications/
  - InvoiceSent.php
  - SubscriptionExpiring.php
  - TicketStatusUpdated.php

app/Console/Commands/
  - ProcessRecurringPayments.php
  - CheckOverdueInvoices.php

resources/views/pdfs/invoices/
  - standard.blade.php

app/Http/Controllers/Api/
  - PaymentCallbackController.php
```

### Modified (4 files):
```
routes/api.php - Added 10 payment routes
routes/console.php - Added scheduled commands
app/Http/Controllers/Api/SuperAdmin/InvoiceController.php
app/Http/Controllers/Api/Tenant/InvoiceController.php
```

---

## 🚀 Next Steps (Wave 3)

### Tenant Self-Service Portal UI
- [ ] Tenant dashboard with usage stats
- [ ] Subscription management page
- [ ] Plan upgrade/downgrade UI
- [ ] Invoice history page
- [ ] Payment page with Midtrans Snap
- [ ] Support ticket portal

### Files to Create:
- `resources/views/pages/tenant-portal/dashboard.blade.php`
- `resources/views/pages/tenant-portal/subscription.blade.php`
- `resources/views/pages/tenant-portal/invoices.blade.php`
- `resources/views/pages/tenant-portal/tickets.blade.php`

---

**Wave 2 Status:** ✅ COMPLETE
**Ready for:** Wave 3 Implementation (Tenant Portal UI)

---

*Phase 22 Wave 2 Summary - Generated 2026-02-22*
