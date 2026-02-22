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
  
  // Payments (Phase 23)
  PAYMENTS_INITIATE: '/payments/initiate',
  PAYMENTS_STATUS: '/payments/status/{orderNumber}',
  PAYMENTS_CANCEL: '/payments/cancel',
  PAYMENTS_CALLBACK: '/payments/callback/midtrans',
};
