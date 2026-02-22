# Phase 20: Mobile App Specification

**Date:** 2026-02-21
**Status:** IMPLEMENTATION READY
**Milestone:** v1.9 — Mobile Experience
**Framework:** React Native (Expo)

---

## 📱 Overview

Build a cross-platform mobile shopping app for iOS and Android that integrates with the existing e-commerce and loyalty systems.

---

## 🎯 Objectives

1. **Mobile Shopping Experience** - Browse, search, and purchase products
2. **Loyalty Integration** - Points, tiers, rewards, and QR membership
3. **Seamless Checkout** - Secure payment processing
4. **Engagement** - Push notifications and personalized features
5. **In-Store Features** - Scan & go, store locator

---

## 🏗️ Technical Architecture

### Stack Selection

| Component | Technology | Rationale |
|-----------|------------|-----------|
| **Framework** | React Native (Expo) | Cross-platform, fast development |
| **Language** | TypeScript | Type safety, better DX |
| **State Management** | Zustand | Lightweight, simple |
| **Navigation** | React Navigation | Industry standard |
| **HTTP Client** | Axios | Reliable, interceptors |
| **Storage** | AsyncStorage + Expo SecureStore | Local data + secure tokens |
| **Push Notifications** | Firebase Cloud Messaging | Cross-platform |
| **Barcode Scanner** | Expo Barcode Scanner | Built-in, reliable |

### Project Structure

```
mobile-app/
├── app/                    # Expo Router (file-based routing)
│   ├── (auth)/            # Auth screens
│   │   ├── login.tsx
│   │   ├── register.tsx
│   │   └── forgot-password.tsx
│   ├── (tabs)/            # Main tab navigation
│   │   ├── index.tsx      # Home
│   │   ├── shop.tsx       # Product catalog
│   │   ├── cart.tsx       # Shopping cart
│   │   ├── loyalty.tsx    # Loyalty program
│   │   └── profile.tsx    # User profile
│   ├── product/
│   │   └── [id].tsx       # Product detail
│   ├── order/
│   │   ├── [id].tsx       # Order detail
│   │   └── history.tsx    # Order history
│   ├── checkout/
│   │   └── index.tsx      # Checkout flow
│   └── _layout.tsx        # Root layout
├── components/            # Reusable components
│   ├── ui/               # Base UI components
│   ├── product/          # Product-related components
│   ├── cart/             # Cart components
│   ├── loyalty/          # Loyalty components
│   └── common/           # Common components
├── services/             # API services
│   ├── api.ts            # Axios configuration
│   ├── auth.service.ts   # Authentication API
│   ├── product.service.ts# Product API
│   ├── cart.service.ts   # Cart API
│   ├── order.service.ts  # Order API
│   ├── loyalty.service.ts# Loyalty API
│   └── notification.service.ts
├── stores/               # Zustand stores
│   ├── auth.store.ts
│   ├── cart.store.ts
│   └── product.store.ts
├── hooks/                # Custom React hooks
│   ├── useAuth.ts
│   ├── useCart.ts
│   ├── useProducts.ts
│   └── useNotifications.ts
├── utils/                # Utility functions
│   ├── formatters.ts
│   ├── validators.ts
│   └── constants.ts
├── types/                # TypeScript types
│   ├── api.types.ts
│   ├── product.types.ts
│   ├── order.types.ts
│   └── loyalty.types.ts
├── assets/               # Images, fonts, icons
│   ├── images/
│   ├── fonts/
│   └── icons/
├── config/               # Configuration
│   ├── app.config.ts
│   └── env.ts
└── package.json
```

---

## 📡 API Integration

### Base URL Configuration

```typescript
// config/env.ts
const API_BASE_URL = process.env.EXPO_PUBLIC_API_URL || 'http://localhost:8000/api';
const API_TIMEOUT = 30000; // 30 seconds
```

### Authentication Flow

```
1. Login → POST /api/auth/login
2. Store tokens in SecureStore
3. Attach token to all requests via Axios interceptor
4. Handle token refresh via /api/auth/refresh
5. Logout → POST /api/auth/logout + clear storage
```

### API Endpoints Mapping

