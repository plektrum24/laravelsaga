# Phase 22: Multi-Tenant SaaS Management Portal

**Status:** `PLANNING` → `IMPLEMENTING`  
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
- Tenant status management (active, suspended, trial)
- System health monitoring
- Usage analytics per tenant
- Revenue tracking dashboard

**Timeline:** 1 week

---

### Wave 2: Subscription & Billing
**Objective:** Monetization infrastructure

**Deliverables:**
- Subscription plan management (Free, Pro, Enterprise)
- Feature-based access control
- Usage limits & quotas
- Billing integration (Midtrans/Xendit)
- Invoice generation
- Payment tracking
- Subscription lifecycle management

**Timeline:** 1-2 weeks

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

**Timeline:** 1-2 weeks

---

## 🗄️ Database Schema

### subscription_plans
```sql
- id: bigint
- name: string (Free, Pro, Enterprise)
- code: string (free, pro, enterprise)
- price_monthly: decimal
- price_yearly: decimal
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
- amount: decimal
- tax: decimal
- total: decimal
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

## 🔌 Integration Points

### Existing Tenants
- Migrate existing tenants to subscription plans
- Grandfather existing pricing
- Set trial periods

### Payment Gateway
- Midtrans integration (existing from Phase 19)
- Recurring payment setup
- Webhook handling

### Email System
- Subscription notifications
- Invoice emails
- Payment reminders
- Trial expiry warnings

---

## 📊 Success Metrics

| Metric | Target |
|--------|--------|
| Tenant onboarding time | < 5 minutes |
| Plan upgrade conversion | > 10% of free tenants |
| Payment success rate | > 95% |
| Churn rate | < 5% monthly |
| MRR growth | > 20% monthly |

---

## ⚠️ Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Data isolation breach | Critical | Tenant scoping on all queries |
| Billing errors | High | Double-entry accounting, audits |
| Downtime during migration | Medium | Zero-downtime migration strategy |
| Tenant resistance to pricing | Medium | Grandfather pricing, free tier |

---

## 🚀 Implementation Plan

### Week 1: Super Admin Dashboard
- Day 1-2: Database migrations
- Day 3-4: Super admin controller & APIs
- Day 5: Admin UI

### Week 2: Subscription & Billing
- Day 1-2: Subscription models & logic
- Day 3-4: Payment integration
- Day 5: Invoice generation

### Week 3: Tenant Self-Service
- Day 1-2: Tenant portal UI
- Day 3-4: Plan management
- Day 5: Testing & deployment

---

**Ready to implement!**
