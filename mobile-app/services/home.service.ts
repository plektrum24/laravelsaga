import { apiClient, API_ENDPOINTS } from './api';

export interface HomeData {
  banners: Banner[];
  featuredProducts: any[];
  newArrivals: any[];
  bestSellers: any[];
  categories: any[];
}

export interface Banner {
  id: number;
  title: string;
  subtitle: string;
  image_url: string;
  action_url?: string;
  background_color: string;
  icon: string;
}

/**
 * Get home page data
 */
export async function getHomeData(): Promise<HomeData> {
  try {
    const response = await apiClient.get(API_ENDPOINTS.HOME);
    return response.data || response;
  } catch (error) {
    console.error('Error fetching home data:', error);
    // Return mock data for development
    return getMockHomeData();
  }
}

/**
 * Get promotional banners
 */
export async function getBanners(): Promise<Banner[]> {
  try {
    // For now, return mock banners
    // Backend can implement: GET /api/mobile/banners
    return [
      {
        id: 1,
        title: '🎉 Welcome Offer',
        subtitle: 'Get 100 bonus points on first purchase!',
        image_url: '',
        background_color: '#4F46E5',
        icon: 'gift',
      },
      {
        id: 2,
        title: '🚚 Free Delivery',
        subtitle: 'On orders over Rp 500,000',
        image_url: '',
        background_color: '#10B981',
        icon: 'truck',
      },
      {
        id: 3,
        title: '💳 Cashback 10%',
        subtitle: 'Pay with GoPay this week',
        image_url: '',
        background_color: '#F59E0B',
        icon: 'card',
      },
    ];
  } catch (error) {
    console.error('Error fetching banners:', error);
    return [];
  }
}

/**
 * Get featured products
 */
export async function getFeaturedProducts(limit: number = 10) {
  try {
    const response = await apiClient.get(API_ENDPOINTS.PRODUCTS, {
      params: { featured: 1, limit }
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching featured products:', error);
    return [];
  }
}

/**
 * Get new arrivals
 */
export async function getNewArrivals(limit: number = 10) {
  try {
    const response = await apiClient.get(API_ENDPOINTS.PRODUCTS, {
      params: { sort: 'newest', limit }
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching new arrivals:', error);
    return [];
  }
}

/**
 * Get best sellers
 */
export async function getBestSellers(limit: number = 10) {
  try {
    const response = await apiClient.get(API_ENDPOINTS.PRODUCTS, {
      params: { sort: 'best_seller', limit }
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching best sellers:', error);
    return [];
  }
}

/**
 * Get categories
 */
export async function getCategories() {
  try {
    const response = await apiClient.get(API_ENDPOINTS.CATEGORIES);
    return response.data || response;
  } catch (error) {
    console.error('Error fetching categories:', error);
    return [];
  }
}

/**
 * Mock data for development
 */
function getMockHomeData(): HomeData {
  return {
    banners: [
      {
        id: 1,
        title: '🎉 Welcome Offer',
        subtitle: 'Get 100 bonus points on first purchase!',
        image_url: '',
        background_color: '#4F46E5',
        icon: 'gift',
      },
      {
        id: 2,
        title: '🚚 Free Delivery',
        subtitle: 'On orders over Rp 500,000',
        image_url: '',
        background_color: '#10B981',
        icon: 'truck',
      },
      {
        id: 3,
        title: '💳 Cashback 10%',
        subtitle: 'Pay with GoPay this week',
        image_url: '',
        background_color: '#F59E0B',
        icon: 'card',
      },
    ],
    featuredProducts: [],
    newArrivals: [],
    bestSellers: [],
    categories: [],
  };
}
