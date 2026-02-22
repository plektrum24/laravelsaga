# Phase 22: Multi-Tenant SaaS Management Portal

**Date:** 2026-02-22
**Status:** `FINALIZED`
**Milestone:** v2.0 — SaaS Platform
**Priority:** HIGH

---

## 📋 Vision

Transform the retail management system into a fully-featured SaaS platform with subscription management, tenant self-service, and automated billing.

---

## 🎯 Goals

### Wave 1: Super Admin Dashboard
**Objective:** Complete tenant management capabilities

**Deliverables:**
- Super admin authentication & authorization
- Tenant CRUD operations
- Tenant status management (active, suspended, trial, expired)
- System health monitoring
- Usage analytics per tenant
- Revenue tracking dashboard

**Timeline:** 3-4 days

---

### Wave 2: Subscription & Billing
**Objective:** Monetization infrastructure

**Deliverables:**
- Subscription plan management (Free, Starter, Pro, Enterprise)
- Feature-based access control
- Usage limits & quotas
- Billing integration (Midtrans)
- Invoice generation (PDF)
- Payment tracking
- Subscription lifecycle management

**Timeline:** 4-5 days

---

### Wave 3: Tenant Self-Service Portal
**Objective:** Reduce operational overhead

**Deliverables:**
- Tenant dashboard
- Plan upgrades/downgrades
- Payment method management
- Usage dashboard
- Billing history
- Support ticket system
- White-label options (logo, colors)

**Timeline:** 3-4 days

---

## 🗄️ Database Schema

### subscription_plans
```sql
- id: bigint
- name: string (Free, Starter, Pro, Enterprise)
- code: string (free, starter, pro, enterprise)
- price_monthly: decimal(12,2)
- price_yearly: decimal(12,2)
- features: json (feature flags)
- limits: json (user_limit, product_limit, etc)
- trial_days: integer
- is_active: boolean
- priority: integer
- created_at, updated_at
```

### tenant_subscriptions
```sql
- id: bigint
- tenant_id: bigint (FK)
- plan_id: bigint (FK)
- status: enum (trial, active, suspended, cancelled, expired)
- started_at: datetime
- expires_at: datetime
- cancelled_at: datetime
- trial_ends_at: datetime
- billing_cycle: enum (monthly, yearly)
- auto_renew: boolean
- midtrans_subscription_id: string (nullable)
- created_at, updated_at
```

### tenant_usage
```sql
- id: bigint
- tenant_id: bigint (FK)
- metric: string (users, products, orders, etc)
- current_value: integer
- limit_value: integer
- period_start: date
- period_end: date
- created_at, updated_at
```

### invoices
```sql
- id: bigint
- tenant_id: bigint (FK)
- subscription_id: bigint (FK)
- invoice_number: string (unique)
- amount: decimal(12,2)
- tax: decimal(12,2)
- total: decimal(12,2)
- status: enum (draft, sent, paid, overdue, cancelled)
- due_date: date
- paid_at: datetime
- payment_method: string
- payment_gateway_id: string
- pdf_path: string
- created_at, updated_at
```

### system_settings
```sql
- id: bigint
- key: string (unique)
- value: json
- description: text
- is_public: boolean
- created_at, updated_at
```

---

## 🔌 API Endpoints

### Super Admin APIs
```
GET    /api/admin/tenants              - List all tenants
GET    /api/admin/tenants/{id}         - Tenant detail
PATCH  /api/admin/tenants/{id}/status  - Update tenant status
DELETE /api/admin/tenants/{id}         - Delete tenant
GET    /api/admin/dashboard/stats      - Dashboard statistics
GET    /api/admin/dashboard/revenue    - Revenue tracking
GET    /api/admin/dashboard/usage      - System usage
```

### Subscription APIs
```
GET    /api/admin/plans                - List subscription plans
POST   /api/admin/plans                - Create plan
PUT    /api/admin/plans/{id}           - Update plan
DELETE /api/admin/plans/{id}           - Delete plan
POST   /api/tenant/subscription/change - Change subscription plan
GET    /api/tenant/subscription        - Get current subscription
GET    /api/tenant/usage               - Get usage stats
```

### Invoice APIs
```
GET    /api/tenant/invoices            - List invoices
GET    /api/tenant/invoices/{id}       - Invoice detail
GET    /api/tenant/invoices/{id}/pdf   - Download PDF
POST   /api/admin/invoices/generate    - Generate invoices (cron)
```

### Support Ticket APIs
```
GET    /api/tenant/tickets             - List tickets
POST   /api/tenant/tickets             - Create ticket
GET    /api/tenant/tickets/{id}        - Ticket detail
POST   /api/tenant/tickets/{id}/reply  - Reply to ticket
PATCH  /api/tenant/tickets/{id}/status - Update status
GET    /api/admin/tickets              - Admin ticket list
```

---

## 📊 Success Metrics

