# Phase 23: Mobile App Development (React Native)

**Date:** 2026-02-22
**Status:** `PLANNING` → `IMPLEMENTING`
**Milestone:** v2.1 — Mobile Experience
**Priority:** HIGH
**Selected Option:** Option A - Customer-Facing Mobile App

---

## 📋 Vision

Build a customer-facing mobile application that provides seamless shopping experience, loyalty program access, and engagement features to increase customer retention and drive sales.

---

## 🎯 Goals

### Wave 1: App Foundation & Authentication
**Objective:** React Native app setup with authentication

**Deliverables:**
- React Native project setup (Expo)
- Navigation structure (tabs, stack)
- Authentication screens (login, register, forgot password)
- Profile management
- API integration layer
- State management (Zustand)

**Timeline:** 3-4 days

---

### Wave 2: Shopping Experience
**Objective:** Complete shopping flow

**Deliverables:**
- Product catalog browsing
- Product search & filters
- Product detail pages
- Shopping cart
- Checkout flow
- Payment integration (Midtrans)
- Order confirmation
- Order history

**Timeline:** 5-7 days

---

### Wave 3: Loyalty & Engagement
**Objective:** Loyalty integration & push notifications

**Deliverables:**
- Loyalty dashboard
- Points balance & history
- QR membership card
- Tier status display
- Push notifications (FCM)
- Notification preferences
- Rewards catalog
- Reward redemption

**Timeline:** 3-4 days

---

### Wave 4: Advanced Features
**Objective:** Premium features

**Deliverables:**
- Barcode scanner
- Store locator (maps)
- Order tracking
- Digital receipts
- Wishlist
- Product reviews
- Social sharing
- Scan & Go

**Timeline:** 4-5 days

---

## 🗄️ Technical Architecture

### Tech Stack

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Framework** | React Native (Expo) | Cross-platform development |
| **Navigation** | React Navigation | Stack, tab, drawer navigation |
| **State Management** | Zustand | Global state management |
| **API Client** | Axios | HTTP requests |
| **Push Notifications** | Firebase Cloud Messaging | Push notifications |
| **Storage** | AsyncStorage | Local data persistence |
| **Maps** | react-native-maps | Store locator |
| **Camera** | expo-camera | Barcode scanner |
| **UI Library** | NativeBase / React Native Paper | Pre-built components |

### Project Structure

```
mobile-app/
├── app/
│   ├── (auth)/
│   │   ├── login.tsx
│   │   ├── register.tsx
│   │   └── forgot-password.tsx
│   ├── (tabs)/
│   │   ├── index.tsx (Home)
│   │   ├── shop.tsx (Catalog)
│   │   ├── cart.tsx (Cart)
│   │   ├── loyalty.tsx (Rewards)
│   │   └── profile.tsx (Profile)
│   ├── product/
│   │   └── [id].tsx (Product Detail)
│   ├── order/
│   │   ├── history.tsx
│   │   └── [id].tsx (Order Detail)
│   └── _layout.tsx (Root Layout)
│
├── components/
│   ├── ui/ (Reusable UI components)
│   ├── product/ (Product-related components)
│   ├── cart/ (Cart components)
│   └── loyalty/ (Loyalty components)
│
├── services/
│   ├── api.ts (API client)
│   ├── auth.service.ts
│   ├── product.service.ts
│   ├── cart.service.ts
│   ├── order.service.ts
│   ├── loyalty.service.ts
│   └── notification.service.ts
│
├── stores/
│   ├── auth.store.ts
│   ├── cart.store.ts
│   ├── product.store.ts
│   └── loyalty.store.ts
│
├── hooks/
│   ├── useAuth.ts
│   ├── useCart.ts
│   └── useProducts.ts
│
├── utils/
│   ├── formatters.ts
│   ├── validators.ts
│   └── constants.ts
│
├── assets/
│   ├── images/
│   ├── fonts/
│   └── icons/
│
└── config/
    ├── api.config.ts
    ├── app.config.ts
    └── firebase.config.ts
```

### API Integration

**Existing Backend APIs (Phase 20):**
```
Authentication:
  POST /api/mobile/login
  POST /api/mobile/register
  POST /api/mobile/logout

Products:
  GET /api/mobile/home
  GET /api/mobile/products
  GET /api/mobile/products/{id}
  GET /api/mobile/categories

Cart:
  GET /api/mobile/cart
  POST /api/mobile/cart/add
  PUT /api/mobile/cart/items/{id}
  DELETE /api/mobile/cart/items/{id}

Checkout:
  POST /api/mobile/checkout

Orders:
  GET /api/mobile/orders
  GET /api/mobile/orders/{orderNumber}

Loyalty:
  GET /api/mobile/loyalty/summary
  GET /api/mobile/loyalty/rewards
  POST /api/mobile/loyalty/redeem

Notifications:
  POST /api/mobile/notifications/register-device
  GET /api/mobile/notifications/preferences
```

