import api from './api';
import { LoginCredentials, RegisterData, AuthResponse, User } from '../types/api.types';

export const authService = {
  /**
   * Login user
   */
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/login', {
      ...credentials,
      device_name: 'Mobile App',
    });
    
    if (response.success && response.data.access_token) {
      await api.setToken(response.data.access_token);
    }
    
    return response.data;
  },

  /**
   * Register new user
   */
  async register(data: RegisterData): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/register', data);
    
    if (response.success && response.data.access_token) {
      await api.setToken(response.data.access_token);
    }
    
    return response.data;
  },

  /**
   * Logout user
   */
  async logout(): Promise<void> {
    try {
      await api.post('/auth/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      await api.clearToken();
    }
  },

  /**
   * Get current user profile
   */
  async getMe(): Promise<User> {
    const response = await api.get<User>('/auth/me');
    return response.data;
  },

  /**
   * Update user profile
   */
  async updateProfile(data: Partial<User>): Promise<User> {
    const response = await api.put<User>('/auth/profile', data);
    return response.data;
  },

  /**
   * Change password
   */
  async changePassword(currentPassword: string, newPassword: string): Promise<void> {
    await api.post('/auth/change-password', {
      current_password: currentPassword,
      new_password: newPassword,
      new_password_confirmation: newPassword,
    });
  },

  /**
   * Request password reset
   */
  async forgotPassword(email: string): Promise<void> {
    await api.post('/auth/forgot-password', { email });
  },

  /**
   * Reset password
   */
  async resetPassword(token: string, password: string): Promise<void> {
    await api.post('/auth/reset-password', {
      token,
      password,
      password_confirmation: password,
    });
  },
};

export default authService;
