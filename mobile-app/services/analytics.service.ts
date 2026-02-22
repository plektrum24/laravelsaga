import { apiClient } from './api';

export interface DateRange {
  start: string;
  end: string;
  preset?: string;
}

export interface ExecutiveSummary {
  revenue: number;
  revenue_change: number;
  orders: number;
  orders_change: number;
  customers: number;
  customers_change: number;
  products_sold: number;
  products_sold_change: number;
  average_order_value: number;
  average_order_value_change: number;
  period: string;
}

export interface RevenueTrendData {
  date: string;
  revenue: number;
  orders: number;
  customers: number;
}

export interface ProductMetric {
  id: string;
  name: string;
  revenue: number;
  revenue_change: number;
  units_sold: number;
  units_sold_change: number;
  profit_margin: number;
  stock_level: string;
}

export interface CustomerMetrics {
  total_customers: number;
  new_customers: number;
  returning_customers: number;
  active_customers: number;
  customer_acquisition_cost: number;
  retention_rate: number;
}

export interface InventoryHealth {
  total_products: number;
  healthy_stock: number;
  healthy_stock_percent: number;
  low_stock: number;
  low_stock_percent: number;
  out_of_stock: number;
  out_of_stock_percent: number;
  dead_stock: number;
  dead_stock_percent: number;
  total_value: number;
}

/**
 * Get executive summary metrics
 */
export async function getExecutiveSummary(dateRange: DateRange): Promise<ExecutiveSummary> {
  try {
    const response = await apiClient.get('/analytics/executive/summary', {
      params: {
        start_date: dateRange.start,
        end_date: dateRange.end,
      },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching executive summary:', error);
    // Return mock data for development
    return getMockExecutiveSummary();
  }
}

/**
 * Get revenue trend data
 */
export async function getRevenueTrend(dateRange: DateRange): Promise<RevenueTrendData[]> {
  try {
    const response = await apiClient.get('/analytics/executive/revenue-trend', {
      params: {
        start_date: dateRange.start,
        end_date: dateRange.end,
      },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching revenue trend:', error);
    return getMockRevenueTrend();
  }
}

/**
 * Get top products
 */
export async function getTopProducts(
  limit: number = 10,
  dateRange: DateRange
): Promise<ProductMetric[]> {
  try {
    const response = await apiClient.get('/analytics/executive/top-products', {
      params: {
        limit,
        start_date: dateRange.start,
        end_date: dateRange.end,
      },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching top products:', error);
    return getMockTopProducts();
  }
}

/**
 * Get customer metrics
 */
export async function getCustomerMetrics(dateRange: DateRange): Promise<CustomerMetrics> {
  try {
    const response = await apiClient.get('/analytics/executive/customer-metrics', {
      params: {
        start_date: dateRange.start,
        end_date: dateRange.end,
      },
    });
    return response.data || response;
  } catch (error) {
    console.error('Error fetching customer metrics:', error);
    return getMockCustomerMetrics();
  }
}

/**
 * Get inventory health
 */
export async function getInventoryHealth(): Promise<InventoryHealth> {
  try {
    const response = await apiClient.get('/analytics/executive/inventory-health');
    return response.data || response;
  } catch (error) {
    console.error('Error fetching inventory health:', error);
    return getMockInventoryHealth();
  }
}

// Mock Data for Development

function getMockExecutiveSummary(): ExecutiveSummary {
  return {
    revenue: 50000000,
    revenue_change: 12.5,
    orders: 1234,
    orders_change: 8.3,
    customers: 5678,
    customers_change: 15.2,
    products_sold: 3456,
    products_sold_change: 10.1,
    average_order_value: 40518,
    average_order_value_change: 4.2,
    period: 'Last 30 days',
  };
}

function getMockRevenueTrend(): RevenueTrendData[] {
  const data: RevenueTrendData[] = [];
  const today = new Date();
  
  for (let i = 29; i >= 0; i--) {
    const date = new Date(today);
    date.setDate(date.getDate() - i);
    
    data.push({
      date: date.toISOString().split('T')[0],
      revenue: Math.floor(Math.random() * 5000000) + 1000000,
      orders: Math.floor(Math.random() * 100) + 20,
      customers: Math.floor(Math.random() * 80) + 15,
    });
  }
  
  return data;
}

function getMockTopProducts(): ProductMetric[] {
  return [
    {
      id: '1',
      name: 'Premium Coffee Beans 1kg',
      revenue: 5000000,
      revenue_change: 20.5,
      units_sold: 500,
      units_sold_change: 15.3,
      profit_margin: 35,
      stock_level: 'healthy',
    },
    {
      id: '2',
      name: 'Organic Green Tea 500g',
      revenue: 3000000,
      revenue_change: 15.2,
      units_sold: 300,
      units_sold_change: 12.1,
      profit_margin: 40,
      stock_level: 'healthy',
    },
    {
      id: '3',
      name: 'Artisan Chocolate Box',
      revenue: 2000000,
      revenue_change: -5.3,
      units_sold: 200,
      units_sold_change: -8.2,
      profit_margin: 45,
      stock_level: 'low',
    },
    {
      id: '4',
      name: 'Premium Honey 500ml',
      revenue: 1500000,
      revenue_change: 25.7,
      units_sold: 150,
      units_sold_change: 22.4,
      profit_margin: 50,
      stock_level: 'healthy',
    },
    {
      id: '5',
      name: 'Organic Olive Oil 750ml',
      revenue: 1200000,
      revenue_change: 8.9,
      units_sold: 120,
      units_sold_change: 5.6,
      profit_margin: 38,
      stock_level: 'healthy',
    },
  ];
}

function getMockCustomerMetrics(): CustomerMetrics {
  return {
    total_customers: 5678,
    new_customers: 856,
    returning_customers: 4822,
    active_customers: 3456,
    customer_acquisition_cost: 25000,
    retention_rate: 72.5,
  };
}

function getMockInventoryHealth(): InventoryHealth {
  return {
    total_products: 1234,
    healthy_stock: 1049,
    healthy_stock_percent: 85,
    low_stock: 123,
    low_stock_percent: 10,
    out_of_stock: 62,
    out_of_stock_percent: 5,
    dead_stock: 37,
    dead_stock_percent: 3,
    total_value: 250000000,
  };
}