| Feature | Endpoint | Method | Backend Controller |
|---------|----------|--------|-------------------|
| **Auth** |
| Login | `/api/auth/login` | POST | AuthController |
| Register | `/api/auth/register` | POST | AuthController |
| Logout | `/api/auth/logout` | POST | AuthController |
| Refresh | `/api/auth/refresh` | POST | AuthController |
| Me | `/api/auth/me` | GET | AuthController |
| **Products** |
| List products | `/api/products` | GET | ProductController |
| Product detail | `/api/products/{id}` | GET | ProductController |
| Search | `/api/products/search` | GET | ProductController |
| Categories | `/api/categories` | GET | CategoryController |
| **Cart** |
| Get cart | `/api/cart` | GET | CartController |
| Add to cart | `/api/cart/add` | POST | CartController |
| Update cart | `/api/cart/update/{id}` | PUT | CartController |
| Remove from cart | `/api/cart/remove/{id}` | DELETE | CartController |
| Clear cart | `/api/cart/clear` | DELETE | CartController |
| **Orders** |
| Create order | `/api/orders` | POST | OrderController |
| Order list | `/api/orders` | GET | OrderController |
| Order detail | `/api/orders/{id}` | GET | OrderController |
| **Loyalty** |
| Points balance | `/api/loyalty/points` | GET | LoyaltyController |
| Tier status | `/api/loyalty/tier` | GET | TierController |
| Rewards | `/api/rewards` | GET | RewardController |
| Redeem reward | `/api/rewards/{id}/redeem` | POST | RewardController |
| **Notifications** |
| Register device | `/api/notifications/register-device` | POST | NotificationController |
| Get notifications | `/api/notifications` | GET | NotificationController |
| Mark as read | `/api/notifications/{id}/read` | PUT | NotificationController |

---

## 🎨 UI/UX Design System

### Color Palette

```typescript
// Theme colors based on existing web app
const colors = {
  primary: '#4F46E5',      // Indigo-600
  primaryDark: '#4338CA',  // Indigo-700
  primaryLight: '#818CF8', // Indigo-400
  secondary: '#10B981',    // Emerald-500
  accent: '#F59E0B',       // Amber-500
  danger: '#EF4444',       // Red-500
  warning: '#F59E0B',      // Amber-500
  success: '#10B981',      // Emerald-500
  info: '#3B82F6',         // Blue-500
  
  background: '#F9FAFB',   // Gray-50
  surface: '#FFFFFF',      // White
  border: '#E5E7EB',       // Gray-200
  
  text: {
    primary: '#111827',    // Gray-900
    secondary: '#6B7280',  // Gray-500
    disabled: '#9CA3AF',   // Gray-400
    inverse: '#FFFFFF',    // White
  },
};
```

### Typography

```typescript
const typography = {
  fontFamily: {
    regular: 'Inter-Regular',
    medium: 'Inter-Medium',
    semiBold: 'Inter-SemiBold',
    bold: 'Inter-Bold',
  },
  sizes: {
    xs: 12,
    sm: 14,
    base: 16,
    lg: 18,
    xl: 20,
    '2xl': 24,
    '3xl': 30,
  },
};
```

### Spacing Scale

```typescript
const spacing = {
  0: 0,
  1: 4,
  2: 8,
  3: 12,
  4: 16,
  5: 20,
  6: 24,
  8: 32,
  10: 40,
  12: 48,
  16: 64,
};
```

---

## 🔐 Security Considerations

### Token Storage
- Use **Expo SecureStore** for auth tokens (encrypted storage)
- Never store tokens in AsyncStorage
- Implement token refresh mechanism

### API Security
- HTTPS only in production
- CSRF token handling
- Request signing for sensitive operations
- Rate limiting awareness

### Data Protection
- Encrypt sensitive local data
- Biometric authentication support
- Auto-logout after inactivity
- Secure deep linking

---

## 📊 State Management (Zustand)

### Auth Store

```typescript
interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  
  login: (credentials: LoginCredentials) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => void;
  refreshToken: () => Promise<void>;
  updateUser: (user: Partial<User>) => void;
}
```

### Cart Store

```typescript
interface CartState {
  items: CartItem[];
  total: number;
  itemCount: number;
  isLoading: boolean;
  
  fetchCart: () => Promise<void>;
  addItem: (product: Product, quantity: number) => Promise<void>;
  updateQuantity: (itemId: string, quantity: number) => Promise<void>;
  removeItem: (itemId: string) => Promise<void>;
  clearCart: () => Promise<void>;
}
```

