import React, { useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  RefreshControl,
} from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../hooks/useAuth';
import { useProductStore } from '../../stores/product.store';
import { Product } from '../../types/api.types';
import PromotionalBanner from '../../components/home/PromotionalBanner';
import CategoriesGrid from '../../components/home/CategoriesGrid';
import ProductSection from '../../components/home/ProductSection';
import * as HomeService from '../../services/home.service';

export default function HomeScreen() {
  const { user, isAuthenticated } = useAuth();
  const {
    featuredProducts,
    categories,
    fetchFeaturedProducts,
    fetchCategories,
    isLoading
  } = useProductStore();
  
  const [refreshing, setRefreshing] = React.useState(false);
  const [banners, setBanners] = React.useState<any[]>([]);
  const [newArrivals, setNewArrivals] = React.useState<any[]>([]);
  const [bestSellers, setBestSellers] = React.useState<any[]>([]);
  const [homeLoading, setHomeLoading] = React.useState(false);

  useEffect(() => {
    fetchHomeData();
  }, []);

  const fetchHomeData = async () => {
    setHomeLoading(true);
    try {
      // Fetch banners
      const bannersData = await HomeService.getBanners();
      setBanners(bannersData);

      // Fetch other data in parallel
      await Promise.all([
        fetchFeaturedProducts(),
        fetchCategories(),
        HomeService.getNewArrivals(10).then(setNewArrivals),
        HomeService.getBestSellers(10).then(setBestSellers),
      ]);
    } catch (error) {
      console.error('Error fetching home data:', error);
    } finally {
      setHomeLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchHomeData();
    setRefreshing(false);
  };

  const quickActions = [
    { icon: 'barcode', label: 'Scan', color: '#4F46E5', route: '/search' },
    { icon: 'time', label: 'Orders', color: '#10B981', route: '/order/history' },
    { icon: 'location', label: 'Stores', color: '#F59E0B', route: '/stores' },
    { icon: 'headset', label: 'Support', color: '#3B82F6', route: '/support' },
  ];

  const handleQuickAction = (route: string) => {
    if (isAuthenticated) {
      router.push(route as any);
    } else {
      router.push('/(auth)/login' as any);
    }
  };

  return (
    <ScrollView
      style={styles.container}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
      }
    >
      {/* Welcome Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.greeting}>
            {isAuthenticated ? `Hello, ${user?.name?.split(' ')[0] || 'there'}!` : 'Welcome!'}
          </Text>
          <Text style={styles.subGreeting}>
            {isAuthenticated ? 'Ready to shop?' : 'Sign in for best experience'}
          </Text>
        </View>
        <TouchableOpacity 
          style={styles.notificationBtn}
          onPress={() => isAuthenticated ? router.push('/notifications' as any) : router.push('/(auth)/login' as any)}
        >
          <Ionicons name="notifications-outline" size={24} color="#6B7280" />
        </TouchableOpacity>
      </View>

      {/* Quick Actions */}
      <View style={styles.quickActions}>
        {quickActions.map((action, index) => (
          <TouchableOpacity 
            key={index} 
            style={styles.quickAction}
            onPress={() => handleQuickAction(action.route)}
          >
            <View style={[styles.quickActionIcon, { backgroundColor: `${action.color}15` }]}>
              <Ionicons name={action.icon as any} size={24} color={action.color} />
            </View>
            <Text style={styles.quickActionLabel}>{action.label}</Text>
          </TouchableOpacity>
        ))}
      </View>

      {/* Promotional Banners */}
      <PromotionalBanner 
        banners={banners}
        autoScroll={true}
        autoScrollInterval={5000}
      />

      {/* Categories */}
      <CategoriesGrid 
        categories={categories}
        title="Categories"
      />

      {/* Featured Products */}
      <ProductSection
        title="Featured Products"
        products={featuredProducts}
        isLoading={isLoading || homeLoading}
        seeAllRoute="/shop?featured=1"
      />

      {/* New Arrivals */}
      <ProductSection
        title="New Arrivals"
        products={newArrivals}
        isLoading={homeLoading}
        seeAllRoute="/shop?sort=newest"
      />

      {/* Best Sellers */}
      <ProductSection
        title="Best Sellers"
        products={bestSellers}
        isLoading={homeLoading}
        seeAllRoute="/shop?sort=best_seller"
      />

      {/* Bottom Spacing */}
      <View style={{ height: 100 }} />
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
    padding: 20,
    paddingTop: 60,
    backgroundColor: '#FFFFFF',
  },
  greeting: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#111827',
  },
  subGreeting: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 4,
  },
  notificationBtn: {
    padding: 8,
  },
  quickActions: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    padding: 16,
    backgroundColor: '#FFFFFF',
    marginTop: 8,
  },
  quickAction: {
    alignItems: 'center',
  },
  quickActionIcon: {
    width: 56,
    height: 56,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  quickActionLabel: {
    fontSize: 12,
    color: '#6B7280',
    fontWeight: '500',
  },
});
