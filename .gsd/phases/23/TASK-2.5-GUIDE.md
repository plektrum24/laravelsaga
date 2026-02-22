# Task 2.5: Checkout Flow - IMPLEMENTATION GUIDE

**Date:** 2026-02-22
**Status:** 🔄 IN PROGRESS
**Estimated Duration:** 3-4 hours

---

## 📋 Task Overview

**Objective:** Create a complete 4-step checkout flow with address selection, delivery method, payment method, and order confirmation.

---

## ✅ Components Created (3 files)

### 1. AddressSelector Component
**File:** `components/checkout/AddressSelector.tsx`

**Features:**
- ✅ List of saved addresses
- ✅ Select address on tap
- ✅ Add new address modal
- ✅ Edit existing address
- ✅ Default address badge
- ✅ Empty state with CTA

**Fields:**
- Label (Home, Office, etc.)
- Full name
- Phone number
- Street address
- City
- Postal code

---

### 2. DeliveryMethod Component
**File:** `components/checkout/DeliveryMethod.tsx`

**Features:**
- ✅ Store Pickup (Free)
- ✅ Home Delivery (Rp 15,000)
- ✅ Express Delivery (Rp 30,000)
- ✅ Radio button selection
- ✅ Estimated time display
- ✅ Fee display

**Default Methods:**
| Method | Type | Fee | Time |
|--------|------|-----|------|
| Store Pickup | pickup | Free | 1-2 hours |
| Home Delivery | delivery | Rp 15K | Same day |
| Express | delivery | Rp 30K | 2 hours |

---

### 3. PaymentMethod Component
**File:** `components/checkout/PaymentMethod.tsx`

**Features:**
- ✅ E-wallets (GoPay, ShopeePay)
- ✅ Credit/Debit Card
- ✅ Bank Transfer
- ✅ Cash on Delivery
- ✅ Fee display
- ✅ Security notice
- ✅ Disabled state handling

**Default Methods:**
| Method | Type | Fee |
|--------|------|-----|
| GoPay | ewallet | Free |
| ShopeePay | ewallet | Free |
| Card | card | Free |
| Bank Transfer | transfer | Free |
| COD | cod | Rp 5,000 |

---

## 📁 Files to Create

### Checkout Screens (4 files):

1. **`app/checkout/index.tsx`** - Main checkout screen (step wizard)
2. **`app/checkout/address.tsx`** - Address selection (optional standalone)
3. **`app/checkout/delivery.tsx`** - Delivery method (optional standalone)
4. **`app/checkout/payment.tsx`** - Payment method (optional standalone)
5. **`app/checkout/confirm.tsx`** - Order review & confirmation
6. **`app/checkout/success.tsx`** - Order success page

### Additional Components (2 files):

1. **`components/checkout/OrderReview.tsx`** - Order summary before place order
2. **`components/checkout/CheckoutStepper.tsx`** - Step progress indicator

---

## 🔧 Checkout Flow

### Step 1: Delivery Address
```
┌─────────────────────────────────┐
│ ← Delivery Address              │
├─────────────────────────────────┤
│ + Add New                       │
│                                 │
│ [Home] [Default]          [✏️] │
│ John Doe                        │
│ 0812-3456-7890                  │
│ Jl. Sudirman No. 123, Jakarta   │
│                          [✓]    │
│                                 │
│ [Office]                        │
│ Jane Smith                      │
│ ...                             │
└─────────────────────────────────┘
[Continue to Delivery →]
```

### Step 2: Delivery Method
```
┌─────────────────────────────────┐
│ ← Delivery Method               │
├─────────────────────────────────┤
│ 🏪 Store Pickup      FREE       │
│    Pick up from our store       │
│    🕐 1-2 hours          ( )    │
│                                 │
│ 🚚 Home Delivery    Rp 15,000   │
│    Delivered to your address    │
│    🕐 Same day          (●)    │
│                                 │
│ 🚀 Express Delivery Rp 30,000   │
│    Fast delivery within 2 hours │
│    🕐 Within 2 hours     ( )    │
└─────────────────────────────────┘
[Continue to Payment →]
```