### Product Store

```typescript
interface ProductState {
  products: Product[];
  categories: Category[];
  selectedProduct: Product | null;
  isLoading: boolean;
  hasMore: boolean;
  
  fetchProducts: (params?: ProductParams) => Promise<void>;
  fetchProductById: (id: string) => Promise<Product>;
  fetchCategories: () => Promise<void>;
  searchProducts: (query: string) => Promise<Product[]>;
}
```

---

## 📱 Screen Specifications

### (auth)/login.tsx

**Purpose:** User authentication

**UI Components:**
- Logo/brand header
- Email input
- Password input
- Login button
- "Forgot password?" link
- "Register" link
- Social login buttons (optional)

**Logic:**
- Form validation
- API call to `/api/auth/login`
- Store tokens on success
- Navigate to home tab

---

### (auth)/register.tsx

**Purpose:** New user registration

**UI Components:**
- Logo/brand header
- Name input
- Email input
- Phone input (optional)
- Password input
- Confirm password input
- Terms & conditions checkbox
- Register button
- "Already have account?" link

**Logic:**
- Form validation
- Password strength check
- API call to `/api/auth/register`
- Auto-login on success

---

### (tabs)/index.tsx (Home)

**Purpose:** Main landing screen

**UI Components:**
- Header with search icon
- Welcome message
- Promotional banner carousel
- Category quick links
- Featured products grid
- Recent products section

**Logic:**
- Fetch featured products
- Fetch categories
- Personalized content based on user

---

### (tabs)/shop.tsx (Product Catalog)

**Purpose:** Browse all products

**UI Components:**
- Search bar at top
- Category filter chips
- Sort options (price, name, popularity)
- Product grid/list toggle
- Product cards (image, name, price, rating)
- Infinite scroll / pagination
- Filter bottom sheet

**Logic:**
- Fetch products with pagination
- Search functionality
- Category filtering
- Sort functionality

---

### product/[id].tsx (Product Detail)

**Purpose:** Detailed product view

**UI Components:**
- Product image gallery
- Product title
- Price (with discount if applicable)
- Rating & reviews count
- Stock status
- Quantity selector
- Add to cart button
- Product description
- Product specifications
- Related products

**Logic:**
- Fetch product details
- Add to cart functionality
- Share functionality
- Add to wishlist

---

### (tabs)/cart.tsx (Shopping Cart)

**Purpose:** View and manage cart

**UI Components:**
- Cart items list
- Quantity adjuster per item
- Remove item button
- Subtotal display
- Discount code input
- Checkout button
- Empty cart state
- Continue shopping button

**Logic:**
- Fetch cart items
- Update quantities
- Remove items
- Calculate totals
- Navigate to checkout

---

### checkout/index.tsx

**Purpose:** Complete purchase

**UI Components:**
- Order summary
- Delivery address form/selector
- Delivery method options
- Payment method selector
- Payment details form
- Apply discount code
- Place order button
- Terms checkbox

**Logic:**
- Form validation
- Payment processing
- Order creation
- Clear cart on success
- Navigate to order confirmation

---

### (tabs)/loyalty.tsx

**Purpose:** Loyalty program hub

**UI Components:**
- Points balance card
- Tier status badge
- QR membership card
- Points history
- Available rewards list
- Redeem reward button
- How to earn section

**Logic:**
- Fetch points balance
- Fetch tier status
- Generate/display QR code
- List rewards
- Handle reward redemption

---

### (tabs)/profile.tsx

**Purpose:** User account management

**UI Components:**
- Profile header (avatar, name, tier)
- Account settings
- Order history link
- Addresses management
- Payment methods
- Notification preferences
- App settings
- Logout button

**Logic:**
- Fetch user profile
- Update profile
- Manage preferences
- Handle logout

---

## 🔔 Push Notifications

### Firebase Setup

1. Create Firebase project
2. Add iOS & Android apps
3. Download config files:
   - `GoogleService-Info.plist` (iOS)
   - `google-services.json` (Android)
4. Configure Expo with Firebase credentials

### Notification Types

