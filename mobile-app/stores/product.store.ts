import { create } from 'zustand';
import { Product, Category, ProductParams } from '../types/api.types';
import productService from '../services/product.service';

interface ProductState {
  products: Product[];
  categories: Category[];
  selectedProduct: Product | null;
  featuredProducts: Product[];
  isLoading: boolean;
  hasMore: boolean;
  currentPage: number;
  totalProducts: number;
  error: string | null;

  fetchProducts: (params?: ProductParams) => Promise<void>;
  fetchProductById: (id: string) => Promise<Product>;
  fetchCategories: () => Promise<void>;
  fetchFeaturedProducts: () => Promise<void>;
  searchProducts: (query: string, params?: ProductParams) => Promise<void>;
  setSelectedProduct: (product: Product | null) => void;
  clearError: () => void;
}

export const useProductStore = create<ProductState>((set, get) => ({
  products: [],
  categories: [],
  selectedProduct: null,
  featuredProducts: [],
  isLoading: false,
  hasMore: true,
  currentPage: 1,
  totalProducts: 0,
  error: null,

  fetchProducts: async (params?: ProductParams) => {
    set({ isLoading: true, error: null });
    try {
      const response = await productService.getProducts({
        page: 1,
        limit: 20,
        ...params,
      });
      set({ 
        products: response.data, 
        currentPage: response.current_page,
        hasMore: response.current_page < response.last_page,
        totalProducts: response.total,
        isLoading: false 
      });
    } catch (error: any) {
      set({ 
        error: error.message || 'Failed to load products', 
        isLoading: false 
      });
    }
  },

  fetchProductById: async (id: string) => {
    set({ isLoading: true, error: null });
    try {
      const product = await productService.getProduct(id);
      set({ selectedProduct: product, isLoading: false });
      return product;
    } catch (error: any) {
      set({ 
        error: error.message || 'Failed to load product', 
        isLoading: false 
      });
      throw error;
    }
  },

  fetchCategories: async () => {
    try {
      const categories = await productService.getCategories();
      set({ categories });
    } catch (error) {
      console.error('Failed to load categories:', error);
    }
  },

  fetchFeaturedProducts: async () => {
    try {
      const products = await productService.getFeaturedProducts(10);
      set({ featuredProducts: products });
    } catch (error) {
      console.error('Failed to load featured products:', error);
    }
  },

  searchProducts: async (query: string, params?: ProductParams) => {
    set({ isLoading: true, error: null });
    try {
      const response = await productService.searchProducts(query, {
        page: 1,
        limit: 20,
        ...params,
      });
      set({ 
        products: response.data, 
        currentPage: response.current_page,
        hasMore: response.current_page < response.last_page,
        totalProducts: response.total,
        isLoading: false 
      });
    } catch (error: any) {
      set({ 
        error: error.message || 'Search failed', 
        isLoading: false 
      });
    }
  },

  setSelectedProduct: (product: Product | null) => {
    set({ selectedProduct: product });
  },

  clearError: () => {
    set({ error: null });
  },
}));

export default useProductStore;
