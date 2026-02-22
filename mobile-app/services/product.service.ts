import api from './api';
import { Product, Category, ProductParams, PaginatedResponse } from '../types/api.types';

export const productService = {
  /**
   * Get paginated products list
   */
  async getProducts(params?: ProductParams): Promise<PaginatedResponse<Product>> {
    const response = await api.get<PaginatedResponse<Product>>('/products', { params });
    return response.data;
  },

  /**
   * Get single product by ID
   */
  async getProduct(id: string): Promise<Product> {
    const response = await api.get<Product>(`/products/${id}`);
    return response.data;
  },

  /**
   * Search products
   */
  async searchProducts(query: string, params?: ProductParams): Promise<PaginatedResponse<Product>> {
    const response = await api.get<PaginatedResponse<Product>>('/products/search', {
      params: { ...params, search: query },
    });
    return response.data;
  },

  /**
   * Get products by category
   */
  async getProductsByCategory(categoryId: string, params?: ProductParams): Promise<PaginatedResponse<Product>> {
    const response = await api.get<PaginatedResponse<Product>>(`/categories/${categoryId}/products`, { params });
    return response.data;
  },

  /**
   * Get featured products
   */
  async getFeaturedProducts(limit?: number): Promise<Product[]> {
    const response = await api.get<PaginatedResponse<Product>>('/products/featured', { params: { limit } });
    return response.data.data;
  },

  /**
   * Get all categories
   */
  async getCategories(): Promise<Category[]> {
    const response = await api.get<Category[]>('/categories');
    return response.data;
  },

  /**
   * Get single category
   */
  async getCategory(id: string): Promise<Category> {
    const response = await api.get<Category>(`/categories/${id}`);
    return response.data;
  },

  /**
   * Get product recommendations
   */
  async getRecommendations(productId?: string, limit?: number): Promise<Product[]> {
    const params = productId ? { product_id: productId, limit } : { limit };
    const response = await api.get<PaginatedResponse<Product>>('/products/recommendations', { params });
    return response.data.data;
  },
};

export default productService;
