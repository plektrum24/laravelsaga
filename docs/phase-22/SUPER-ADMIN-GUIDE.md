# Super Admin - Administration Guide

**SAGA POS - SaaS Management Platform**  
**Version:** 1.0.0  
**Last Updated:** 2026-02-22

---

## Table of Contents

1. [Accessing Super Admin Panel](#accessing-super-admin-panel)
2. [Dashboard Overview](#dashboard-overview)
3. [Tenant Management](#tenant-management)
4. [Subscription Plans](#subscription-plans)
5. [Invoice Management](#invoice-management)
6. [Support Ticket Management](#support-ticket-management)
7. [System Settings](#system-settings)
8. [Scheduled Tasks](#scheduled-tasks)
9. [Troubleshooting](#troubleshooting)

---

## Accessing Super Admin Panel

### Prerequisites

- User account with `super_admin` role
- Valid authentication token

### Access Methods

**Web Interface:**
```
https://your-domain.com/super-admin/dashboard
```

**API Endpoints:**
```
https://your-domain.com/api/admin/*
```

### Creating a Super Admin User

**Via Database:**
```sql
INSERT INTO users (name, email, password, role, created_at, updated_at)
VALUES ('Super Admin', 'admin@sagaposo.com', '$2y$10$...', 'super_admin', NOW(), NOW());
```

**Via Tinker:**
```bash
php artisan tinker
```
```php
\App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@sagaposo.com',
    'password' => bcrypt('your-password'),
    'role' => 'super_admin'
]);
```

---

## Dashboard Overview

### URL: `/super-admin/dashboard`

### Key Metrics

#### Tenant Statistics
- **Total Tenants:** All registered tenants
- **Active Tenants:** Currently paying customers
- **Suspended Tenants:** Temporarily disabled accounts
- **Growth Rate:** Month-over-month growth percentage

#### Revenue Metrics
- **Monthly Revenue:** Current month revenue
- **Total Revenue:** All-time revenue
- **Revenue Trend:** 6-month revenue graph
- **Plan Distribution:** Pie chart of plan types

#### Invoice Metrics
- **Unpaid Invoices:** Pending payment count
- **Overdue Invoices:** Past due count
- **Collection Rate:** Percentage collected

#### Support Metrics
- **Open Tickets:** Unresolved tickets
- **Urgent Tickets:** High-priority tickets
- **Avg Response Time:** Support performance

### Dashboard Actions

**Quick Links:**
- View all tenants
- Generate invoices
- Process recurring payments
- View support tickets

**Data Refresh:**
- Auto-refresh every 5 minutes
- Manual refresh button available
- Real-time updates for critical metrics

---

## Tenant Management

### URL: `/super-admin/tenants`

### Viewing Tenants

**List View Features:**
- Search by name, email, business name
- Filter by status (trial, active, suspended)
- Sort by created date, name, expiry
- Pagination (15/30/50 per page)

**Tenant Information Displayed:**
- Tenant name and email
- Current plan
- Subscription status
- Expiry date
- Created date

### Tenant Detail View

**URL:** `/super-admin/tenants/{id}`

**Information Available:**
- Complete tenant profile
- Subscription details
- Usage statistics
- Invoice history
- Support tickets
- Activity log

### Tenant Actions

#### Update Status

**Available Statuses:**
- **Trial:** Free trial period
- **Active:** Paying customer
- **Suspended:** Temporarily disabled
- **Cancelled:** Cancelled by tenant
- **Expired:** Trial/payment expired

**How to Update:**
1. Find tenant in list
2. Click **Status** button
3. Select new status
4. Add reason (optional)
5. Click **Update**

**Status Change Effects:**

| From → To | Effect |
|-----------|--------|
| Trial → Active | Start billing, full access |
| Active → Suspended | Disable access, retain data |
| Suspended → Active | Restore access |
| Any → Cancelled | Schedule deletion |

#### Extend Subscription

**Use Cases:**
- Goodwill gesture
- Compensation for issues
- Trial extension
- Pre-paid custom periods

**How to Extend:**
1. Find tenant in list
2. Click **Extend** button
3. Enter number of days (1-365)
4. Click **Extend**
5. Confirmation shown with new expiry date

#### Delete Tenant

**Warning:** This action is irreversible.

**Pre-deletion Checklist:**
- [ ] Subscription is cancelled or expired
- [ ] All invoices are paid
- [ ] Data export completed (if needed)
- [ ] Tenant notified (if applicable)

**How to Delete:**
1. Go to tenant detail page
2. Click **Delete** button
3. Type "DELETE" to confirm
4. Click **Confirm Delete**

**What Gets Deleted:**
- Tenant record
- All users associated
- All data in tenant database
- Associated invoices (soft delete)

**What Remains:**
- Invoice records for accounting
- Support ticket history
- Audit logs

### Tenant Onboarding

**Manual Tenant Creation:**

1. Go to **Tenants** → **Create Tenant**
2. Fill in required information:
   - Tenant name
   - Business name
   - Email
   - Phone
   - Address
3. Select initial plan
4. Set trial period (if applicable)
5. Click **Create Tenant**

**Automatic Tenant Creation:**
- Self-service signup
- Trial automatically assigned
- Welcome email sent
- Onboarding checklist provided

---

## Subscription Plans

### URL: `/super-admin/plans`

### Viewing Plans

**Plan Information:**
- Plan name and code
- Monthly and yearly pricing
- Features included
- Usage limits
- Trial days
- Priority (display order)

### Creating a Plan

**Step 1:** Click **Create Plan**

**Step 2:** Fill in plan details:

**Basic Information:**
- **Name:** Display name (e.g., "Professional")
- **Code:** Unique identifier (e.g., "pro")
- **Priority:** Display order (1 = first)

**Pricing:**
- **Monthly Price:** Rp per month
- **Yearly Price:** Rp per year (suggest 17% discount)
- **Trial Days:** Free trial period (0 = no trial)

**Features:**
```json
[
  "pos_access",
  "advanced_inventory",
  "advanced_reports",
  "loyalty_program",
  "barcode_generation",
  "label_printing",
  "stock_transfer",
  "e_commerce",
  "mobile_app",
  "priority_support"
]
```

**Limits:**
```json
{
  "users": 50,
  "products": 10000,
  "branches": 10,
  "transactions_per_month": 50000,
  "storage_mb": 10240
}
```

**Step 3:** Click **Save Plan**

### Editing a Plan

**Important:** Changes to existing plans do NOT affect current subscribers.

**What You Can Change:**
- Plan name
- Pricing (for new subscribers)
- Features list
- Limits
- Trial days
- Active status

**What You Cannot Change:**
- Plan code (creates new plan instead)
- Existing subscriber pricing

**How to Edit:**
1. Find plan in list
2. Click **Edit** button
3. Modify fields
4. Click **Update Plan**

### Deactivating a Plan

**Effects:**
- Plan hidden from signup
- Current subscribers unaffected
- Cannot create new subscriptions

**How to Deactivate:**
1. Go to plan detail
2. Toggle **Is Active** to OFF
3. Click **Save**

### Plan Migration

**Migrating Tenants to New Plan:**

1. Create new plan
2. Go to tenant list
3. Select tenants to migrate
4. Bulk action → Change Plan
5. Select new plan
6. Confirm migration

**Migration Considerations:**
- Prorated charges apply
- New limits take effect immediately
- Features may change
- Notify tenants before migration

---

## Invoice Management

### URL: `/super-admin/invoices`

### Viewing Invoices

**Filter Options:**
- Status (draft, sent, paid, overdue, cancelled)
- Tenant
- Date range
- Payment method

**Invoice Information:**
- Invoice number
- Tenant name
- Amount and total
- Status
- Due date
- Payment date

### Invoice Actions

#### Download PDF

1. Find invoice in list
2. Click **Download PDF** icon
3. PDF opens/downloads
4. Send to tenant if needed

#### Mark as Paid

**Use Cases:**
- Manual bank transfer received
- Check payment
- Cash payment
- Payment gateway error

**How to Mark Paid:**
1. Find invoice
2. Click **Mark Paid** button
3. Select payment method
4. Add transaction ID (optional)
5. Click **Confirm**

**Effects:**
- Invoice status → paid
- Subscription reactivated (if suspended)
- PDF regenerated with PAID stamp
- Receipt email sent

#### Cancel Invoice

**Use Cases:**
- Invoice created in error
- Tenant exempted from payment
- Credit applied

**How to Cancel:**
1. Find invoice
2. Click **Cancel** button
3. Add cancellation reason
4. Click **Confirm**

**Note:** Cannot cancel paid invoices.

#### Generate Recurring Invoices

**Manual Generation:**

1. Go to **Invoices** page
2. Click **Generate Recurring** button
3. System processes expiring subscriptions
4. Invoices created for renewals in next 7 days

**What Gets Generated:**
- Renewal invoices for active subscriptions
- Amount based on plan and billing cycle
- Due date: 14 days from creation
- PDF automatically generated

**Automatic Generation:**
- Runs daily at 02:00
- Command: `php artisan saas:process-recurring`

### Invoice Statistics

**View at:** `/super-admin/invoices/stats`

**Metrics Available:**
- Total invoices
- Paid total
- Pending total
- Overdue total
- Monthly revenue
- Collection rate

### Dunning Management

**For Overdue Invoices:**

**Day 1-7 (Grace Period):**
- Email reminders
- No service interruption

**Day 8-30:**
- Daily late fees (0.1%/day)
- Service suspension warning

**Day 31+:**
- Service suspended
- Data retention period starts

**Day 91+:**
- Account termination
- Data deletion process

---

## Support Ticket Management

### URL: `/super-admin/tickets`

### Viewing Tickets

**Filter Options:**
- Status (open, in_progress, waiting_customer, resolved, closed)
- Priority (low, medium, high, urgent)
- Category (technical, billing, feature, general)
- Assigned to me
- Search by subject or tenant

### Ticket Assignment

**Auto-Assignment:**
- Enabled in system settings
- Round-robin distribution
- Based on support team availability

**Manual Assignment:**

1. Find unassigned ticket
2. Click **Assign** button
3. Select support agent
4. Click **Assign**

**Assignment Notifications:**
- Email to assigned agent
- Dashboard notification
- Mobile push (if enabled)

### Responding to Tickets

**How to Reply:**

1. Open ticket detail view
2. Read ticket and history
3. Type response in reply box
4. Click **Send Reply**

**Response Best Practices:**
- Acknowledge the issue
- Provide clear solution steps
- Set expectations for resolution
- Use professional tone
- Include relevant links/documentation

### Changing Ticket Status

**Status Workflow:**

```
Open → In Progress → Waiting Customer → Resolved → Closed
```

**When to Use Each Status:**

| Status | When to Set |
|--------|-------------|
| **Open** | New ticket, not yet reviewed |
| **In Progress** | Agent is working on it |
| **Waiting Customer** | Need more information from customer |
| **Resolved** | Issue fixed, awaiting confirmation |
| **Closed** | Customer confirmed resolution |

### Resolving a Ticket

**How to Resolve:**

1. Ensure issue is fixed
2. Document the solution
3. Click **Resolve** button
4. Add resolution notes
5. Click **Confirm**

**Resolution Email:**
- Sent to customer
- Includes solution summary
- Request for confirmation
- Link to reopen if needed

### Ticket Metrics

**View at:** `/super-admin/tickets/stats`

**Metrics Available:**
- Total tickets
- Open tickets
- Resolved tickets
- Average resolution time
- Tickets by category
- Agent performance

### SLA Management

**Response Time Targets:**

| Priority | Target | Breach Action |
|----------|--------|---------------|
| Low | 72 hours | Escalate to senior |
| Medium | 48 hours | Notify team lead |
| High | 24 hours | Manager notification |
| Urgent | 4 hours | Immediate escalation |

**SLA Monitoring:**
- Dashboard shows approaching breaches
- Email alerts 2 hours before breach
- Daily SLA compliance report

---

## System Settings

### URL: `/super-admin/settings`

### SaaS Configuration

**General Settings:**
- Platform name
- Support email
- Support phone
- Timezone
- Currency

**Signup Settings:**
- Allow self-signup: Yes/No
- Default trial days: 14
- Require email verification: Yes/No
- Auto-approve tenants: Yes/No

### Billing Settings

**Invoice Settings:**
- Default tax rate: 0%
- Invoice due days: 14
- Late fee percentage: 0.1%/day
- Grace period: 7 days

**Payment Settings:**
- Auto-retry failed payments: Yes/No
- Retry schedule: [1, 3, 7, 14] days
- Payment gateway: Midtrans
- Sandbox mode: Yes/No

### Notification Settings

**Email Notifications:**
- Invoice sent: Enabled
- Payment received: Enabled
- Subscription expiring: Enabled (7, 3, 1 days)
- Overdue notice: Enabled
- Ticket updates: Enabled

**SMS Notifications:**
- Payment reminders: Disabled
- Urgent alerts: Enabled

### Usage Tracking

**Tracked Metrics:**
- Users: Enabled
- Products: Enabled
- Branches: Enabled
- Storage: Enabled
- API calls: Disabled

**Enforcement:**
- Soft limits: Warn at 80%
- Hard limits: Block at 100%
- Overage fees: Disabled

---

## Scheduled Tasks

### Overview

Scheduled tasks automate routine operations.

### Task Schedule

| Task | Schedule | Command |
|------|----------|---------|
| Process Recurring | Daily 02:00 | `saas:process-recurring` |
| Check Overdue | Daily 03:00 | `saas:check-overdue` |
| Monthly Renewal | 1st of month 01:00 | `saas:process-recurring` |

### Process Recurring Payments

**What It Does:**
1. Renew expiring subscriptions
2. Expire completed trials
3. Generate renewal invoices (7 days before expiry)
4. Mark overdue invoices
5. Send expiry reminders (7, 3, 1 days)

**Manual Execution:**
```bash
php artisan saas:process-recurring
```

**Expected Output:**
```
Starting recurring payment processing...
Processing expiring subscriptions...
✓ Renewed 15 subscriptions
Processing expired trials...
✓ Expired 3 trials
Generating renewal invoices...
✓ Generated 25 invoices
Checking for overdue invoices...
✓ Marked 5 invoices as overdue
Sending expiry reminders...
✓ Sent 12 reminders
Recurring payment processing completed!
```

### Check Overdue Invoices

**What It Does:**
1. Find invoices past due date
2. Mark as overdue
3. Calculate late fees
4. Log for notification

**Manual Execution:**
```bash
php artisan saas:check-overdue
```

### Setting Up Cron

**Add to server crontab:**
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

**Verify cron is running:**
```bash
crontab -l
```

**Test schedule:**
```bash
php artisan schedule:test
```

---

## Troubleshooting

### Common Issues

#### Tenant Cannot Access Portal

**Symptoms:**
- 403 Forbidden error
- "Unauthorized" message

**Solutions:**
1. Verify tenant status is active or trial
2. Check user has valid authentication token
3. Ensure tenant database is accessible
4. Verify middleware configuration

#### Payment Not Processing

**Symptoms:**
- Midtrans error
- Payment fails silently

**Solutions:**
1. Check Midtrans API keys are correct
2. Verify sandbox/production mode
3. Check invoice total is within limits
4. Review Midtrans dashboard for errors
5. Test with sandbox credentials

#### Invoices Not Generating

**Symptoms:**
- No renewal invoices created
- Manual generation fails

**Solutions:**
1. Run command manually: `php artisan saas:process-recurring`
2. Check subscription expiry dates
3. Verify InvoiceService is working
4. Check error logs for exceptions

#### Scheduled Tasks Not Running

**Symptoms:**
- No automatic renewals
- Overdue invoices not marked

**Solutions:**
1. Verify cron job is configured
2. Check cron is running: `ps aux | grep cron`
3. Test schedule: `php artisan schedule:test`
4. Review Laravel logs for errors

#### PDF Generation Fails

**Symptoms:**
- 500 error when downloading PDF
- Blank PDF

**Solutions:**
1. Check DomPDF is installed
2. Verify storage permissions
3. Check memory limit (increase if needed)
4. Clear cache: `php artisan cache:clear`
5. Regenerate PDF: `php artisan saas:regenerate-pdfs`

### Debugging Commands

```bash
# View recent errors
tail -f storage/logs/laravel.log

# Check scheduled tasks
php artisan schedule:list

# Test invoice generation
php artisan saas:process-recurring --verbose

# Verify routes
php artisan route:list --path=admin

# Check tenant status
php artisan tinker
>>> \App\Models\Tenant::find(1)->subscription

# Test PDF generation
php artisan tinker
>>> $invoice = \App\Models\Invoice::find(1);
>>> app(\App\Services\InvoiceService::class)->generatePdf($invoice);
```

### Getting Help

**Internal Resources:**
- API Documentation: `/docs/api`
- Error Logs: `storage/logs/laravel.log`
- Database: MySQL workbench

**External Resources:**
- Laravel Documentation: https://laravel.com/docs
- DomPDF Documentation: https://github.com/dompdf/dompdf
- Midtrans Documentation: https://docs.midtrans.com

**Contact:**
- Technical Lead: [contact info]
- System Admin: [contact info]

---

*Admin Guide v1.0.0 - SAGA POS Super Admin Panel*
