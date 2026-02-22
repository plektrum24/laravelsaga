# Mobile App API Documentation

**Version:** 1.0.0  
**Base URL:** `/api/mobile`  
**Authentication:** Bearer Token (Laravel Sanctum)

---

## 📱 Authentication

### Login
```http
POST /api/mobile/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": { "id": 1, "name": "John Doe", "email": "user@example.com" },
    "token": "1|abc123...",
    "customer": { "id": 1, "name": "John Doe", "email": "user@example.com" },
    "token_type": "Bearer"
  }
}
```

### Register
```http
POST /api/mobile/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "phone": "08123456789"
}
```

### Logout
```http
POST /api/mobile/logout
Authorization: Bearer {token}
```

---

## 🏠 Home & Catalog

### Get Home Data
```http
GET /api/mobile/home
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "featured_products": [...],
    "categories": [...],
    "banners": [...]
  }
}
```

### Product List
```http
GET /api/mobile/products?q=search&category_id=1&in_stock=true&sort=price_low&limit=20
Authorization: Bearer {token}
```

**Query Parameters:**
- `q` - Search query
- `category_id` - Filter by category
- `in_stock` - Show only in-stock items
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `sort` - Sort by: `relevance`, `price_low`, `price_high`, `newest`, `popular`
- `limit` - Items per page (default: 20)

### Product Detail
```http
GET /api/mobile/products/{id}
Authorization: Bearer {token}
```

---

## 🛒 Cart

### Cart Summary
```http
GET /api/mobile/cart/summary
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "item_count": 5,
    "total": 150000
  }
}
```

---

## 🎯 Loyalty Program

### Loyalty Summary
```http
GET /api/mobile/loyalty/summary
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "points_balance": 500,
    "total_earned": 1000,
    "expiring_soon": 100,
    "tier": { "id": 2, "name": "Silver" },
    "tier_name": "Silver",
    "tier_benefits": {
      "discount_percent": 2,
      "points_multiplier": 1.2,
      "birthday_bonus": 50
    }
  }
}
```

### QR Membership Code
```http
GET /api/mobile/loyalty/qr-code
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "qr_code": "base64_encoded_data",
    "customer": {
      "name": "John Doe",
      "email": "user@example.com",
      "phone": "08123456789"
    }
  }
}
```

---

## 📦 Orders

### Order History
```http
GET /api/mobile/orders?limit=20
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "order_number": "WO-20260221-1234",
        "status": "confirmed",
        "payment_status": "paid",
        "total": 150000,
        "created_at": "2026-02-21T10:00:00Z"
      }
    ],
    "current_page": 1,
    "last_page": 5
  }
}
```

### Order Detail
```http
GET /api/mobile/orders/{orderNumber}
Authorization: Bearer {token}
```

---

## 🔧 Utilities

### App Settings
```http
GET /api/mobile/settings
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "loyalty": {
      "enabled": true,
      "earn_rate": 10000,
      "point_value": 100
    },
    "payment_methods": [
      { "code": "cod", "name": "Cash on Delivery", "enabled": true },
      { "code": "bank_transfer", "name": "Bank Transfer", "enabled": true }
    ],
    "app_version": "1.0.0",
    "min_app_version": "1.0.0"
  }
}
```

### Scan Barcode
```http
POST /api/mobile/scan
Authorization: Bearer {token}
Content-Type: application/json

{
  "barcode": "123456789"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product Name",
    "sell_price": 50000,
    "stock": 100
  }
}
```

---

## 🔐 Authentication

All endpoints (except login/register) require Bearer token authentication:

```
Authorization: Bearer {your_token}
```

---

## 📊 Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Product not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## 📱 Mobile Implementation Notes

### Recommended Framework
- **React Native** or **Flutter** for cross-platform
- **PWA** for web-based approach

### State Management
- Redux (React Native)
- Provider/Bloc (Flutter)

### Key Libraries
- `@react-native-async-storage/async-storage` - Token storage
- `react-native-vector-icons` - Icons
- `react-native-qrcode-scanner` - Barcode scanning
- `react-native-push-notification` - Push notifications
- `axios` - HTTP client

### Security Best Practices
1. Store tokens securely (Keychain/Keystore)
2. Use HTTPS only
3. Implement token refresh
4. Biometric authentication (optional)
5. Certificate pinning (production)

---

## 🚀 Getting Started

1. **Clone Backend**
   ```bash
   git clone <repository>
   cd laravelsaga
   composer install
   php artisan migrate
   ```

2. **Configure Environment**
   ```env
   APP_URL=http://localhost:8000
   SANCTUM_STATEFUL_DOMAINS=localhost
   ```

3. **Test APIs**
   ```bash
   # Login
   curl -X POST http://localhost:8000/api/mobile/login \
     -H "Content-Type: application/json" \
     -d '{"email":"user@example.com","password":"password"}'
   ```

4. **Start Mobile Development**
   ```bash
   # React Native
   npx react-native init SagaApp
   cd SagaApp
   npm install @react-navigation/native axios
   
   # Flutter
   flutter create saga_app
   cd saga_app
   flutter pub add http provider
   ```

---

**API Version:** 1.0.0  
**Last Updated:** 2026-02-21  
**Contact:** dev@sagaposo.com
