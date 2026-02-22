// API Response Types
export interface ApiResponse<T = any> {
  success: boolean;
  data: T;
  message?: string;
}

export interface PaginatedResponse<T = any> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}

// User Types
export interface User {
  id: string;
  name: string;
  email: string;
  phone?: string;
  avatar?: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

// Auth Types
export interface LoginCredentials {
  email: string;
  password: string;
  device_name?: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
}

export interface AuthTokens {
  access_token: string;
  token_type: string;
  expires_in?: number;
}

export interface AuthResponse extends AuthTokens {
  user: User;
}

// Product Types
export interface Product {
  id: string;
  name: string;
  slug: string;
  sku?: string;
  description?: string;
  price: number;
  sale_price?: number;
  cost?: number;
  stock: number;
  min_stock?: number;
  category_id?: string;
  category?: Category;
  images?: ProductImage[];
  unit?: string;
  status: 'active' | 'inactive';
  is_featured?: boolean;
  rating?: number;
  reviews_count?: number;
  created_at: string;
  updated_at: string;
}

export interface ProductImage {
  id: string;
  product_id: string;
  url: string;
  is_primary: boolean;
  order: number;
}

export interface Category {
  id: string;
  name: string;
  slug: string;
  description?: string;
  parent_id?: string;
  image?: string;
  order: number;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

export interface ProductParams {
  page?: number;
  limit?: number;
  category_id?: string;
  search?: string;
  sort?: 'price_asc' | 'price_desc' | 'name' | 'newest' | 'popular';
  min_price?: number;
  max_price?: number;
}

// Cart Types
export interface Cart {
  id: string;
  user_id: string;
  items: CartItem[];
  subtotal: number;
  discount: number;
  tax: number;
  total: number;
  created_at: string;
  updated_at: string;
}

export interface CartItem {
  id: string;
  cart_id: string;
  product_id: string;
  product: Product;
  quantity: number;
  price: number;
  subtotal: number;
  created_at: string;
  updated_at: string;
}

export interface AddToCartData {
  product_id: string;
  quantity: number;
}

export interface UpdateCartData {
  quantity: number;
}

// Order Types
export interface Order {
  id: string;
  order_number: string;
  user_id: string;
  user?: User;
  status: OrderStatus;
  items: OrderItem[];
  subtotal: number;
  discount: number;
  tax: number;
  shipping_fee: number;
  total: number;
  payment_method?: string;
  payment_status: PaymentStatus;
  shipping_address?: Address;
  billing_address?: Address;
  notes?: string;
  shipped_at?: string;
  completed_at?: string;
  cancelled_at?: string;
  created_at: string;
  updated_at: string;
}

export type OrderStatus = 'pending' | 'confirmed' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded';
export type PaymentStatus = 'pending' | 'paid' | 'failed' | 'refunded';

export interface OrderItem {
  id: string;
  order_id: string;
  product_id: string;
  product?: Product;
  quantity: number;
  price: number;
  subtotal: number;
  created_at: string;
  updated_at: string;
}

export interface CreateOrderData {
  shipping_address_id?: string;
  billing_address_id?: string;
  shipping_address?: Address;
  billing_address?: Address;
  payment_method: string;
  shipping_method?: string;
  notes?: string;
  discount_code?: string;
}

export interface Address {
  id?: string;
  name: string;
  phone: string;
  address_line1: string;
  address_line2?: string;
  city: string;
  state?: string;
  postal_code?: string;
  country: string;
  is_default?: boolean;
}

// Loyalty Types
export interface LoyaltyPoints {
  id: string;
  user_id: string;
  points: number;
  lifetime_points: number;
  redeemed_points: number;
  expires_at?: string;
  created_at: string;
  updated_at: string;
}

export interface MembershipTier {
  id: string;
  name: string;
  slug: string;
  min_points: number;
  max_points?: number;
  discount_percentage?: number;
  benefits: string[];
  icon?: string;
  color?: string;
  order: number;
}

export interface CustomerTier {
  id: string;
  user_id: string;
  tier_id: string;
  tier: MembershipTier;
  points: number;
  next_tier_id?: string;
  points_to_next_tier?: number;
  assessed_at: string;
  created_at: string;
  updated_at: string;
}

export interface Reward {
  id: string;
  name: string;
  slug: string;
  description?: string;
  points_required: number;
  quantity?: number;
  redeemed_count: number;
  starts_at: string;
  expires_at?: string;
  status: 'active' | 'inactive';
  image?: string;
  created_at: string;
  updated_at: string;
}

export interface CustomerReward {
  id: string;
  user_id: string;
  reward_id: string;
  reward: Reward;
  status: 'pending' | 'redeemed' | 'used' | 'expired';
  redeemed_at: string;
  used_at?: string;
  expires_at?: string;
  created_at: string;
  updated_at: string;
}

export interface PointsHistory {
  id: string;
  user_id: string;
  points: number;
  type: 'earn' | 'redeem' | 'adjustment' | 'expiry';
  description: string;
  reference_type?: string;
  reference_id?: string;
  balance_after: number;
  created_at: string;
}

// Notification Types
export interface Notification {
  id: string;
  type: string;
  title: string;
  message: string;
  data?: Record<string, any>;
  read_at?: string;
  created_at: string;
}

export interface RegisterDeviceData {
  token: string;
  platform: 'ios' | 'android';
  device_name?: string;
}

// Store Types
export interface Store {
  id: string;
  name: string;
  slug: string;
  description?: string;
  address: string;
  city: string;
  state?: string;
  postal_code?: string;
  phone: string;
  email?: string;
  latitude?: number;
  longitude?: number;
  opening_hours?: StoreHours;
  image?: string;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

export interface StoreHours {
  monday?: { open: string; close: string; is_closed: boolean };
  tuesday?: { open: string; close: string; is_closed: boolean };
  wednesday?: { open: string; close: string; is_closed: boolean };
  thursday?: { open: string; close: string; is_closed: boolean };
  friday?: { open: string; close: string; is_closed: boolean };
  saturday?: { open: string; close: string; is_closed: boolean };
  sunday?: { open: string; close: string; is_closed: boolean };
}
