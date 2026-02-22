import { apiClient } from './api';

export interface Recommendation {
  id: string;
  product_id: string;
  name: string;
  price: number;
  image_url?: string;
  score: number;
  reason: string;
}

export interface BundleDeal {
  products: Recommendation[];
  total_price: number;
  discount: number;
  savings: number;
}

/**
 * Get products that customers also bought
 */
export async function getCustomersAlsoBought(productId: string, limit: number = 6): Promise<Recommendation[]> {
  try {
    const response = await apiClient.get(`/api/recommendations/customers-also-bought/${productId}`, {
      params: { limit },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching customers also bought:', error);
    return getMockCustomersAlsoBought();
  }
}

/**
 * Get products you may also like
 */
export async function getYouMayAlsoLike(productId: string, limit: number = 6): Promise<Recommendation[]> {
  try {
    const response = await apiClient.get(`/api/recommendations/you-may-also-like/${productId}`, {
      params: { limit },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching you may also like:', error);
    return getMockYouMayAlsoLike();
  }
}

/**
 * Get personalized recommendations for user
 */
export async function getPersonalizedRecommendations(
  userId: string,
  limit: number = 10
): Promise<Recommendation[]> {
  try {
    const response = await apiClient.get(`/api/recommendations/personalized/${userId}`, {
      params: { limit },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching personalized recommendations:', error);
    return getMockPersonalizedRecommendations();
  }
}

/**
 * Get cart-based recommendations
 */
export async function getCartRecommendations(cartItemIds: string[]): Promise<Recommendation[]> {
  try {
    const response = await apiClient.get('/api/recommendations/cart', {
      params: { items: cartItemIds.join(',') },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching cart recommendations:', error);
    return getMockCartRecommendations();
  }
}

/**
 * Get frequently bought together bundle
 */
export async function getFrequentlyBoughtTogether(productId: string): Promise<BundleDeal> {
  try {
    const response = await apiClient.get(`/api/recommendations/frequently-bought-together/${productId}`);
    return response.data || response;
  } catch (error) {
    console.error('Error fetching frequently bought together:', error);
    return getMockFrequentlyBoughtTogether();
  }
}

/**
 * Get trending products
 */
export async function getTrendingProducts(category?: string, limit: number = 10): Promise<Recommendation[]> {
  try {
    const response = await apiClient.get('/api/recommendations/trending', {
      params: { category, limit },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching trending products:', error);
    return getMockTrendingProducts();
  }
}

// Mock Data for Development

function getMockCustomersAlsoBought(): Recommendation[] {
  return [
    {
      id: '1',
      product_id: '101',
      name: 'Premium Coffee Beans 1kg',
      price: 150000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.95,
      reason: 'Bought together by 85% of customers',
    },
    {
      id: '2',
      product_id: '102',
      name: 'Coffee Grinder Electric',
      price: 250000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.88,
      reason: 'Bought together by 72% of customers',
    },
    {
      id: '3',
      product_id: '103',
      name: 'French Press 1L',
      price: 120000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.82,
      reason: 'Bought together by 65% of customers',
    },
  ];
}

function getMockYouMayAlsoLike(): Recommendation[] {
  return [
    {
      id: '4',
      product_id: '104',
      name: 'Organic Green Tea 500g',
      price: 100000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.90,
      reason: 'Similar to products you viewed',
    },
    {
      id: '5',
      product_id: '105',
      name: 'Artisan Chocolate Box',
      price: 180000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.85,
      reason: 'Popular in your category',
    },
    {
      id: '6',
      product_id: '106',
      name: 'Premium Honey 500ml',
      price: 95000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.80,
      reason: 'Trending now',
    },
  ];
}

function getMockPersonalizedRecommendations(): Recommendation[] {
  return [
    {
      id: '7',
      product_id: '107',
      name: 'Organic Olive Oil 750ml',
      price: 135000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.92,
      reason: 'Based on your purchase history',
    },
    {
      id: '8',
      product_id: '108',
      name: 'Whole Grain Bread',
      price: 45000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.88,
      reason: 'Frequently bought by similar customers',
    },
    {
      id: '9',
      product_id: '109',
      name: 'Fresh Milk 1L',
      price: 25000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.85,
      reason: 'You might need this soon',
    },
  ];
}

function getMockCartRecommendations(): Recommendation[] {
  return [
    {
      id: '10',
      product_id: '110',
      name: 'Reusable Shopping Bags',
      price: 15000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.90,
      reason: 'Complete your purchase',
    },
    {
      id: '11',
      product_id: '111',
      name: 'Food Storage Containers',
      price: 75000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.85,
      reason: 'Don\'t forget these',
    },
  ];
}

function getMockFrequentlyBoughtTogether(): BundleDeal {
  return {
    products: [
      {
        id: '1',
        product_id: '101',
        name: 'Premium Coffee Beans 1kg',
        price: 150000,
        image_url: 'https://via.placeholder.com/200',
        score: 1.0,
        reason: 'Main product',
      },
      {
        id: '2',
        product_id: '102',
        name: 'Coffee Grinder Electric',
        price: 250000,
        image_url: 'https://via.placeholder.com/200',
        score: 0.95,
        reason: 'Frequently bought together',
      },
      {
        id: '3',
        product_id: '103',
        name: 'French Press 1L',
        price: 120000,
        image_url: 'https://via.placeholder.com/200',
        score: 0.90,
        reason: 'Frequently bought together',
      },
    ],
    total_price: 520000,
    discount: 10,
    savings: 52000,
  };
}

function getMockTrendingProducts(): Recommendation[] {
  return [
    {
      id: '12',
      product_id: '112',
      name: 'Wireless Earbuds',
      price: 350000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.98,
      reason: 'Trending in Electronics',
    },
    {
      id: '13',
      product_id: '113',
      name: 'Smart Watch',
      price: 500000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.95,
      reason: 'Trending in Electronics',
    },
    {
      id: '14',
      product_id: '114',
      name: 'Portable Charger',
      price: 150000,
      image_url: 'https://via.placeholder.com/200',
      score: 0.92,
      reason: 'Trending in Electronics',
    },
  ];
}
