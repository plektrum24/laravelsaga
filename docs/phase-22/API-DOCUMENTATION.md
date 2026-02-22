# Phase 22: SaaS Management - API Documentation

**Version:** 1.0.0  
**Date:** 2026-02-22  
**Base URL:** `/api`

---

## Table of Contents

1. [Authentication](#authentication)
2. [Super Admin APIs](#super-admin-apis)
3. [Tenant Portal APIs](#tenant-portal-apis)
4. [Payment APIs](#payment-apis)
5. [Error Responses](#error-responses)

---

## Authentication

All API endpoints require authentication via Laravel Sanctum tokens.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Middleware
- **Super Admin routes:** `auth:sanctum`, `super_admin`
- **Tenant routes:** `auth:sanctum`, `tenant`
- **Payment callbacks:** Public (no auth required)

---

## Super Admin APIs

### Dashboard

#### Get Dashboard Statistics
```http
GET /admin/dashboard/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "stats": {
      "total_tenants": 150,
      "active_tenants": 120,
      "suspended_tenants": 10,
      "total_revenue": 50000000,
      "monthly_revenue": 15000000,
      "total_invoices": 450,
      "unpaid_invoices": 25,
      "overdue_invoices": 8,
      "open_tickets": 15,
      "urgent_tickets": 2
    },
    "revenue_trend": [
      { "month": "Aug 2026", "revenue": 12000000 },
      { "month": "Sep 2026", "revenue": 13500000 }
    ],
    "plan_distribution": [
      { "name": "Free", "code": "free", "count": 50 },
      { "name": "Starter", "code": "starter", "count": 60 }
    ],
    "recent_tenants": [...],
    "recent_tickets": [...]
  }
}
```

---

### Tenants

#### List Tenants
```http
GET /admin/tenants?search=keyword&status=active&plan_id=1&sort_by=created_at&per_page=15
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| search | string | - | Search name, email, business |
| status | string | - | Filter by status (trial, active, suspended) |
| plan_id | integer | - | Filter by plan |
| sort_by | string | created_at | Sort field |
| sort_order | string | desc | asc or desc |
| per_page | integer | 15 | Pagination size |

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Test Tenant",
        "email": "test@example.com",
        "business_name": "Test Business",
        "subscription": {
          "id": 1,
          "status": "active",
          "plan": { "name": "Professional", "code": "pro" }
        }
      }
    ],
    "last_page": 10,
    "total": 150,
    "per_page": 15
  }
}
```

#### Get Tenant Detail
```http
GET /admin/tenants/{tenant}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tenant": {
      "id": 1,
      "name": "Test Tenant",
      "email": "test@example.com",
      "subscription": {
        "status": "active",
        "plan": { "name": "Professional" },
        "expires_at": "2026-12-31"
      }
    },
    "usage": [...],
    "invoice_stats": {...}
  }
}
```

#### Update Tenant Status
```http
PATCH /admin/tenants/{tenant}/status
Content-Type: application/json

{
  "status": "suspended",
  "reason": "Payment overdue"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Tenant status updated successfully",
  "data": {
    "subscription": {
      "id": 1,
      "status": "suspended"
    }
  }
}
```

#### Extend Subscription
```http
POST /admin/tenants/{tenant}/extend
Content-Type: application/json

{
  "days": 30
}
```

---

### Subscription Plans

#### List Plans
```http
GET /admin/plans
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Free",
      "code": "free",
      "price_monthly": 0,
      "price_yearly": 0,
      "features": ["pos_access", "basic_inventory"],
      "limits": {
        "users": 3,
        "products": 100,
        "branches": 1
      },
      "trial_days": 0,
      "is_active": true
    }
  ]
}
```

#### Create Plan
```http
POST /admin/plans
Content-Type: application/json

{
  "name": "Professional",
  "code": "pro",
  "price_monthly": 299000,
  "price_yearly": 2990000,
  "features": ["pos_access", "advanced_reports"],
  "limits": {
    "users": 50,
    "products": 10000,
    "branches": 10
  },
  "trial_days": 14,
  "is_active": true,
  "priority": 3
}
```

#### Update Plan
```http
PUT /admin/plans/{plan}
Content-Type: application/json

{
  "name": "Professional Plus",
  "price_monthly": 399000
}
```

---

### Invoices

#### List Invoices
```http
GET /admin/invoices?status=sent&tenant_id=1&start_date=2026-01-01&end_date=2026-12-31
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| status | string | Filter by status |
| tenant_id | integer | Filter by tenant |
| start_date | date | Date range start |
| end_date | date | Date range end |
| search | string | Search invoice number |

#### Download Invoice PDF
```http
GET /admin/invoices/{invoice}/pdf
```

**Response:** PDF file download

#### Mark Invoice as Paid
```http
POST /admin/invoices/{invoice}/mark-paid
Content-Type: application/json

{
  "payment_method": "bank_transfer",
  "payment_gateway_id": "MID-123456"
}
```

#### Generate Recurring Invoices (Manual)
```http
POST /admin/invoices/generate-recurring
```

**Response:**
```json
{
  "success": true,
  "message": "Generated 25 invoices",
  "data": { "count": 25 }
}
```

---

### Support Tickets

#### List Tickets
```http
GET /admin/tickets?status=open&priority=high&assigned_to_me=true
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| status | string | Filter by status |
| priority | string | Filter by priority |
| assigned_to_me | boolean | Show my tickets only |
| category | string | Filter by category |

#### Assign Ticket
```http
POST /admin/tickets/{ticket}/assign
Content-Type: application/json

{
  "assigned_to": 5
}
```

#### Reply to Ticket
```http
POST /admin/tickets/{ticket}/reply
Content-Type: application/json

{
  "message": "Thank you for contacting support. We are looking into this issue."
}
```

#### Resolve Ticket
```http
POST /admin/tickets/{ticket}/resolve
```

---

## Tenant Portal APIs

### Subscription

#### Get Current Subscription
```http
GET /tenant/subscription
```

**Response:**
```json
{
  "success": true,
  "data": {
    "subscription": {
      "id": 1,
      "status": "active",
      "billing_cycle": "monthly",
      "expires_at": "2026-12-31",
      "plan": {
        "name": "Professional",
        "code": "pro",
        "price_monthly": 299000,
        "limits": {
          "users": 50,
          "products": 10000
        }
      }
    },
    "available_plans": [
      {
        "id": 2,
        "name": "Enterprise",
        "price_monthly": 999000,
        "can_upgrade": true,
        "is_current": false
      }
    ]
  }
}
```

#### Change Plan
```http
POST /tenant/subscription/change
Content-Type: application/json

{
  "plan_id": 2,
  "billing_cycle": "yearly"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription plan changed successfully",
  "data": {
    "subscription": {...},
    "new_plan": {...},
    "expires_at": "2027-12-31"
  }
}
```

#### Cancel Subscription
```http
POST /tenant/subscription/cancel
Content-Type: application/json

{
  "reason": "Too expensive",
  "cancel_immediately": false
}
```

---

### Usage

#### Get Current Usage
```http
GET /tenant/usage/current
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tenant_id": 1,
      "metric": "users",
      "current_value": 25,
      "limit_value": 50,
      "period_start": "2026-02-01",
      "period_end": "2026-02-28"
    },
    {
      "metric": "products",
      "current_value": 500,
      "limit_value": 10000
    }
  ]
}
```

#### Check Limits
```http
GET /tenant/usage/check-limits
```

**Response:**
```json
{
  "success": true,
  "data": {
    "is_over_limit": false,
    "over_limit_metrics": []
  }
}
```

---

### Invoices

#### List Invoices
```http
GET /tenant/invoices?status=paid&per_page=10
```

#### Get Invoice Summary
```http
GET /tenant/invoices/summary
```

**Response:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total": 24,
      "paid_total": 5000000,
      "pending_total": 299000,
      "overdue_total": 0
    },
    "latest_unpaid": {
      "id": 100,
      "invoice_number": "INV-20260222-ABC123",
      "total": 299000,
      "due_date": "2026-03-08"
    }
  }
}
```

#### Download Invoice PDF
```http
GET /tenant/invoices/{invoice}/pdf
```

#### Pay Invoice
```http
POST /tenant/invoices/{invoice}/pay
Content-Type: application/json

{
  "payment_method": "credit_card"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment initiated",
  "data": {
    "snap_token": "abc123xyz",
    "payment_url": "https://app.midtrans.com/snap/v2/vtweb/abc123xyz"
  }
}
```

---

### Support Tickets

#### List Tickets
```http
GET /tenant/tickets?status=open
```

#### Create Ticket
```http
POST /tenant/tickets
Content-Type: application/json

{
  "subject": "Cannot access dashboard",
  "message": "I'm getting a 403 error when trying to access the dashboard.",
  "priority": "high",
  "category": "Technical"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Support ticket created successfully",
  "data": {
    "id": 15,
    "subject": "Cannot access dashboard",
    "status": "open",
    "created_at": "2026-02-22T10:30:00Z"
  }
}
```

#### Reply to Ticket
```http
POST /tenant/tickets/{ticket}/reply
Content-Type: application/json

{
  "message": "Thank you for the update. The issue is now resolved."
}
```

#### Close Ticket
```http
POST /tenant/tickets/{ticket}/close
```

#### Reopen Ticket
```http
POST /tenant/tickets/{ticket}/reopen
```

---

## Payment APIs

### Payment Callbacks (Public)

#### Midtrans Webhook
```http
POST /payments/callback/midtrans
Content-Type: application/json

{
  "order_id": "INV-20260222-ABC123",
  "transaction_status": "settlement",
  "fraud_status": "accept",
  "transaction_id": "MID-123456",
  "gross_amount": "299000.00"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment notification processed",
  "data": {
    "invoice_id": 100,
    "status": "paid"
  }
}
```

#### Payment Finish Callback
```http
GET /payments/callback/finish?order_id=INV-xxx&status_code=200&transaction_id=MID-123
```

#### Get Payment Status
```http
GET /payments/status/{invoiceNumber}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "transaction_id": "MID-123456",
    "transaction_status": "settlement",
    "fraud_status": "accept",
    "payment_type": "credit_card"
  }
}
```

---

## Error Responses

### Standard Error Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### HTTP Status Codes

| Code | Meaning | Example |
|------|---------|---------|
| 200 | Success | Successful GET/POST |
| 201 | Created | Resource created |
| 400 | Bad Request | Invalid input |
| 401 | Unauthorized | Missing/invalid token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable | Validation errors |
| 500 | Server Error | Internal error |

### Common Error Messages

```json
// Unauthenticated
{
  "success": false,
  "message": "Unauthenticated"
}

