# Phase 23 - Wave 2: Shopping Experience - Detailed Plan

**Date:** 2026-02-22
**Status:** `PLANNING`
**Milestone:** v2.1 — Mobile Experience
**Estimated Duration:** 5-7 days

---

## 📋 Wave 2 Overview

**Objective:** Build a complete shopping experience with product catalog, shopping cart, checkout flow, and payment integration.

**Deliverables:**
- Enhanced home screen with promotions
- Product catalog with search & filters
- Product detail page with gallery
- Shopping cart management
- Complete checkout flow
- Midtrans payment integration
- Order confirmation

---

## 🎯 Wave 2 Tasks Breakdown

### Task 2.1: Enhance Home Screen
**Duration:** 1 day  
**Priority:** HIGH

#### Subtasks:
1. **Welcome Header**
   - User greeting based on time
   - Loyalty tier badge display
   - Notification icon with badge

2. **Search Bar**
   - Prominent search input
   - Recent searches
   - Quick access to scan barcode

3. **Promotional Banners**
   - Carousel/slider component
   - Auto-scroll every 5 seconds
   - Manual navigation dots
   - Tap to view promotion details
   - Backend integration for dynamic banners

4. **Categories Grid**
   - Horizontal scroll or grid layout
   - Category icons/images
   - Category name
   - Navigation to filtered products

5. **Featured Products**
   - "Featured" section
   - Horizontal product carousel
   - Product card component
   - Add to cart quick action

6. **New Arrivals**
   - Latest products section
   - Sort by created_at
   - Limit to 10 items
   - "View All" link

7. **Best Sellers**
   - Top selling products
   - Based on order count
   - Product cards
   - "View All" link

#### Files to Create/Modify:
```
app/(tabs)/index.tsx              # Modify home screen
components/home/PromotionalBanner.tsx  # New
components/home/CategoriesGrid.tsx     # New
components/home/ProductSection.tsx     # New
services/home.service.ts               # New
```

#### API Endpoints Needed:
```
GET /api/mobile/home              # Home data (banners, featured, etc)
GET /api/mobile/categories        # Categories list
GET /api/mobile/products?featured=1 # Featured products
GET /api/mobile/products?sort=best_seller # Best sellers
```

---

### Task 2.2: Product Catalog Enhancement
**Duration:** 1.5 days  
**Priority:** HIGH

#### Subtasks:

1. **View Toggle**
   - Grid view (2 columns)
   - List view (1 column)
   - Persist user preference

2. **Search Functionality**
   - Search input with debounce
   - Search by product name
   - Search by SKU/barcode
   - Clear search button
   - Search results highlighting

3. **Category Filter**
   - Category dropdown/modal
   - "All Categories" option
   - Category count display
   - Active filter indicator

4. **Sort Options**
   - Sort by: Default, Price (Low-High), Price (High-Low), Name (A-Z), Newest
   - Sort dropdown/modal
   - Active sort indicator

5. **Price Range Filter**
   - Min-max price input
   - Price slider (optional)
   - Apply/Clear buttons

6. **Product Cards**
   - Product image
   - Product name (truncate long names)
   - Price (with discount if applicable)
   - Stock status badge
   - Add to cart button
   - Quick view option

7. **Infinite Scroll / Pagination**
   - Load more on scroll
   - Loading indicator
   - "No more products" message
   - Pull-to-refresh

8. **Empty State**
   - No products message
   - Clear filters button
   - Browse all link

#### Files to Create/Modify:
```
app/(tabs)/shop.tsx                    # Modify catalog screen
components/product/ProductCard.tsx     # Enhance
components/product/ProductGrid.tsx     # New
components/product/ProductList.tsx     # New
components/product/FilterModal.tsx     # New
components/product/SortModal.tsx       # New
components/product/SearchBar.tsx       # New
hooks/useProducts.ts                   # Enhance
stores/product.store.ts                # Enhance
```

#### API Endpoints Needed:
```
GET /api/mobile/products                      # Products list
GET /api/mobile/products?search=keyword       # Search products
GET /api/mobile/products?category_id=1        # Filter by category
GET /api/mobile/products?sort=price_asc       # Sort products
GET /api/mobile/products?min_price=10000      # Price filter
GET /api/mobile/categories                    # Categories for filter
```