| Metric | Target |
|--------|--------|
| Tenant onboarding time | < 5 minutes |
| Plan upgrade conversion | > 10% of free tenants |
| Payment success rate | > 95% |
| Invoice generation success | 100% |
| Support ticket response time | < 24 hours |

---

## ⚠️ Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Data isolation breach | Critical | Tenant scoping on all queries, tests |
| Billing errors | High | Double-entry accounting, reconciliation |
| Downtime during migration | Medium | Zero-downtime migration strategy |
| Payment gateway failures | High | Retry logic, manual override, notifications |
| Invoice PDF generation fails | Medium | Queue-based generation, fallback HTML |

---

## ✅ Acceptance Criteria

### Wave 1:
- [ ] Super admin can view all tenants
- [ ] Super admin can change tenant status
- [ ] Dashboard shows revenue, usage, health metrics
- [ ] Tenant list is searchable and filterable

### Wave 2:
- [ ] 4 subscription plans exist (Free, Starter, Pro, Enterprise)
- [ ] Tenants can upgrade/downgrade plans
- [ ] Invoices auto-generated monthly
- [ ] PDF invoices downloadable
- [ ] Payment gateway integration works

### Wave 3:
- [ ] Tenant dashboard shows usage & billing
- [ ] Support ticket system functional
- [ ] Plan change flow works end-to-end
- [ ] Billing history accessible

---

## 📁 Files to Create

### Migrations:
1. `database/migrations/2026_02_22_000001_create_subscription_plans_table.php`
2. `database/migrations/2026_02_22_000002_create_tenant_subscriptions_table.php`
3. `database/migrations/2026_02_22_000003_create_tenant_usage_table.php`
4. `database/migrations/2026_02_22_000004_create_invoices_table.php`
5. `database/migrations/2026_02_22_000005_create_system_settings_table.php`
6. `database/migrations/2026_02_22_000006_create_support_tickets_table.php`

### Models:
1. `app/Models/SubscriptionPlan.php`
2. `app/Models/TenantSubscription.php`
3. `app/Models/TenantUsage.php`
4. `app/Models/Invoice.php`
5. `app/Models/SystemSetting.php`
6. `app/Models/SupportTicket.php`

### Controllers:
1. `app/Http/Controllers/Api/SuperAdmin/DashboardController.php`
2. `app/Http/Controllers/Api/SuperAdmin/TenantController.php`
3. `app/Http/Controllers/Api/SuperAdmin/PlanController.php`
4. `app/Http/Controllers/Api/SuperAdmin/InvoiceController.php`
5. `app/Http/Controllers/Api/SuperAdmin/SupportTicketController.php`
6. `app/Http/Controllers/Api/Tenant/SubscriptionController.php`
7. `app/Http/Controllers/Api/Tenant/UsageController.php`
8. `app/Http/Controllers/Api/Tenant/InvoiceController.php`
9. `app/Http/Controllers/Api/Tenant/SupportTicketController.php`

### Services:
1. `app/Services/SubscriptionService.php`
2. `app/Services/InvoiceService.php`
3. `app/Services/UsageTrackingService.php`
4. `app/Services/PaymentGatewayService.php`

### Middleware:
1. `app/Http/Middleware/SuperAdmin.php`
2. `app/Http/Middleware/CheckSubscription.php`
3. `app/Http/Middleware/CheckFeatureAccess.php`

### Views:
1. `resources/views/pages/super-admin/dashboard.blade.php`
2. `resources/views/pages/super-admin/tenants/index.blade.php`
3. `resources/views/pages/super-admin/tenants/show.blade.php`
4. `resources/views/pages/super-admin/plans/index.blade.php`
5. `resources/views/pages/super-admin/invoices/index.blade.php`
6. `resources/views/pages/tenant/portal/dashboard.blade.php`
7. `resources/views/pages/tenant/portal/subscription.blade.php`
8. `resources/views/pages/tenant/portal/invoices.blade.php`
9. `resources/views/pages/tenant/portal/tickets.blade.php`

### Seeders:
1. `database/seeders/SubscriptionPlansSeeder.php`
2. `database/seeders/SystemSettingsSeeder.php`

### Routes:
1. Update `routes/api.php` - Add super admin & tenant routes
2. Update `routes/web.php` - Add portal pages

---

## 🚀 Implementation Order

**Week 1: Wave 1 (Super Admin Dashboard)**
- Day 1: Migrations + Models
- Day 2: Super Admin APIs + Middleware
- Day 3: Super Admin UI
- Day 4: Testing & refinement

**Week 2: Wave 2 (Subscription & Billing)**
- Day 1: Subscription logic + Services
- Day 2: Payment gateway integration
- Day 3: Invoice generation (PDF)
- Day 4: Automated billing (cron)
- Day 5: Testing

**Week 3: Wave 3 (Tenant Portal)**
- Day 1: Tenant portal UI
- Day 2: Plan change flow
- Day 3: Support ticket system
- Day 4: Usage dashboard
- Day 5: End-to-end testing

---

**Phase 22 Specification - FINALIZED**
**Ready for Wave 1 Implementation**