// Forbidden
{
  "success": false,
  "message": "Forbidden. Super Admin access required."
}

// Resource not found
{
  "success": false,
  "message": "Invoice not found"
}

// Validation error
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "plan_id": ["The selected plan is invalid."],
    "billing_cycle": ["The billing cycle must be monthly or yearly."]
  }
}
```

---

## Rate Limiting

API endpoints are rate-limited by Laravel Sanctum:
- **Standard:** 60 requests per minute
- **Payment callbacks:** No limit

---

## Pagination

List endpoints support pagination:

```http
GET /admin/tenants?per_page=15&page=2
```

**Pagination Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 2,
    "data": [...],
    "first_page_url": "...page=1",
    "from": 16,
    "last_page": 10,
    "last_page_url": "...page=10",
    "next_page_url": "...page=3",
    "path": "...",
    "per_page": 15,
    "prev_page_url": "...page=1",
    "to": 30,
    "total": 150
  }
}
```

---

## Testing

### Using cURL

```bash
# Get subscription
curl -X GET "http://localhost/api/tenant/subscription" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Change plan
curl -X POST "http://localhost/api/tenant/subscription/change" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"plan_id": 2, "billing_cycle": "yearly"}'
```

### Using JavaScript Fetch

```javascript
const token = localStorage.getItem('saga_token');

// Get subscription
fetch('/api/tenant/subscription', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(res => res.json())
.then(data => console.log(data));

// Change plan
fetch('/api/tenant/subscription/change', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    plan_id: 2,
    billing_cycle: 'yearly'
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

---

*API Documentation v1.0.0 - Phase 22 SaaS Management*
