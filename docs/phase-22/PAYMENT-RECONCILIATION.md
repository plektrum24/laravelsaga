# Payment Reconciliation Guide

**SAGA POS - Midtrans Integration**  
**Version:** 1.0.0  
**Date:** 2026-02-22

---

## Table of Contents

1. [Overview](#overview)
2. [Payment Flow](#payment-flow)
3. [Daily Reconciliation](#daily-reconciliation)
4. [Handling Discrepancies](#handling-discrepancies)
5. [Refund Process](#refund-process)
6. [Reporting](#reporting)
7. [Troubleshooting](#troubleshooting)

---

## Overview

### Payment Gateway: Midtrans

**Supported Payment Methods:**
- Credit/Debit Cards (Visa, Mastercard, JCB)
- Bank Transfers (BCA, Mandiri, BNI, BRI)
- E-Wallets (GoPay, ShopeePay, LinkAja)
- QRIS
- Installments

### Account Types

**Sandbox Mode (Testing):**
- URL: `https://app.sandbox.midtrans.com`
- Test cards available
- No real money involved
- Use for development and testing

**Production Mode (Live):**
- URL: `https://app.midtrans.com`
- Real transactions
- Requires KYC verification
- Settlement to bank account

### Key Credentials

```env
# Sandbox
MIDTRANS_SERVER_KEY=SB-Mid-server-xxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxx
MIDTRANS_IS_PRODUCTION=false

# Production
MIDTRANS_SERVER_KEY=Mid-server-xxx
MIDTRANS_CLIENT_KEY=Mid-client-xxx
MIDTRANS_IS_PRODUCTION=true
```

---

## Payment Flow

### Transaction Lifecycle

```
1. Customer initiates payment
   ↓
2. System creates invoice
   ↓
3. Payment initiated via Midtrans Snap
   ↓
4. Customer completes payment
   ↓
5. Midtrans sends webhook
   ↓
6. System updates invoice status
   ↓
7. Funds settled to bank account (T+1)
```

### Payment Status Mapping

| Midtrans Status | System Status | Action |
|----------------|---------------|--------|
| pending | sent | Awaiting payment |
| settlement | paid | Payment successful |
| capture | paid | Card payment captured |
| deny | failed | Payment declined |
| expire | cancelled | Payment expired |
| cancel | cancelled | Payment cancelled |
| refund | refunded | Payment refunded |
| chargeback | disputed | Chargeback received |

### Webhook Handling

**Webhook Endpoint:**
```
POST /payments/callback/midtrans
```

**Webhook Payload:**
```json
{
  "order_id": "INV-20260222-ABC123",
  "transaction_id": "MID-123456",
  "transaction_time": "2026-02-22 10:30:00",
  "transaction_status": "settlement",
  "fraud_status": "accept",
  "payment_type": "credit_card",
  "gross_amount": "299000.00",
  "signature_key": "abc123xyz..."
}
```

**Processing Steps:**
1. Verify signature key
2. Find invoice by order_id
3. Check transaction status
4. Update invoice status
5. Log transaction
6. Send notification

---

## Daily Reconciliation

### Reconciliation Process

**Time:** Daily at 10:00 AM (after settlement)

**Sources:**
1. **System Records:** Invoice database
2. **Midtrans Report:** Transaction report
3. **Bank Statement:** Settlement report

### Step-by-Step Process

#### Step 1: Download Midtrans Report

**Via Midtrans Dashboard:**
1. Login to Midtrans dashboard
2. Go to Reports → Transactions
3. Select date range (yesterday)
4. Download CSV

**Via API:**
```bash
curl -u SB-Mid-server-xxx: \
  https://api.sandbox.midtrans.com/v2/reports/transactions?start_date=2026-02-21&end_date=2026-02-21
```

#### Step 2: Export System Invoices

**Via Database:**
```sql
SELECT 
  invoice_number,
  total,
  status,
  payment_method,
  payment_gateway_id,
  paid_at
FROM invoices
WHERE DATE(paid_at) = '2026-02-21'
  AND status = 'paid';
```

**Via Admin Panel:**
1. Go to Super Admin → Invoices
2. Filter by date and status
3. Export to CSV

#### Step 3: Compare Records

**Matching Criteria:**
- Order ID / Invoice Number
- Transaction Amount
- Transaction Date
- Payment Status

**Reconciliation Template:**

| Invoice # | System Amount | Midtrans Amount | Status | Match |
|-----------|--------------|-----------------|--------|-------|
| INV-001 | 299,000 | 299,000 | settlement | ✅ |
| INV-002 | 99,000 | 99,000 | settlement | ✅ |
| INV-003 | 299,000 | 0 | pending | ❌ |

#### Step 4: Identify Discrepancies

**Common Discrepancies:**

| Type | Description | Action |
|------|-------------|--------|
| **Missing in System** | Payment in Midtrans, not in system | Check webhook logs, manual update |
| **Missing in Midtrans** | Payment in system, not in Midtrans | Verify payment, check test mode |
| **Amount Mismatch** | Different amounts | Check fees, refunds, adjustments |
| **Status Mismatch** | Different status | Update to correct status |

#### Step 5: Resolve Discrepancies

**Missing Payment in System:**

1. Find invoice in admin panel
2. Verify payment in Midtrans dashboard
3. Manually mark as paid
4. Add transaction ID
5. Document in reconciliation log

**False Positive Payment:**

1. Check if test mode was used
2. Verify payment gateway response
3. Reverse payment if invalid
4. Document in reconciliation log

#### Step 6: Generate Reconciliation Report

**Daily Reconciliation Report:**

```
Date: 2026-02-22
Prepared by: [Name]

Summary:
- Total invoices paid: 25
- Total amount: Rp 7,475,000
- Matched: 24
- Discrepancies: 1

Discrepancies:
1. INV-003 - Payment pending in Midtrans
   Action: Follow up with customer

Settlement:
- Expected settlement: Rp 7,176,000 (after fees)
- Settlement date: 2026-02-23
- Bank account: BCA 1234567890

Verified by: ___________
Date: ___________
```

---

## Handling Discrepancies

### Payment Not Reflected in System

**Symptoms:**
- Customer claims payment successful
- Invoice still shows "sent" or "pending"
- Payment appears in Midtrans

**Investigation Steps:**

1. **Check Webhook Logs:**
```bash
grep "INV-003" storage/logs/laravel.log
```

2. **Verify Midtrans Status:**
```bash
curl -u SB-Mid-server-xxx: \
  https://api.sandbox.midtrans.com/v2/INV-003/status
```

3. **Check Failed Jobs:**
```bash
php artisan queue:failed
```

**Resolution:**

1. Manual status update:
```php
$invoice = Invoice::where('invoice_number', 'INV-003')->first();
$invoice->markAsPaid('midtrans', 'MID-123456');
```

2. Document in reconciliation log

3. Notify customer

### Payment Failed but Amount Deducted

**Symptoms:**
- Payment failed in Midtrans
- Customer's bank shows deduction
- Invoice not marked as paid

**Investigation Steps:**

1. Check Midtrans transaction status
2. Contact Midtrans support
3. Verify with customer's bank

**Resolution:**

1. Wait for auto-reversal (1-3 business days)
2. If not reversed, file dispute with Midtrans
3. Offer customer alternative payment method

### Duplicate Payment

**Symptoms:**
- Two payments for same invoice
- Customer charged twice

**Investigation Steps:**

1. Check invoice payment history
2. Verify both transactions in Midtrans
3. Check webhook processing logs

**Resolution:**

1. Refund duplicate payment
2. Document in reconciliation log
3. Investigate root cause

---

## Refund Process

### Types of Refunds

**Full Refund:**
- Return entire payment amount
- Invoice status → cancelled

**Partial Refund:**
- Return portion of payment
- Invoice remains paid
- Credit note issued

### Refund via Midtrans

**Via Dashboard:**
1. Login to Midtrans dashboard
2. Find transaction
3. Click "Refund"
4. Enter amount and reason
5. Submit

**Via API:**
```bash
curl -X POST -u SB-Mid-server-xxx: \
  https://api.sandbox.midtrans.com/v2/MID-123456/refund \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 299000,
    "reason": "Customer request"
  }'
```

### Refund in System

**Create Refund Record:**

```php
$invoice->refunds()->create([
    'amount' => 299000,
    'reason' => 'Customer request',
    'midtrans_refund_id' => 'REF-123456',
    'status' => 'pending',
    'requested_by' => auth()->id(),
]);
```

**Update Invoice:**

```php
if ($refund->amount == $invoice->total) {
    $invoice->status = 'refunded';
} else {
    $invoice->refunded_amount += $refund->amount;
}
$invoice->save();
```

### Refund Timeline

| Step | Timeline |
|------|----------|
| Refund request | Day 0 |
| Midtrans processing | 1-2 business days |
| Bank processing | 3-7 business days |
| Customer receives | 5-10 business days |

---

## Reporting

### Daily Reports

**Payment Summary:**
```
Date: 2026-02-22

Payment Methods:
- Credit Card: 15 transactions, Rp 4,485,000
- Bank Transfer: 8 transactions, Rp 2,392,000
- GoPay: 2 transactions, Rp 598,000

Total: 25 transactions, Rp 7,475,000
Fees (3%): Rp 224,250
Net Settlement: Rp 7,250,750
```

### Weekly Reports

**Weekly Reconciliation:**
- Total transactions
- Success rate
- Refund count
- Discrepancy summary
- Settlement amounts

### Monthly Reports

**Monthly Financial Report:**
- Gross revenue
- Payment gateway fees
- Refunds issued
- Chargebacks
- Net revenue

**Report Generation:**

```bash
php artisan saas:generate-monthly-report --month=2026-02
```

---

## Troubleshooting

### Common Issues

#### Webhook Not Received

**Symptoms:**
- Payment successful in Midtrans
- System not updated
- No webhook in logs

**Solutions:**
1. Check webhook URL in Midtrans dashboard
2. Verify SSL certificate
3. Check firewall rules
4. Test webhook manually

**Test Webhook:**
```bash
curl -X POST https://your-domain.com/payments/callback/midtrans \
  -H "Content-Type: application/json" \
  -d '{"order_id":"TEST-001","transaction_status":"settlement"}'
```

#### Signature Verification Failed

**Symptoms:**
- Webhook received
- Signature verification fails
- Payment not processed

**Solutions:**
1. Verify server key is correct
2. Check signature generation method
3. Ensure UTF-8 encoding
4. Contact Midtrans support

#### Settlement Not Received

**Symptoms:**
- Payments successful
- No settlement in bank account
- Settlement date passed

**Solutions:**
1. Check settlement report in Midtrans
2. Verify bank account details
3. Check for holidays/weekends
4. Contact Midtrans support

### Contact Information

**Midtrans Support:**
- Email: support@midtrans.com
- Phone: +62-21-3000-5000
- Hours: Mon-Fri 9:00-17:00 WIB

**Internal Support:**
- Finance Team: [Email]
- Technical Team: [Email]
- On-Call: [Phone]

---

## Best Practices

### Security

- Never share server keys
- Use environment variables
- Rotate keys periodically
- Monitor for fraud
- Enable 3D Secure for cards

### Operations

- Reconcile daily
- Document all discrepancies
- Keep detailed logs
- Backup before bulk operations
- Test in sandbox first

### Customer Service

- Respond to payment issues within 24 hours
- Provide clear payment instructions
- Send payment confirmations
- Offer multiple payment methods
- Have refund policy documented

---

*Payment Reconciliation Guide v1.0.0 - SAGA POS*
