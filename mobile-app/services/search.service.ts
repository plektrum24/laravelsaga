import { apiClient } from './api';

export interface SearchSuggestion {
  text: string;
  type: 'product' | 'category' | 'brand';
  product_id?: string;
  image_url?: string;
  score: number;
}

export interface SearchResult {
  query: string;
  total_results: number;
  products: Array<{
    id: string;
    name: string;
    price: number;
    image_url?: string;
    score: number;
    highlights?: string[];
  }>;
  categories: Array<{
    id: string;
    name: string;
    product_count: number;
  }>;
  took_ms: number;
}

export interface SearchFilters {
  category_id?: string;
  min_price?: number;
  max_price?: number;
  in_stock?: boolean;
  sort?: 'relevance' | 'price_asc' | 'price_desc' | 'newest' | 'popular';
}

/**
 * Get search autocomplete suggestions
 */
export async function getSearchSuggestions(query: string, limit: number = 5): Promise<SearchSuggestion[]> {
  try {
    const response = await apiClient.get('/api/search/suggestions', {
      params: { q: query, limit },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching search suggestions:', error);
    return getMockSearchSuggestions(query);
  }
}

/**
 * Perform intelligent search
 */
export async function intelligentSearch(
  query: string,
  filters?: SearchFilters,
  page: number = 1,
  limit: number = 20
): Promise<SearchResult> {
  try {
    const response = await apiClient.get('/api/search', {
      params: {
        q: query,
        page,
        limit,
        ...filters,
      },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error performing intelligent search:', error);
    return getMockSearchResult(query);
  }
}

/**
 * Perform voice search (speech-to-text)
 */
export async function voiceSearch(): Promise<string> {
  try {
    // This would integrate with device's speech recognition
    // For now, return mock implementation
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve('wireless earbuds');
      }, 1000);
    });
  } catch (error) {
    console.error('Error with voice search:', error);
    throw error;
  }
}

/**
 * Correct search query typos
 */
export async function correctQuery(query: string): Promise<{ corrected: string; is_corrected: boolean }> {
  try {
    const response = await apiClient.get('/api/search/correct', {
      params: { q: query },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error correcting query:', error);
    return { corrected: query, is_corrected: false };
  }
}

/**
 * Track search analytics
 */
export async function trackSearch(query: string, results_count: number, clicked_product_id?: string): Promise<void> {
  try {
    await apiClient.post('/api/search/track', {
      query,
      results_count,
      clicked_product_id,
      timestamp: new Date().toISOString(),
    });
  } catch (error) {
    console.error('Error tracking search:', error);
  }
}

// Mock Data for Development

function getMockSearchSuggestions(query: string): SearchSuggestion[] {
  const suggestions: SearchSuggestion[] = [
    {
      text: `${query} wireless`,
      type: 'product',
      product_id: '101',
      image_url: 'https://via.placeholder.com/50',
      score: 0.95,
    },
    {
      text: `${query} bluetooth`,
      type: 'product',
      product_id: '102',
      image_url: 'https://via.placeholder.com/50',
      score: 0.90,
    },
    {
      text: 'Electronics',
      type: 'category',
      score: 0.85,
    },
    {
      text: 'Accessories',
      type: 'category',
      score: 0.80,
    },
  ];

  return suggestions.filter((s) => s.text.toLowerCase().includes(query.toLowerCase())).slice(0, 5);
}

function getMockSearchResult(query: string): SearchResult {
  return {
    query,
    total_results: 156,
    products: [
      {
        id: '1',
        name: `Wireless Earbuds ${query}`,
        price: 350000,
        image_url: 'https://via.placeholder.com/200',
        score: 0.98,
        highlights: ['<mark>Wireless</mark> Earbuds', 'Bluetooth 5.0'],
      },
      {
        id: '2',
        name: `Smart Watch ${query}`,
        price: 500000,
        image_url: 'https://via.placeholder.com/200',
        score: 0.95,
        highlights: ['<mark>Smart</mark> Watch', 'Fitness Tracker'],
      },
      {
        id: '3',
        name: `Portable Charger ${query}`,
        price: 150000,
        image_url: 'https://via.placeholder.com/200',
        score: 0.92,
        highlights: ['<mark>Portable</mark> Charger', '10000mAh'],
      },
    ],
    categories: [
      {
        id: '1',
        name: 'Electronics',
        product_count: 85,
      },
      {
        id: '2',
        name: 'Accessories',
        product_count: 45,
      },
    ],
    took_ms: 45,
  };
}
