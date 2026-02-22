# Phase 23 - Wave 1 Summary: Mobile App Foundation

**Date:** 2026-02-22
**Status:** ✅ EXISTING FOUNDATION - READY FOR ENHANCEMENT
**Milestone:** v2.1 — Mobile Experience

---

## 📋 Objective

Leverage existing Phase 20 mobile app foundation and enhance it with complete authentication, improved navigation, and production-ready setup.

---

## ✅ Existing Foundation (from Phase 20)

### Project Structure ✅
```
mobile-app/
├── app/
│   ├── (auth)/           # Authentication screens
│   ├── (tabs)/           # Tab navigation
│   ├── checkout/         # Checkout flow
│   ├── order/            # Order management
│   └── _layout.tsx       # Root layout
├── components/           # Reusable components
├── services/            # API services
├── stores/             # Zustand stores
├── hooks/              # Custom hooks
├── utils/              # Utilities
└── config/             # Configuration
```

### Dependencies Installed ✅
```json
{
  "expo": "~54.0.33",
  "expo-router": "~6.0.23",
  "expo-notifications": "^0.32.16",
  "expo-location": "^19.0.8",
  "expo-barcode-scanner": "^13.0.1",
  "react-native-maps": "^1.27.1",
  "zustand": "^5.0.11",
  "axios": "^1.13.5"
}
```

### Existing Screens ✅
- **Home** (`(tabs)/index.tsx`)
- **Shop/Catalog** (`(tabs)/shop.tsx`)
- **Cart** (`(tabs)/cart.tsx`)
- **Loyalty** (`(tabs)/loyalty.tsx`)
- **Profile** (`(tabs)/profile.tsx`)
- **Product Detail** (`(tabs)/product/[id].tsx`)
- **Login/Register** (`(auth)/`)
- **Checkout** (`checkout/`)
- **Order History** (`order/`)

### Existing Services ✅
- `api.ts` - API client
- `auth.service.ts` - Authentication
- `product.service.ts` - Products
- `cart.service.ts` - Shopping cart
- `loyalty.service.ts` - Loyalty program

### Existing Stores ✅
- `auth.store.ts` - Authentication state
- `cart.store.ts` - Cart state
- `product.store.ts` - Product state

---

## 🔧 Wave 1 Enhancements (Phase 23)

### 1. Update app.json for Production

**Add push notification configuration:**
```json
{
  "expo": {
    "name": "SAGA POS",
    "slug": "saga-pos",
    "version": "2.1.0",
    "orientation": "portrait",
    "icon": "./assets/images/icon.png",
    "scheme": "sagapos",
    "userInterfaceStyle": "automatic",
    "newArchEnabled": true,
    "splash": {
      "image": "./assets/images/splash-icon.png",
      "resizeMode": "contain",
      "backgroundColor": "#3b82f6"
    },
    "ios": {
      "supportsTablet": true,
      "bundleIdentifier": "com.sagaposo.mobileapp",
      "infoPlist": {
        "NSCameraUsageDescription": "Scan product barcodes",
        "NSLocationWhenInUseUsageDescription": "Find nearby stores"
      }
    },
    "android": {
      "adaptiveIcon": {
        "foregroundImage": "./assets/images/adaptive-icon.png",
        "backgroundColor": "#3b82f6"
      },
      "edgeToEdgeEnabled": true,
      "package": "com.sagaposo.mobileapp",
      "permissions": [
        "CAMERA",
        "ACCESS_FINE_LOCATION",
        "ACCESS_COARSE_LOCATION"
      ]
    },
    "web": {
      "bundler": "metro",
      "output": "static",
      "favicon": "./assets/images/favicon.png"
    },
    "plugins": [
      "expo-router",
      [
        "expo-notifications",
        {
          "icon": "./assets/images/notification-icon.png",
          "color": "#3b82f6"
        }
      ]
    ],
    "experiments": {
      "typedRoutes": true
    }
  }
}
```

