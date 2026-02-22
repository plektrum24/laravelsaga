import api from './api';
import { LoyaltyPoints, CustomerTier, Reward, CustomerReward, PointsHistory, PaginatedResponse } from '../types/api.types';

export const loyaltyService = {
  /**
   * Get user's points balance
   */
  async getPoints(): Promise<LoyaltyPoints> {
    const response = await api.get<LoyaltyPoints>('/loyalty/points');
    return response.data;
  },

  /**
   * Get user's tier status
   */
  async getTier(): Promise<CustomerTier> {
    const response = await api.get<CustomerTier>('/loyalty/tier');
    return response.data;
  },

  /**
   * Get all membership tiers
   */
  async getTiers(): Promise<CustomerTier[]> {
    const response = await api.get<CustomerTier[]>('/tiers');
    return response.data;
  },

  /**
   * Get points history
   */
  async getPointsHistory(params?: { page?: number; limit?: number }): Promise<PaginatedResponse<PointsHistory>> {
    const response = await api.get<PaginatedResponse<PointsHistory>>('/loyalty/points/history', { params });
    return response.data;
  },

  /**
   * Get rewards catalog
   */
  async getRewards(params?: { page?: number; limit?: number; status?: string }): Promise<PaginatedResponse<Reward>> {
    const response = await api.get<PaginatedResponse<Reward>>('/rewards', { params });
    return response.data;
  },

  /**
   * Get single reward
   */
  async getReward(id: string): Promise<Reward> {
    const response = await api.get<Reward>(`/rewards/${id}`);
    return response.data;
  },

  /**
   * Redeem reward
   */
  async redeemReward(id: string): Promise<CustomerReward> {
    const response = await api.post<CustomerReward>(`/rewards/${id}/redeem`);
    return response.data;
  },

  /**
   * Get user's redeemed rewards
   */
  async getMyRewards(params?: { page?: number; limit?: number; status?: string }): Promise<PaginatedResponse<CustomerReward>> {
    const response = await api.get<PaginatedResponse<CustomerReward>>('/loyalty/rewards', { params });
    return response.data;
  },

  /**
   * Use reward (mark as used)
   */
  async useReward(rewardId: string): Promise<CustomerReward> {
    const response = await api.post<CustomerReward>(`/loyalty/rewards/${rewardId}/use`);
    return response.data;
  },

  /**
   * Earn points from purchase
   */
  async earnPointsFromOrder(orderId: string): Promise<LoyaltyPoints> {
    const response = await api.post<LoyaltyPoints>(`/loyalty/earn-from-order/${orderId}`);
    return response.data;
  },
};

export default loyaltyService;
