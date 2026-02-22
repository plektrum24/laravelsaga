import api from './api';
import { Cart, CartItem, AddToCartData, UpdateCartData } from '../types/api.types';

export const cartService = {
  /**
   * Get current user's cart
   */
  async getCart(): Promise<Cart> {
    const response = await api.get<Cart>('/cart');
    return response.data;
  },

  /**
   * Add item to cart
   */
  async addToCart(data: AddToCartData): Promise<Cart> {
    const response = await api.post<Cart>('/cart/add', data);
    return response.data;
  },

  /**
   * Update cart item quantity
   */
  async updateQuantity(itemId: string, data: UpdateCartData): Promise<Cart> {
    const response = await api.put<Cart>(`/cart/update/${itemId}`, data);
    return response.data;
  },

  /**
   * Remove item from cart
   */
  async removeFromCart(itemId: string): Promise<Cart> {
    const response = await api.delete<Cart>(`/cart/remove/${itemId}`);
    return response.data;
  },

  /**
   * Clear entire cart
   */
  async clearCart(): Promise<void> {
    await api.delete('/cart/clear');
  },

  /**
   * Apply discount code
   */
  async applyDiscount(code: string): Promise<Cart> {
    const response = await api.post<Cart>('/cart/apply-discount', { code });
    return response.data;
  },

  /**
   * Remove discount code
   */
  async removeDiscount(): Promise<Cart> {
    const response = await api.delete<Cart>('/cart/remove-discount');
    return response.data;
  },
};

export default cartService;
