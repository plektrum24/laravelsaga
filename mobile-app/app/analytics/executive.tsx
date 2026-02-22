import React, { useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
} from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useAnalyticsStore } from '../../stores/analytics.store';
import MetricCard from '../../components/analytics/MetricCard';
import RevenueChart from '../../components/analytics/RevenueChart';
import DateRangeSelector from '../../components/analytics/DateRangeSelector';

export default function ExecutiveDashboard() {
  const {
    summary,
    revenueTrend,
    topProducts,
    customerMetrics,
    inventoryHealth,
    dateRange,
    isLoading,
    error,
    lastUpdated,
    fetchAllExecutiveData,
    setDateRange,
    refresh,
  } = useAnalyticsStore();

  useEffect(() => {
    fetchAllExecutiveData(dateRange);
  }, []);

  const handleDateRangeChange = (newDateRange: any) => {
    setDateRange(newDateRange);
  };

  const formatCurrency = (value: number) => {
    return `Rp ${value.toLocaleString('id-ID')}`;
  };

  return (
    <ScrollView
      style={styles.container}
      refreshControl={
        <RefreshControl
          refreshing={isLoading}
          onRefresh={refresh}
          tintColor="#4F46E5"
        />
      }
    >
      {/* Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.title}>Analytics</Text>
          <Text style={styles.subtitle}>Executive Dashboard</Text>
        </View>
        <DateRangeSelector
          dateRange={dateRange}
          onDateRangeChange={handleDateRangeChange}
        />
      </View>

      {/* Error Message */}
      {error && (
        <View style={styles.errorContainer}>
          <Ionicons name="alert-circle" size={20} color="#EF4444" />
          <Text style={styles.errorText}>{error}</Text>
          <TouchableOpacity onPress={() => refresh()}>
            <Text style={styles.retryText}>Retry</Text>
          </TouchableOpacity>
        </View>
      )}

      {/* Key Metrics */}
      <View style={styles.metricsGrid}>
        <MetricCard
          label="Revenue"
          value={formatCurrency(summary?.revenue || 0)}
          trend={summary?.revenue_change || 0 > 0 ? 'up' : 'down'}
          change={summary?.revenue_change || 0}
          icon="cash"
          color="#4F46E5"
          isLoading={isLoading}
        />
        <MetricCard
          label="Orders"
          value={summary?.orders || 0}
          trend={summary?.orders_change || 0 > 0 ? 'up' : 'down'}
          change={summary?.orders_change || 0}
          icon="cart"
          color="#10B981"
          isLoading={isLoading}
        />
        <MetricCard
          label="Customers"
          value={summary?.customers || 0}
          trend={summary?.customers_change || 0 > 0 ? 'up' : 'down'}
          change={summary?.customers_change || 0}
          icon="people"
          color="#F59E0B"
          isLoading={isLoading}
        />
        <MetricCard
          label="Avg Order"
          value={formatCurrency(summary?.average_order_value || 0)}
          trend={summary?.average_order_value_change || 0 > 0 ? 'up' : 'down'}
          change={summary?.average_order_value_change || 0}
          icon="receipt"
          color="#8B5CF6"
          isLoading={isLoading}
        />
      </View>

      {/* Revenue Trend Chart */}
      <RevenueChart
        data={revenueTrend}
        title="Revenue Trend"
        color="#4F46E5"
        height={240}
      />

      {/* Top Products */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Top Products</Text>
          <TouchableOpacity onPress={() => router.push('/analytics/products' as any)}>
            <Text style={styles.seeAll}>See All</Text>
          </TouchableOpacity>
        </View>

        {topProducts.slice(0, 5).map((product, index) => (
          <View key={product.id} style={styles.productItem}>
            <View style={styles.productRank}>
              <Text style={styles.rankText}>#{index + 1}</Text>
            </View>
            <View style={styles.productInfo}>
              <Text style={styles.productName} numberOfLines={1}>
                {product.name}
              </Text>
              <Text style={styles.productUnits}>
                {product.units_sold} units sold
              </Text>
            </View>
            <View style={styles.productMetrics}>
              <Text style={styles.productRevenue}>
                {formatCurrency(product.revenue)}
              </Text>
              <View
                style={[
                  styles.trendBadge,
                  product.revenue_change >= 0
                    ? styles.trendPositive
                    : styles.trendNegative,
                ]}
              >
                <Ionicons
                  name={product.revenue_change >= 0 ? 'arrow-up' : 'arrow-down'}
                  size={12}
                  color={product.revenue_change >= 0 ? '#10B981' : '#EF4444'}
                />
                <Text
                  style={[
                    styles.trendText,
                    product.revenue_change >= 0
                      ? styles.trendTextPositive
                      : styles.trendTextNegative,
                  ]}
                >
                  {Math.abs(product.revenue_change).toFixed(1)}%
                </Text>
              </View>
            </View>
          </View>
        ))}
      </View>

      {/* Inventory Health */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Inventory Health</Text>
        
        <View style={styles.inventoryGrid}>
          <View style={styles.inventoryItem}>
            <View style={[styles.inventoryDot, { backgroundColor: '#10B981' }]} />
            <View style={styles.inventoryInfo}>
              <Text style={styles.inventoryLabel}>Healthy Stock</Text>
              <Text style={styles.inventoryValue}>
                {inventoryHealth?.healthy_stock_percent || 0}%
              </Text>
              <Text style={styles.inventorySubtext}>
                {inventoryHealth?.healthy_stock || 0} products
              </Text>
            </View>
          </View>

          <View style={styles.inventoryItem}>
            <View style={[styles.inventoryDot, { backgroundColor: '#F59E0B' }]} />
            <View style={styles.inventoryInfo}>
              <Text style={styles.inventoryLabel}>Low Stock</Text>
              <Text style={styles.inventoryValue}>
                {inventoryHealth?.low_stock_percent || 0}%
              </Text>
              <Text style={styles.inventorySubtext}>
                {inventoryHealth?.low_stock || 0} products
              </Text>
            </View>
          </View>

          <View style={styles.inventoryItem}>
            <View style={[styles.inventoryDot, { backgroundColor: '#EF4444' }]} />
            <View style={styles.inventoryInfo}>
              <Text style={styles.inventoryLabel}>Out of Stock</Text>
              <Text style={styles.inventoryValue}>
                {inventoryHealth?.out_of_stock_percent || 0}%
              </Text>
              <Text style={styles.inventorySubtext}>
                {inventoryHealth?.out_of_stock || 0} products
              </Text>
            </View>
          </View>
        </View>

        {inventoryHealth && inventoryHealth.dead_stock_percent > 0 && (
          <View style={styles.deadStockWarning}>
            <Ionicons name="warning" size={20} color="#F59E0B" />
            <Text style={styles.deadStockText}>
              {inventoryHealth.dead_stock_percent}% dead stock detected
            </Text>
          </View>
        )}
      </View>

      {/* Last Updated */}
      {lastUpdated && (
        <View style={styles.lastUpdated}>
          <Text style={styles.lastUpdatedText}>
            Last updated: {lastUpdated.toLocaleString('id-ID')}
          </Text>
        </View>
      )}

      <View style={{ height: 40 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    paddingTop: 50,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#111827',
  },
  subtitle: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 2,
  },
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEF2F2',
    margin: 16,
    padding: 12,
    borderRadius: 10,
    gap: 8,
  },
  errorText: {
    flex: 1,
    fontSize: 13,
    color: '#DC2626',
  },
  retryText: {
    fontSize: 13,
    color: '#DC2626',
    fontWeight: '600',
  },
  metricsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 16,
    gap: 12,
  },
  section: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 12,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  seeAll: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
  },
  productItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  productRank: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  rankText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#4F46E5',
  },
  productInfo: {
    flex: 1,
  },
  productName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 2,
  },
  productUnits: {
    fontSize: 12,
    color: '#6B7280',
  },
  productMetrics: {
    alignItems: 'flex-end',
  },
  productRevenue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 4,
  },
  trendBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 4,
  },
  trendPositive: {
    backgroundColor: '#ECFDF5',
  },
  trendNegative: {
    backgroundColor: '#FEF2F2',
  },
  trendText: {
    fontSize: 11,
    fontWeight: '600',
  },
  trendTextPositive: {
    color: '#10B981',
  },
  trendTextNegative: {
    color: '#EF4444',
  },
  inventoryGrid: {
    gap: 12,
  },
  inventoryItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  inventoryDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    marginRight: 12,
  },
  inventoryInfo: {
    flex: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  inventoryLabel: {
    fontSize: 13,
    color: '#6B7280',
  },
  inventoryValue: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  inventorySubtext: {
    fontSize: 11,
    color: '#9CA3AF',
  },
  deadStockWarning: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEF3C7',
    padding: 12,
    borderRadius: 10,
    marginTop: 8,
    gap: 8,
  },
  deadStockText: {
    flex: 1,
    fontSize: 13,
    color: '#92400E',
  },
  lastUpdated: {
    alignItems: 'center',
    paddingVertical: 16,
  },
  lastUpdatedText: {
    fontSize: 12,
    color: '#9CA3AF',
  },
});
