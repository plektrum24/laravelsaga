import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import * as AnalyticsService from '../services/analytics.service';

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

interface AnalyticsState {
  // Executive Dashboard Data
  summary: ExecutiveSummary | null;
  revenueTrend: RevenueTrendData[];
  topProducts: ProductMetric[];
  customerMetrics: CustomerMetrics | null;
  inventoryHealth: InventoryHealth | null;
  
  // Filters
  dateRange: DateRange;
  
  // State
  isLoading: boolean;
  error: string | null;
  lastUpdated: Date | null;
  
  // Actions
  fetchExecutiveSummary: (dateRange: DateRange) => Promise<void>;
  fetchRevenueTrend: (dateRange: DateRange) => Promise<void>;
  fetchTopProducts: (limit: number, dateRange: DateRange) => Promise<void>;
  fetchCustomerMetrics: (dateRange: DateRange) => Promise<void>;
  fetchInventoryHealth: () => Promise<void>;
  fetchAllExecutiveData: (dateRange: DateRange) => Promise<void>;
  setDateRange: (dateRange: DateRange) => void;
  refresh: () => Promise<void>;
  clearError: () => void;
}

export const useAnalyticsStore = create<AnalyticsState>()(
  persist(
    (set, get) => ({
      // Initial State
      summary: null,
      revenueTrend: [],
      topProducts: [],
      customerMetrics: null,
      inventoryHealth: null,
      dateRange: {
        start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        end: new Date().toISOString().split('T')[0],
        preset: 'last_30_days',
      },
      isLoading: false,
      error: null,
      lastUpdated: null,
      
      // Fetch Executive Summary
      fetchExecutiveSummary: async (dateRange: DateRange) => {
        set({ isLoading: true, error: null });
        try {
          const summary = await AnalyticsService.getExecutiveSummary(dateRange);
          set({ summary, lastUpdated: new Date() });
        } catch (error: any) {
          set({ error: error.message || 'Failed to fetch executive summary' });
        } finally {
          set({ isLoading: false });
        }
      },
      
      // Fetch Revenue Trend
      fetchRevenueTrend: async (dateRange: DateRange) => {
        set({ isLoading: true, error: null });
        try {
          const revenueTrend = await AnalyticsService.getRevenueTrend(dateRange);
          set({ revenueTrend, lastUpdated: new Date() });
        } catch (error: any) {
          set({ error: error.message || 'Failed to fetch revenue trend' });
        } finally {
          set({ isLoading: false });
        }
      },
      
      // Fetch Top Products
      fetchTopProducts: async (limit: number, dateRange: DateRange) => {
        set({ isLoading: true, error: null });
        try {
          const topProducts = await AnalyticsService.getTopProducts(limit, dateRange);
          set({ topProducts, lastUpdated: new Date() });
        } catch (error: any) {
          set({ error: error.message || 'Failed to fetch top products' });
        } finally {
          set({ isLoading: false });
        }
      },
      
      // Fetch Customer Metrics
      fetchCustomerMetrics: async (dateRange: DateRange) => {
        set({ isLoading: true, error: null });
        try {
          const customerMetrics = await AnalyticsService.getCustomerMetrics(dateRange);
          set({ customerMetrics, lastUpdated: new Date() });
        } catch (error: any) {
          set({ error: error.message || 'Failed to fetch customer metrics' });
        } finally {
          set({ isLoading: false });
        }
      },
      
      // Fetch Inventory Health
      fetchInventoryHealth: async () => {
        set({ isLoading: true, error: null });
        try {
          const inventoryHealth = await AnalyticsService.getInventoryHealth();
          set({ inventoryHealth, lastUpdated: new Date() });
        } catch (error: any) {
          set({ error: error.message || 'Failed to fetch inventory health' });
        } finally {
          set({ isLoading: false });
        }
      },
      
      // Fetch All Executive Data
      fetchAllExecutiveData: async (dateRange: DateRange) => {
        set({ isLoading: true, error: null });
        try {
          // Fetch all data in parallel
          const [summary, revenueTrend, topProducts, customerMetrics, inventoryHealth] = await Promise.all([
            AnalyticsService.getExecutiveSummary(dateRange),
            AnalyticsService.getRevenueTrend(dateRange),
            AnalyticsService.getTopProducts(10, dateRange),
            AnalyticsService.getCustomerMetrics(dateRange),
            AnalyticsService.getInventoryHealth(),
          ]);
          
          set({
            summary,
            revenueTrend,
            topProducts,
            customerMetrics,
            inventoryHealth,
            lastUpdated: new Date(),
          });
        } catch (error: any) {
          set({ error: error.message || 'Failed to fetch analytics data' });
        } finally {
          set({ isLoading: false });
        }
      },
      
      // Set Date Range
      setDateRange: (dateRange: DateRange) => {
        set({ dateRange });
        // Automatically fetch new data
        get().fetchAllExecutiveData(dateRange);
      },
      
      // Refresh Data
      refresh: async () => {
        const { dateRange } = get();
        await get().fetchAllExecutiveData(dateRange);
      },
      
      // Clear Error
      clearError: () => {
        set({ error: null });
      },
    }),
    {
      name: 'analytics-storage',
      partialize: (state) => ({
        dateRange: state.dateRange,
        summary: state.summary,
        revenueTrend: state.revenueTrend,
        topProducts: state.topProducts,
        customerMetrics: state.customerMetrics,
        inventoryHealth: state.inventoryHealth,
        lastUpdated: state.lastUpdated,
      }),
    }
  )
);