### Step 3: Payment Method
```
┌─────────────────────────────────┐
│ ← Payment Method                │
├─────────────────────────────────┤
│ 💳 GoPay         No fee     (●)│
│    Pay with GoPay balance       │
│                                 │
│ 🛍️ ShopeePay     No fee     ( )│
│    Pay with ShopeePay           │
│                                 │
│ 💳 Card          No fee     ( )│
│    Visa, Mastercard, JCB        │
│                                 │
│ 🏦 Bank Transfer No fee     ( )│
│    BCA, Mandiri, BNI, BRI       │
│                                 │
│ 💵 COD           +Rp 5,000  ( )│
│    Pay when you receive         │
│                                 │
│ 🛡️ All payments secure          │
└─────────────────────────────────┘
[Review Order →]
```

### Step 4: Order Review
```
┌─────────────────────────────────┐
│ ← Review Order                  │
├─────────────────────────────────┤
│ 📍 Delivery To:                 │
│    John Doe - 0812-3456-7890    │
│    Jl. Sudirman No. 123         │
│    [Change]                     │
│                                 │
│ 🚚 Delivery: Home Delivery      │
│    Same day - Rp 15,000         │
│    [Change]                     │
│                                 │
│ 💳 Payment: GoPay               │
│    [Change]                     │
│                                 │
│ ─── Order Summary ───           │
│ Product 1 x 2      Rp 100,000   │
│ Product 2 x 1       Rp 50,000   │
│                                 │
│ Subtotal         Rp 150,000     │
│ Shipping          Rp 15,000     │
│ ─────────────────────────────   │
│ Total            Rp 165,000     │
└─────────────────────────────────┘
[Place Order]
```

### Success Page
```
┌─────────────────────────────────┐
│          ✓                      │
│     Order Confirmed!            │
│                                 │
│  Order #ORD-20260222-001        │
│                                 │
│  Estimated delivery:            │
│  Today, 2:00 PM - 4:00 PM       │
│                                 │
│  [Track Order] [Continue Shop]  │
└─────────────────────────────────┘
```

---

## 📊 State Management

### Checkout Store (to create)
```typescript
interface CheckoutState {
  // Step management
  currentStep: number;
  
  // Address
  selectedAddress: Address | null;
  addresses: Address[];
  
  // Delivery
  selectedDelivery: DeliveryMethod | null;
  
  // Payment
  selectedPayment: PaymentMethod | null;
  
  // Order
  orderNotes: string;
  
  // Actions
  setAddress: (address: Address) => void;
  setDelivery: (method: DeliveryMethod) => void;
  setPayment: (method: PaymentMethod) => void;
  resetCheckout: () => void;
}
```

---

## 🔧 Integration Points

### API Endpoints Needed:
```
POST /api/mobile/checkout          - Create order
GET  /api/mobile/checkout/calculate - Calculate fees
GET  /api/mobile/addresses         - Get saved addresses
POST /api/mobile/addresses         - Add new address
PUT  /api/mobile/addresses/{id}    - Update address
DELETE /api/mobile/addresses/{id}  - Delete address
```

### Store Integration:
```typescript
// Use existing cart store
const { items, total, clearCart } = useCartStore();

// Create checkout store
const { 
  currentStep, 
  selectedAddress, 
  selectedDelivery,
  selectedPayment,
  setAddress,
  setDelivery,
  setPayment,
  placeOrder 
} = useCheckoutStore();
```

---

## ⏭️ Next Steps

To complete Task 2.5, the following needs to be created:

1. **Checkout Store** - State management for checkout flow
2. **Checkout Screens** - 4-6 screens for the flow
3. **Order Review Component** - Summary before placing order
4. **Success Screen** - Order confirmation
5. **API Integration** - Connect to backend checkout endpoints

---

**Estimated Time:** 3-4 hours
**Complexity:** Medium-High
**Dependencies:** Cart store, Address API, Checkout API

---

*Task 2.5 Implementation Guide - Generated 2026-02-22*
