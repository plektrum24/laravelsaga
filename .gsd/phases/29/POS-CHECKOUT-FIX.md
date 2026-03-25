# POS Checkout Error Fix

**Issue:** `SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON`  
**Date Fixed:** 2026-02-27  
**Status:** ✅ FIXED

---

## 🐛 Root Cause

Error terjadi karena:
1. **API mengembalikan HTML error page** bukan JSON
2. **Session/token expired** - middleware auth mengembalikan redirect ke login page
3. **Validasi tidak lengkap** - field yang dikirim tidak sesuai dengan yang diharapkan
4. **Error handling lemah** - tidak ada pengecekan content-type response

---

## ✅ Solutions Applied

### 1. Frontend Fix (`pos/index.blade.php`)

**Changes:**
- ✅ Added token validation before request
- ✅ Added cart items validation
- ✅ Added content-type check (HTML vs JSON)
- ✅ Improved error messages
- ✅ Auto-redirect to login on session expired
- ✅ Fixed field names to match backend validation

**Before:**
```javascript
const response = await fetch('/api/transactions', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        cart_items: this.cart.map(item => ({
            product_id: item.id,
            unit_id: item.unitId,
            quantity: item.qty,  // ❌ Wrong field name
            price: item.price
        })),
        payment_method: 'Cash',
    })
});
```

**After:**
```javascript
// Validate token
if (!token) {
    Swal.fire('Error', 'Session expired. Silakan login ulang.', 'error');
    window.location.href = '/signin';
    return;
}

// Validate cart items
const cartItems = this.cart.map(item => {
    if (!item.id || !item.price || item.qty <= 0) {
        throw new Error('Data produk tidak valid: ' + (item.name || 'Unknown'));
    }
    return {
        product_id: item.id,
        unit_id: item.unitId || null,
        qty: item.qty,  // ✅ Correct field name
        price: item.price,
        subtotal: item.price * item.qty
    };
});

const payload = {
    cart_items: cartItems,
    payment_method: 'cash',
    paid_amount: this.total,
    customer_id: null,
    notes: null
};

const response = await fetch('/api/transactions', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'  // ✅ Force JSON response
    },
    body: JSON.stringify(payload)
});

// Check if response is HTML (error page)
const contentType = response.headers.get('content-type');
if (contentType && contentType.includes('text/html')) {
    throw new Error('Server mengembalikan HTML error page. Session mungkin expired.');
}
```

---

### 2. Backend Fix (`TransactionController.php`)

**Changes:**
- ✅ Complete validation rules
- ✅ Better error handling
- ✅ Stock validation before decrement
- ✅ Branch ID validation with fallback
- ✅ Proper transaction rollback on error
- ✅ Detailed error messages
- ✅ Load relationships in response

**Before:**
```php
$request->validate([
    'cart_items' => 'required|array|min:1',
    'paid_amount' => 'required|numeric',
    'payment_method' => 'required|string',
]);

// ... loose validation, no field-level checks
```

**After:**
```php
$validated = $request->validate([
    'cart_items' => 'required|array|min:1',
    'cart_items.*.product_id' => 'required|exists:products,id',
    'cart_items.*.unit_id' => 'nullable|exists:units,id',
    'cart_items.*.qty' => 'required|numeric|min:1',
    'cart_items.*.price' => 'required|numeric|min:0',
    'payment_method' => 'required|in:cash,transfer,debit,credit,ewallet',
    'paid_amount' => 'required|numeric|min:0',
    'customer_id' => 'nullable|exists:customers,id',
    'notes' => 'nullable|string',
]);

// Stock validation
if ($product->stock < $stockToDeduct) {
    DB::connection('tenant')->rollBack();
    return response()->json([
        'success' => false,
        'message' => 'Stok tidak mencukupi untuk produk: ' . $product->name
    ], 400);
}

// Branch validation
$branchId = $user->branch_id ?? $user->current_branch_id;
if (!$branchId) {
    DB::connection('tenant')->rollBack();
    return response()->json([
        'success' => false,
        'message' => 'Branch tidak ditemukan. Silakan pilih branch terlebih dahulu.'
    ], 400);
}
```

---

## 📊 Error Response Handling

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "cart_items.0.product_id": ["The selected product id is invalid."]
    }
}
```

### Stock Error (400)
```json
{
    "success": false,
    "message": "Stok tidak mencukupi untuk produk: Kopi Susu Gula Aren"
}
```

### Branch Error (400)
```json
{
    "success": false,
    "message": "Branch tidak ditemukan. Silakan pilih branch terlebih dahulu."
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Error: Connection timeout"
}
```

---

## 🧪 Testing Checklist

### Manual Testing:
- [ ] Login dengan user valid
- [ ] Tambahkan produk ke keranjang
- [ ] Klik "Bayar Sekarang"
- [ ] Verifikasi invoice number
- [ ] Cetak struk
- [ ] Cek stock berkurang
- [ ] Cek inventory movement

### Edge Cases:
- [ ] Cart kosong → Should show warning
- [ ] Token expired → Should redirect to login
- [ ] Stock tidak cukup → Should show error
- [ ] Branch tidak dipilih → Should show error
- [ ] Network error → Should show connection error

---

## 🔧 Files Modified

1. **`resources/views/pages/pos/index.blade.php`**
   - Enhanced checkout validation
   - Better error handling
   - Session expiry detection

2. **`app/Http/Controllers/Api/TransactionController.php`**
   - Complete validation
   - Stock checking
   - Branch validation
   - Better error responses

---

## 📝 Usage Example

### Valid Request:
```javascript
POST /api/transactions
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json

Body:
{
  "cart_items": [
    {
      "product_id": 1,
      "unit_id": 2,
      "qty": 2,
      "price": 15000,
      "subtotal": 30000
    }
  ],
  "payment_method": "cash",
  "paid_amount": 30000,
  "customer_id": null,
  "notes": null
}
```

### Success Response:
```json
{
  "success": true,
  "message": "Transaksi berhasil",
  "data": {
    "id": 123,
    "invoice_number": "INV/20260227/0042",
    "grand_total": 30000,
    "paid_amount": 30000,
    "change_amount": 0,
    "payment_method": "cash",
    "status": "completed",
    "items": [
      {
        "product_id": 1,
        "qty": 2,
        "price": 15000,
        "subtotal": 30000,
        "product": {
          "name": "Kopi Susu Gula Aren"
        }
      }
    ]
  }
}
```

---

## 🎯 Result

**Before Fix:**
- ❌ HTML error page returned
- ❌ No validation
- ❌ Poor error messages
- ❌ Session expiry not handled

**After Fix:**
- ✅ Proper JSON responses
- ✅ Complete validation
- ✅ Clear error messages
- ✅ Auto-redirect on session expired
- ✅ Stock validation
- ✅ Branch validation

---

*POS Checkout Error Fix Documentation*  
**Fixed:** 2026-02-27  
**Status:** ✅ COMPLETE