### 2. Update API Configuration

**File:** `config/api.config.ts`

```typescript
export const API_CONFIG = {
  // Development
  // baseUrl: 'http://192.168.1.100:8000/api',
  
  // Production - Update with actual URL
  baseUrl: 'https://your-domain.com/api',
  
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
};

export const API_ENDPOINTS = {
  // Authentication
  LOGIN: '/mobile/login',
  REGISTER: '/mobile/register',
  LOGOUT: '/mobile/logout',
  
  // Products
  HOME: '/mobile/home',
  PRODUCTS: '/mobile/products',
  PRODUCT_DETAIL: '/mobile/products/:id',
  CATEGORIES: '/mobile/categories',
  
  // Cart
  CART: '/mobile/cart',
  CART_ADD: '/mobile/cart/add',
  CART_UPDATE: '/mobile/cart/items/:id',
  CART_REMOVE: '/mobile/cart/items/:id',
  CART_CLEAR: '/mobile/cart/clear',
  
  // Checkout
  CHECKOUT: '/mobile/checkout',
  
  // Orders
  ORDERS: '/mobile/orders',
  ORDER_DETAIL: '/mobile/orders/:orderNumber',
  
  // Loyalty
  LOYALTY_SUMMARY: '/mobile/loyalty/summary',
  LOYALTY_REWARDS: '/mobile/loyalty/rewards',
  LOYALTY_REDEEM: '/mobile/loyalty/redeem',
  LOYALTY_QR: '/mobile/loyalty/qr-code',
  
  // Notifications
  NOTIFICATIONS_REGISTER: '/mobile/notifications/register-device',
  NOTIFICATIONS_PREFERENCES: '/mobile/notifications/preferences',
  
  // Stores
  STORES: '/mobile/stores',
  STORES_NEARBY: '/mobile/stores/nearby',
  
  // Utilities
  SCAN: '/mobile/scan',
  SETTINGS: '/mobile/settings',
};
```

### 3. Enhance API Service

**File:** `services/api.ts`

```typescript
import axios from 'axios';
import { API_CONFIG, API_ENDPOINTS } from '../config/api.config';
import { useAuthStore } from '../stores/auth.store';

// Create axios instance
const apiClient = axios.create({
  baseURL: API_CONFIG.baseUrl,
  timeout: API_CONFIG.timeout,
  headers: API_CONFIG.headers,
});

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    const token = useAuthStore.getState().token;
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor
apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired, logout
      useAuthStore.getState().logout();
    }
    return Promise.reject(error);
  }
);

export { apiClient, API_ENDPOINTS };
```

### 4. Update Auth Store

**File:** `stores/auth.store.ts`

```typescript
import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import * as AuthService from '../services/auth.service';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  tenant_id?: number;
}

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => void;
  updateUser: (user: Partial<User>) => void;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      isAuthenticated: false,
      isLoading: false,

      login: async (email: string, password: string) => {
        set({ isLoading: true });
        try {
          const response = await AuthService.login(email, password);
          set({
            user: response.user,
            token: response.token,
            isAuthenticated: true,
            isLoading: false,
          });
        } catch (error) {
          set({ isLoading: false });
          throw error;
        }
      },

      register: async (data: RegisterData) => {
        set({ isLoading: true });
        try {
          const response = await AuthService.register(data);
          set({
            user: response.user,
            token: response.token,
            isAuthenticated: true,
            isLoading: false,
          });
        } catch (error) {
          set({ isLoading: false });
          throw error;
        }
      },

      logout: () => {
        AuthService.logout();
        set({
          user: null,
          token: null,
          isAuthenticated: false,
        });
      },

      updateUser: (user: Partial<User>) => {
        const currentUser = get().user;
        if (currentUser) {
          set({ user: { ...currentUser, ...user } });
        }
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({
        user: state.user,
        token: state.token,
        isAuthenticated: state.isAuthenticated,
      }),
    }
  )
);
```