---

## 📱 App Features

### Authentication Flow

**Login:**
- Email/password login
- Remember me
- Forgot password
- Social login (optional)

**Registration:**
- Email, password, name
- Phone number (optional)
- Terms acceptance
- Email verification

**Profile:**
- View/edit profile
- Change password
- Notification preferences
- Saved addresses

---

### Home Screen

**Sections:**
- Welcome message
- Search bar
- Promotional banners
- Featured products
- Categories
- New arrivals
- Best sellers

---

### Product Catalog

**Features:**
- Grid/List view toggle
- Category filter
- Search functionality
- Sort options (price, name, popularity)
- Product quick view
- Add to cart from list

---

### Product Detail

**Information:**
- Product images (gallery)
- Name and description
- Price and discounts
- Stock status
- Unit selector
- Quantity selector
- Add to cart button
- Related products
- Reviews & ratings

---

### Shopping Cart

**Features:**
- Cart item list
- Quantity adjustment
- Remove items
- Price summary (subtotal, tax, total)
- Promo code input
- Proceed to checkout

---

### Checkout Flow

**Steps:**
1. **Delivery Address**
   - Select saved address
   - Add new address
   - Delivery instructions

2. **Delivery Method**
   - Store pickup
   - Home delivery
   - Delivery time slot

3. **Payment**
   - Payment method selection
   - Midtrans integration
   - Payment confirmation

4. **Review & Confirm**
   - Order summary
   - Apply promo code
   - Place order

---

### Order Management

**Order History:**
- List of all orders
- Filter by status
- Search orders
- Order details

**Order Status:**
- Pending
- Confirmed
- Processing
- Shipped
- Out for delivery
- Delivered
- Cancelled

**Order Actions:**
- Track order
- Cancel order (if allowed)
- Reorder
- Download receipt
- Rate products

---

### Loyalty Program

**Loyalty Dashboard:**
- Points balance
- Tier status & badge
- Progress to next tier
- Available rewards
- Points history

**QR Membership Card:**
- Digital membership card
- QR code for in-store scanning
- Tier badge display
- Member since date

**Rewards Catalog:**
- Available rewards
- Points required
- Reward details
- Redeem reward
- Redemption history

---

### Push Notifications

**Notification Types:**
- Order updates
- Payment confirmations
- Promotional offers
- Points expiry reminders
- New rewards
- Personalized recommendations

**Notification Settings:**
- Order notifications (on/off)
- Promotional notifications (on/off)
- Points reminders (on/off)

---

### Advanced Features (Wave 4)

**Barcode Scanner:**
- Scan product barcodes
- Quick product lookup
- Price check
- Add to cart from scan

**Store Locator:**
- Map view of stores
- Store details
- Opening hours
- Contact information
- Get directions

**Wishlist:**
- Save favorite products
- Move to cart
- Price drop alerts
- Share wishlist

**Product Reviews:**
- Rate products (1-5 stars)
- Write review
- Upload photos
- View other reviews

**Scan & Go:**
- Scan products while shopping
- Build cart in-store
- Self-checkout
- Digital receipt

---

## 📊 Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| App Downloads | 1,000+ (first month) | App Store / Play Store |
| Daily Active Users | 30% of downloads | Analytics |
| Session Duration | > 3 minutes | Analytics |
| Cart Conversion | > 3% | Checkout completion |
| Push Notification Open Rate | > 20% | FCM Analytics |
| App Store Rating | > 4.5 stars | Store reviews |
| Crash-free Sessions | > 99% | Crashlytics |

---

## ⚠️ Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| App store rejection | High | Follow guidelines, test thoroughly |
| Performance issues | Medium | Optimize images, lazy loading, code splitting |
| Low adoption | Medium | Marketing, incentives, referral program |
| Security concerns | High | Encryption, secure auth, compliance |
| API compatibility | Medium | Version APIs, backward compatibility |
| Push notification delivery | Medium | FCM fallback, email backup |

---

## 📁 Files to Create