---

### Task 2.3: Product Detail Page
**Duration:** 1 day  
**Priority:** HIGH

#### Subtasks:

1. **Image Gallery**
   - Main product image
   - Thumbnail carousel
   - Swipe to navigate images
   - Zoom on tap (optional)
   - Image counter (e.g., "1/5")

2. **Product Information**
   - Product name
   - SKU/Barcode
   - Product description (expandable)
   - Product specifications

3. **Pricing Display**
   - Current price (large)
   - Original price (strikethrough if discounted)
   - Discount percentage badge
   - Tax info

4. **Stock Status**
   - In stock / Out of stock badge
   - Available quantity
   - "Only X left" warning

5. **Unit Selector**
   - Available units (pcs, box, kg, etc)
   - Price per unit display
   - Unit conversion info

6. **Quantity Selector**
   - Stepper (- 1 +)
   - Direct input
   - Max stock validation
   - Add to cart button

7. **Add to Cart**
   - Primary CTA button
   - Stock validation
   - Success feedback
   - Cart count update
   - "Added" animation

8. **Related Products**
   - Products from same category
   - "Customers also bought"
   - Horizontal scroll
   - Add to cart from related

9. **Product Reviews (Optional)**
   - Average rating
   - Rating distribution
   - Review list
   - Write review button

#### Files to Create/Modify:
```
app/(tabs)/product/[id].tsx           # Enhance product detail
components/product/ProductGallery.tsx  # New
components/product/ProductInfo.tsx     # New
components/product/UnitSelector.tsx    # New
components/product/QuantityStepper.tsx # New
components/product/RelatedProducts.tsx # New
services/product.service.ts            # Enhance
```

#### API Endpoints Needed:
```
GET /api/mobile/products/{id}          # Product detail
GET /api/mobile/products/{id}/related  # Related products
POST /api/mobile/cart/add              # Add to cart
GET /api/mobile/products/{id}/reviews  # Product reviews (optional)
```

---

### Task 2.4: Shopping Cart
**Duration:** 1 day  
**Priority:** HIGH

#### Subtasks:

1. **Cart Items List**
   - Product image thumbnail
   - Product name
   - Selected unit
   - Quantity stepper
   - Item total price
   - Remove item button

2. **Quantity Management**
   - Increase/decrease quantity
   - Direct quantity input
   - Max stock validation
   - Update cart API call

3. **Remove Items**
   - Swipe to delete (iOS style)
   - Delete confirmation
   - Remove from cart API

4. **Cart Summary**
   - Subtotal
   - Tax/delivery fee (if applicable)
   - Discount (promo code)
   - Grand total

5. **Promo Code**
   - Input field
   - Apply button
   - Validation
   - Success/error message
   - Applied promo display

6. **Empty Cart State**
   - Empty cart illustration
   - "Your cart is empty" message
   - "Start Shopping" button
   - Navigate to shop

7. **Cart Persistence**
   - Save cart to AsyncStorage
   - Restore cart on app open
   - Sync with backend on login

8. **Stock Validation**
   - Check stock before checkout
   - Warn if stock changed
   - Remove unavailable items

#### Files to Create/Modify:
```
app/(tabs)/cart.tsx                   # Enhance cart screen
components/cart/CartItem.tsx          # Enhance
components/cart/CartSummary.tsx       # Enhance
components/cart/PromoCodeInput.tsx    # New
components/cart/EmptyCart.tsx         # New
stores/cart.store.ts                  # Enhance
services/cart.service.ts              # Enhance
```

#### API Endpoints Needed:
```
GET /api/mobile/cart                  # Get cart
PUT /api/mobile/cart/items/{id}       # Update cart item
DELETE /api/mobile/cart/items/{id}    # Remove item
POST /api/mobile/cart/add             # Add to cart
DELETE /api/mobile/cart/clear         # Clear cart
POST /api/mobile/cart/promo           # Apply promo code
```

---

### Task 2.5: Checkout Flow
**Duration:** 1.5 days  
**Priority:** HIGH