### 5. Create Firebase Configuration

**File:** `config/firebase.config.ts`

```typescript
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';

// Firebase configuration
const firebaseConfig = {
  apiKey: process.env.EXPO_PUBLIC_FIREBASE_API_KEY,
  authDomain: process.env.EXPO_PUBLIC_FIREBASE_AUTH_DOMAIN,
  projectId: process.env.EXPO_PUBLIC_FIREBASE_PROJECT_ID,
  storageBucket: process.env.EXPO_PUBLIC_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: process.env.EXPO_PUBLIC_FIREBASE_MESSAGING_SENDER_ID,
  appId: process.env.EXPO_PUBLIC_FIREBASE_APP_ID,
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
export const messaging = getMessaging(app);

// Get FCM token
export const getFCMToken = async (): Promise<string | null> => {
  try {
    const token = await getToken(messaging, {
      vapidKey: process.env.EXPO_PUBLIC_FIREBASE_VAPID_KEY,
    });
    return token;
  } catch (error) {
    console.error('Error getting FCM token:', error);
    return null;
  }
};

// Listen for foreground messages
export const onForegroundMessage = (callback: (payload: any) => void) => {
  return onMessage(messaging, (payload) => {
    callback(payload);
  });
};
```

### 6. Create Environment File

**File:** `.env`

```env
# API Configuration
EXPO_PUBLIC_API_URL=https://your-domain.com/api

# Firebase Configuration (for push notifications)
EXPO_PUBLIC_FIREBASE_API_KEY=your_api_key
EXPO_PUBLIC_FIREBASE_AUTH_DOMAIN=your_project.firebaseapp.com
EXPO_PUBLIC_FIREBASE_PROJECT_ID=your_project_id
EXPO_PUBLIC_FIREBASE_STORAGE_BUCKET=your_project.appspot.com
EXPO_PUBLIC_FIREBASE_MESSAGING_SENDER_ID=your_sender_id
EXPO_PUBLIC_FIREBASE_APP_ID=your_app_id
EXPO_PUBLIC_FIREBASE_VAPID_KEY=your_vapid_key

# Midtrans (for payment)
EXPO_PUBLIC_MIDTRANS_CLIENT_KEY=your_midtrans_client_key
EXPO_PUBLIC_MIDTRANS_IS_PRODUCTION=false

# Mapbox/Google Maps (for store locator)
EXPO_PUBLIC_MAPS_API_KEY=your_maps_api_key
```

---

## 📊 Wave 1 Status

### What Exists (Phase 20) ✅
- Project structure
- Dependencies installed
- Basic screens created
- API services configured
- State management setup

### What Needs Enhancement (Phase 23 Wave 1) 🔧
- [ ] Update app.json with production config
- [ ] Update API endpoints to match Phase 22 backend
- [ ] Enhance error handling
- [ ] Add loading states
- [ ] Add pull-to-refresh
- [ ] Add offline support
- [ ] Configure push notifications
- [ ] Add biometric authentication (optional)

---

## 🚀 Next Steps (Wave 2)

After completing Wave 1 enhancements, proceed to Wave 2: Shopping Experience

1. **Enhance Home Screen**
   - Add promotional banners
   - Featured products section
   - Categories grid

2. **Improve Product Catalog**
   - Advanced filters
   - Sort options
   - Infinite scroll

3. **Enhance Product Detail**
   - Image gallery
   - Related products
   - Reviews section

4. **Shopping Cart Improvements**
   - Cart persistence
   - Promo codes
   - Saved for later

---

**Wave 1 Status:** ✅ FOUNDATION READY - ENHANCEMENTS IN PROGRESS
**Ready for:** Wave 2 Implementation (Shopping Experience)

---

*Phase 23 Wave 1 Summary - Generated 2026-02-22*
