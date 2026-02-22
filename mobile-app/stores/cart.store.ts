import { create } from 'zustand';
import { Cart, CartItem, Product } from '../types/api.types';
import cartService from '../services/cart.service';

interface CartState {
  cart: Cart | null;
  items: CartItem[];
  total: number;
  itemCount: number;
  isLoading: boolean;
  error: string | null;

  fetchCart: () => Promise<void>;
  addItem: (product: Product, quantity: number) => Promise<void>;
  updateQuantity: (itemId: string, quantity: number) => Promise<void>;
  removeItem: (itemId: string) => Promise<void>;
  clearCart: () => Promise<void>;
  applyDiscount: (code: string) => Promise<void>;
  clearError: () => void;
}

export const useCartStore = create<CartState>((set, get) => ({
  cart: null,
  items: [],
  total: 0,
  itemCount: 0,
  isLoading: false,
  error: null,

  fetchCart: async () => {
    set({ isLoading: true, error: null });
    try {
      const cart = await cartService.getCart();
      set({ 
        cart, 
        items: cart.items, 
        total: cart.total, 
        itemCount: cart.items.reduce((sum, item) => sum + item.quantity, 0),
        isLoading: false 
      });
    } catch (error: any) {
      set({ 
        error: error.message || 'Failed to load cart', 
        isLoading: false 
      });
    }
  },

  addItem: async (product: Product, quantity: number) => {
    set({ isLoading: true, error: null });
    try {
      const cart = await cartService.addToCart({
        product_id: product.id,
        quantity,
      });
      set({ 
        cart, 
        items: cart.items, 
        total: cart.total, 
        itemCount: cart.items.reduce((sum, item) => sum + item.quantity, 0),
        isLoading: false 
      });
    } catch (error: any) {
      set({ 
        error: error.message || 'Failed to add item', 
        isLoading: false 
      });
      throw error;
    }
  },

  updateQuantity: async (itemId: string, quantity: number) => {
    set({ isLoading: true, error: null });
    try {
      const cart = await cartService.updateQuantity(itemId, { quantity });
      set({ 
        cart, 
        items: cart.items, 
        total: cart.total, 
        itemCount: cart.items.reduce((sum, item) => sum + item.quantity, 0),
        isLoading: false 
      });
    } catch (error: any) {
      set({ 
        error: error.message || 'Failed to update quantity', 
        isLoading: false 
      });
      throw error;
    }
  },

  removeItem: async (itemId: string) => {
    set({ isLoading: true, error: null });
    try {
      const cart = await cartService.removeFromCart(itemId);
      set({ 
        cart, 
        items: cart.items, 
        total: cart.total, 
        itemCount: cart.items.reduce((sum, item) => sum + item.quantity, 0),
        isLoading: false 
      });
    } catch (error: any) {
      set({ 
        error: error.message || 'Failed to remove item', 
        isLoading: false 
      });
      throw error;
    }
  },

  clearCart: async () => {
    set({ isLoading: true, error: null });
    try {
      await cartService.clearCart();
      set({ 
        cart: null, 
        items: [], 
        total: 0, 
        itemCount: 0,
        isLoading: false 
      });
    } catch (error: any) {
      set({ 
        error: error.message || 'Failed to clear cart', 
        isLoading: false 
      });
      throw error;
    }
  },

  applyDiscount: async (code: string) => {
    set({ isLoading: true, error: null });
    try {
      const cart = await cartService.applyDiscount(code);
      set({ 
        cart, 
        items: cart.items, 
        total: cart.total, 
        isLoading: false 
      });
    } catch (error: any) {
      set({ 
        error: error.message || 'Invalid discount code', 
        isLoading: false 
      });
      throw error;
    }
  },

  clearError: () => {
    set({ error: null });
  },
}));

export default useCartStore;