#### Subtasks:

**2.5.1: Delivery Address Screen**
1. Select saved address
2. Add new address
3. Edit address
4. Delivery instructions (textarea)
5. Set as default option
6. Next button

**2.5.2: Delivery Method Screen**
1. Store pickup option
   - Select store location
   - Pickup time slot
2. Home delivery option
   - Delivery fee calculation
   - Delivery time slot
3. Delivery instructions
4. Next button

**2.5.3: Payment Screen**
1. Payment method selection
   - Credit/Debit Card
   - Bank Transfer
   - GoPay
   - ShopeePay
2. Payment summary
3. Terms acceptance checkbox
4. Pay button

**2.5.4: Order Confirmation Screen**
1. Order summary
2. Items list
3. Delivery details
4. Payment details
5. Grand total
6. Place order button

#### Files to Create/Modify:
```
app/checkout/address.tsx              # New
app/checkout/delivery.tsx             # New
app/checkout/payment.tsx              # New
app/checkout/confirm.tsx              # New
components/checkout/AddressSelector.tsx  # New
components/checkout/DeliveryMethod.tsx   # New
components/checkout/PaymentMethod.tsx    # New
components/checkout/OrderSummary.tsx     # New
services/checkout.service.ts              # New
stores/checkout.store.ts                  # New
```

#### API Endpoints Needed:
```
GET /api/mobile/stores                # Get stores for pickup
POST /api/mobile/checkout             # Create order
GET /api/mobile/checkout/calculate    # Calculate delivery fee
```

---

### Task 2.6: Payment Integration
**Duration:** 1 day  
**Priority:** HIGH

#### Subtasks:

1. **Midtrans Snap Integration**
   - Install Midtrans SDK (if needed)
   - Configure Snap.js
   - Get Snap token from backend
   - Open Snap popup/webview

2. **Payment Flow**
   - Initiate payment
   - Open Midtrans Snap
   - Handle payment success
   - Handle payment pending
   - Handle payment failure
   - Handle payment close

3. **Payment Callbacks**
   - Success callback
   - Pending callback
   - Error callback
   - On close callback

4. **Payment Status Check**
   - Poll payment status
   - Update order status
   - Show receipt on success

5. **Error Handling**
   - Network errors
   - Payment gateway errors
   - Timeout handling
   - Retry mechanism

#### Files to Create/Modify:
```
app/checkout/payment.tsx              # Enhance
services/payment.service.ts           # New
components/payment/MidtransSnap.tsx   # New
config/midtrans.config.ts             # New
```

#### API Endpoints Needed:
```
POST /api/payments/initiate           # Get Snap token
POST /api/payments/callback/midtrans  # Webhook (backend)
GET /api/payments/status/{orderNumber} # Check payment status
```

---

### Task 2.7: Order Confirmation
**Duration:** 0.5 day  
**Priority:** HIGH

#### Subtasks:

1. **Order Success Screen**
   - Success animation/icon
   - Order number
   - Order summary
   - Estimated delivery time
   - Continue shopping button
   - View order details button

2. **Order Receipt**
   - Download receipt PDF
   - Share receipt
   - Email receipt option

3. **Order Tracking**
   - Track order button
   - Order status timeline
   - Expected delivery date

#### Files to Create/Modify:
```
app/checkout/success.tsx              # New
app/order/[id].tsx                    # Enhance
components/order/OrderTimeline.tsx    # New
```

#### API Endpoints Needed:
```
GET /api/mobile/orders/{orderNumber}  # Order detail
GET /api/mobile/orders/{orderNumber}/receipt # Download receipt
```

---

## 📊 Task Summary

| Task | Duration | Priority | Dependencies |
|------|----------|----------|--------------|
| 2.1: Home Screen | 1 day | HIGH | None |
| 2.2: Product Catalog | 1.5 days | HIGH | None |
| 2.3: Product Detail | 1 day | HIGH | 2.2 |
| 2.4: Shopping Cart | 1 day | HIGH | 2.2, 2.3 |
| 2.5: Checkout Flow | 1.5 days | HIGH | 2.4 |
| 2.6: Payment Integration | 1 day | HIGH | 2.5 |
| 2.7: Order Confirmation | 0.5 day | HIGH | 2.6 |

