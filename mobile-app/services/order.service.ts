import api from './api';
import { Order, CreateOrderData, PaginatedResponse } from '../types/api.types';

export const orderService = {
  /**
   * Create new order
   */
  async createOrder(data: CreateOrderData): Promise<Order> {
    const response = await api.post<Order>('/orders', data);
    return response.data;
  },

  /**
   * Get user's orders
   */
  async getOrders(params?: { page?: number; limit?: number; status?: string }): Promise<PaginatedResponse<Order>> {
    const response = await api.get<PaginatedResponse<Order>>('/orders', { params });
    return response.data;
  },

  /**
   * Get single order by ID
   */
  async getOrder(id: string): Promise<Order> {
    const response = await api.get<Order>(`/orders/${id}`);
    return response.data;
  },

  /**
   * Cancel order
   */
  async cancelOrder(id: string, reason?: string): Promise<Order> {
    const response = await api.post<Order>(`/orders/${id}/cancel`, { reason });
    return response.data;
  },

  /**
   * Track order status
   */
  async trackOrder(id: string): Promise<Order> {
    const response = await api.get<Order>(`/orders/${id}/tracking`);
    return response.data;
  },

  /**
   * Reorder (create new order from previous order)
   */
  async reorder(id: string): Promise<Order> {
    const response = await api.post<Order>(`/orders/${id}/reorder`);
    return response.data;
  },

  /**
   * Get order invoice
   */
  async getInvoice(id: string): Promise<Blob> {
    return await api.getBlob(`/orders/${id}/invoice`);
  },
};

export default orderService;