| Type | Trigger | Action |
|------|---------|--------|
| **Promotional** | New deals, sales | Deep link to product |
| **Points Alert** | Points earned/expiring | Deep link to loyalty |
| **Order Update** | Status change | Deep link to order |
| **Personalized** | Based on behavior | Deep link to recommendations |
| **Abandoned Cart** | Cart inactive > 24h | Deep link to cart |

### Implementation

```typescript
// Register for notifications
async function registerForPushNotifications() {
  const { status } = await Notifications.requestPermissionsAsync();
  if (status === 'granted') {
    const token = (await Notifications.getExpoPushTokenAsync()).data;
    await api.post('/notifications/register-device', { token });
  }
}

// Handle notification tap
Notifications.addNotificationResponseReceivedListener(response => {
  const data = response.notification.request.content.data;
  if (data.url) {
    router.push(data.url as any);
  }
});
```

---

## 📦 Dependencies

### Core Dependencies

```json
{
  "dependencies": {
    "expo": "~50.0.0",
    "expo-router": "~3.4.0",
    "react-native": "0.73.0",
    "react-native-safe-area-context": "4.8.2",
    "react-native-screens": "~3.29.0",
    "expo-status-bar": "~1.11.1",
    "expo-secure-store": "~12.8.0",
    "expo-device": "~5.9.0",
    "expo-notifications": "~0.27.0",
    "expo-barcode-scanner": "~12.9.0",
    "expo-location": "~16.5.0",
    "expo-image-picker": "~14.7.0",
    "expo-linking": "~6.2.2",
    "expo-constants": "~15.4.0",
    "expo-splash-screen": "~0.26.0",
    "expo-system-ui": "~2.9.0",
    "expo-web-browser": "~12.8.0",
    
    "axios": "^1.6.0",
    "zustand": "^4.5.0",
    "@react-native-async-storage/async-storage": "1.21.0",
    "react-native-gesture-handler": "~2.14.0",
    "react-native-reanimated": "~3.6.0",
    "react-native-svg": "14.1.0",
    "react-native-maps": "1.10.0",
    
    "@expo/vector-icons": "^14.0.0",
    "expo-font": "~11.10.0"
  },
  "devDependencies": {
    "@babel/core": "^7.23.0",
    "@types/react": "~18.2.0",
    "typescript": "^5.3.0"
  }
}
```

---

## 🧪 Testing Strategy

### Unit Tests
- Utility functions
- Formatters
- Validators
- Store actions

### Component Tests
- UI components rendering
- User interactions
- Props validation

### Integration Tests
- API service calls
- Store + component integration
- Navigation flows

### E2E Tests
- Login flow
- Product browsing
- Add to cart + checkout
- Loyalty redemption

---

## 📈 Performance Optimization

### Image Optimization
- Use WebP format
- Lazy loading
- Progressive images
- Cached images

### Network Optimization
- Request debouncing
- Response caching
- Optimistic updates
- Background sync

### Rendering Optimization
- Memoization (React.memo)
- Virtualized lists
- Code splitting
- Lazy loading screens

---

## 🚀 Deployment

### App Store Submission

**iOS (App Store):**
1. Create App Store Connect account
2. Create app record
3. Upload build via Xcode
4. Submit for review
5. Wait for approval (1-3 days)

**Android (Google Play):**
1. Create Google Play Console account
2. Create app listing
3. Upload APK/AAB
4. Submit for review
5. Wait for approval (1-2 days)

### OTA Updates (Expo)
- Use EAS Update for quick fixes
- No app store review needed
- Background updates

---

## 📋 Wave Breakdown

### Wave 1: Foundation (Week 1-2)
- [ ] Project setup
- [ ] Authentication screens
- [ ] Product catalog
- [ ] Product detail
- [ ] Basic navigation

### Wave 2: Shopping (Week 3-4)
- [ ] Shopping cart
- [ ] Checkout flow
- [ ] Order management
- [ ] Loyalty integration

### Wave 3: Engagement (Week 5-6)
- [ ] Push notifications
- [ ] Barcode scanner
- [ ] Store locator
- [ ] Order tracking

### Wave 4: Advanced (Week 7-8)
- [ ] Scan & go
- [ ] Recommendations
- [ ] Reviews & ratings
- [ ] App store submission

---

**Specification Complete**
**Ready for Implementation**