### Mobile App Structure:
```
mobile-app/
├── app.json (Expo config)
├── package.json
├── tsconfig.json
├── babel.config.js
├── eas.json (Build config)
│
├── app/
│   ├── (auth)/
│   │   ├── _layout.tsx
│   │   ├── login.tsx
│   │   ├── register.tsx
│   │   └── forgot-password.tsx
│   ├── (tabs)/
│   │   ├── _layout.tsx
│   │   ├── index.tsx (Home)
│   │   ├── shop.tsx
│   │   ├── cart.tsx
│   │   ├── loyalty.tsx
│   │   └── profile.tsx
│   ├── product/
│   │   └── [id].tsx
│   ├── order/
│   │   ├── history.tsx
│   │   └── [id].tsx
│   ├── checkout/
│   │   ├── address.tsx
│   │   ├── delivery.tsx
│   │   ├── payment.tsx
│   │   └── confirm.tsx
│   └── _layout.tsx
│
├── components/
│   ├── ui/
│   │   ├── Button.tsx
│   │   ├── Input.tsx
│   │   ├── Card.tsx
│   │   └── Loading.tsx
│   ├── product/
│   │   ├── ProductCard.tsx
│   │   ├── ProductList.tsx
│   │   └── ProductGallery.tsx
│   └── cart/
│       ├── CartItem.tsx
│       └── CartSummary.tsx
│
├── services/
│   ├── api.ts
│   ├── auth.service.ts
│   ├── product.service.ts
│   ├── cart.service.ts
│   ├── order.service.ts
│   └── loyalty.service.ts
│
├── stores/
│   ├── auth.store.ts
│   ├── cart.store.ts
│   └── product.store.ts
│
└── utils/
    ├── formatters.ts
    └── constants.ts
```

---

## ✅ Acceptance Criteria

### Wave 1 Criteria:
- [ ] Expo project initialized
- [ ] Navigation structure working
- [ ] Login/register functional
- [ ] API integration layer configured
- [ ] State management setup
- [ ] Profile screen complete

### Wave 2 Criteria:
- [ ] Product catalog displays
- [ ] Search and filters work
- [ ] Product detail complete
- [ ] Cart management functional
- [ ] Checkout flow complete
- [ ] Payment integration working
- [ ] Order history displays

### Wave 3 Criteria:
- [ ] Loyalty dashboard shows data
- [ ] QR card displays
- [ ] Push notifications configured
- [ ] Rewards catalog functional
- [ ] Reward redemption works

### Wave 4 Criteria:
- [ ] Barcode scanner functional
- [ ] Store locator displays map
- [ ] Wishlist management works
- [ ] Product reviews submit
- [ ] Scan & Go flow complete

---

## 🚀 Implementation Plan

### Week 1: Wave 1 - Foundation
- Day 1: Project setup, dependencies
- Day 2: Navigation structure
- Day 3: Authentication screens
- Day 4: API integration
- Day 5: Testing & refinement

### Week 2: Wave 2 - Shopping (Part 1)
- Day 1: Home screen
- Day 2: Product catalog
- Day 3: Product detail
- Day 4: Shopping cart
- Day 5: Cart state management

### Week 3: Wave 2 - Shopping (Part 2)
- Day 1: Checkout flow (address)
- Day 2: Checkout flow (payment)
- Day 3: Midtrans integration
- Day 4: Order confirmation
- Day 5: Order history

### Week 4: Wave 3 - Loyalty & Notifications
- Day 1: Loyalty dashboard
- Day 2: QR card & tier display
- Day 3: Push notifications (FCM)
- Day 4: Rewards catalog
- Day 5: Testing

### Week 5: Wave 4 - Advanced Features
- Day 1-2: Barcode scanner
- Day 3: Store locator
- Day 4: Wishlist & reviews
- Day 5: Polish & testing

---

## 🧪 Testing Strategy

### Unit Testing
- Component rendering
- Utility functions
- Store actions
- API service methods

### Integration Testing
- API integration
- Navigation flows
- State management
- Payment flow

### E2E Testing
- Login to checkout flow
- Product search to purchase
- Loyalty redemption
- Push notification handling

### Device Testing
- iOS (iPhone 12, 13, 14, 15)
- Android (various screen sizes)
- Tablet compatibility

---

## 📱 App Store Submission

### iOS App Store
- Apple Developer account
- App Store Connect setup
- App icons and screenshots
- Privacy policy
- Terms of service
- App review guidelines compliance

### Google Play Store
- Google Play Console account
- Store listing
- App icons and screenshots
- Content rating
- Privacy policy
- Play Store guidelines compliance

---

**Phase 23 Specification - READY FOR IMPLEMENTATION**
**Selected Option:** Option A - Mobile App Development
**Estimated Timeline:** 4-5 weeks