**Total Duration:** 7.5 days (rounded to 5-7 days with parallel work)

---

## 📁 Files to Create (Wave 2)

### New Components (15 files):
```
components/home/
  - PromotionalBanner.tsx
  - CategoriesGrid.tsx
  - ProductSection.tsx

components/product/
  - ProductGrid.tsx
  - ProductList.tsx
  - FilterModal.tsx
  - SortModal.tsx
  - SearchBar.tsx
  - ProductGallery.tsx
  - ProductInfo.tsx
  - UnitSelector.tsx
  - QuantityStepper.tsx
  - RelatedProducts.tsx

components/cart/
  - PromoCodeInput.tsx
  - EmptyCart.tsx

components/checkout/
  - AddressSelector.tsx
  - DeliveryMethod.tsx
  - PaymentMethod.tsx
  - OrderSummary.tsx

components/order/
  - OrderTimeline.tsx

components/payment/
  - MidtransSnap.tsx
```

### New Screens (5 files):
```
app/checkout/
  - address.tsx
  - delivery.tsx
  - payment.tsx
  - confirm.tsx
  - success.tsx
```

### New Services (3 files):
```
services/
  - home.service.ts
  - checkout.service.ts
  - payment.service.ts
```

### New Stores (1 file):
```
stores/
  - checkout.store.ts
```

### Configuration (1 file):
```
config/
  - midtrans.config.ts
```

**Total New Files:** ~25 files

---

## 🧪 Testing Checklist (Wave 2)

### Home Screen
- [ ] Welcome message displays
- [ ] Search bar functional
- [ ] Banners auto-scroll
- [ ] Categories navigate correctly
- [ ] Featured products load
- [ ] New arrivals load
- [ ] Best sellers load

### Product Catalog
- [ ] Grid/List toggle works
- [ ] Search filters products
- [ ] Category filter works
- [ ] Sort options work
- [ ] Price range filter works
- [ ] Infinite scroll works
- [ ] Pull-to-refresh works

### Product Detail
- [ ] Image gallery swipes
- [ ] Product info displays
- [ ] Price shows correctly
- [ ] Stock status accurate
- [ ] Unit selector works
- [ ] Quantity stepper works
- [ ] Add to cart works
- [ ] Related products load

### Shopping Cart
- [ ] Cart items display
- [ ] Quantity update works
- [ ] Remove item works
- [ ] Summary calculates correctly
- [ ] Promo code applies
- [ ] Empty state shows
- [ ] Cart persists

### Checkout
- [ ] Address selection works
- [ ] Add new address works
- [ ] Delivery method selection
- [ ] Payment method selection
- [ ] Order summary accurate
- [ ] Place order creates order

### Payment
- [ ] Midtrans Snap opens
- [ ] Payment success handled
- [ ] Payment failure handled
- [ ] Payment pending handled
- [ ] Order status updates

### Order Confirmation
- [ ] Success screen shows
- [ ] Order number displays
- [ ] Receipt download works
- [ ] Track order navigates

---

## ⚠️ Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Midtrans integration issues | HIGH | Test in sandbox first, have backup payment method |
| Performance with large product catalog | MEDIUM | Implement pagination, lazy loading, image optimization |
| Cart sync issues | MEDIUM | Implement offline-first with sync queue |
| Payment webhook failures | HIGH | Implement polling fallback, manual status check |
| API compatibility issues | MEDIUM | Version APIs, test with backend team |

---

## 📈 Success Criteria

- [ ] All 7 tasks completed
- [ ] All 25 files created
- [ ] All testing checklist items pass
- [ ] No critical bugs
- [ ] Performance acceptable (< 3s page loads)
- [ ] Payment success rate > 95%
- [ ] Cart abandonment rate < 70%

---

**Wave 2 Plan - READY FOR IMPLEMENTATION**

**Next Step:** Begin Task 2.1 (Home Screen Enhancement)

---

*Phase 23 Wave 2 Detailed Plan - Generated 2026-02-22*
