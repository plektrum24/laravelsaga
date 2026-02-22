import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import ProductCard from './ProductCard';

interface Product {
  id: number;
  name: string;
  price: number;
  image_url?: string;
  stock?: number;
  discount_percent?: number;
}

interface RelatedProductsProps {
  products?: Product[];
  isLoading?: boolean;
  onProductPress?: (product: Product) => void;
  onSeeAllPress?: () => void;
}

export default function RelatedProducts({
  products = [],
  isLoading = false,
  onProductPress,
  onSeeAllPress,
}: RelatedProductsProps) {
  if (!products || products.length === 0) {
    return null;
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Related Products</Text>
        <TouchableOpacity onPress={onSeeAllPress}>
          <Text style={styles.seeAll}>See All</Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {products.map((product) => (
          <View key={product.id} style={styles.cardContainer}>
            <ProductCard
              product={product}
              viewMode="grid"
              onPress={onProductPress}
            />
          </View>
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginTop: 24,
    borderTopWidth: 1,
    borderTopColor: '#F3F4F6',
    paddingTop: 16,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  seeAll: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
  },
  scrollContent: {
    flexDirection: 'row',
    gap: 8,
    paddingRight: 8,
  },
  cardContainer: {
    width: '48%',
  },
});
