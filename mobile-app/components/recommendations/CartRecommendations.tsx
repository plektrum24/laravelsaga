import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as RecommendationsService from '../../services/recommendations.service';

interface CartRecommendationsProps {
  cartItemIds: string[];
  onProductPress?: (productId: string) => void;
  onAddToCart?: (productId: string) => void;
}

export default function CartRecommendations({
  cartItemIds,
  onProductPress,
  onAddToCart,
}: CartRecommendationsProps) {
  const [products, setProducts] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (cartItemIds.length > 0) {
      loadRecommendations();
    }
  }, [cartItemIds]);

  const loadRecommendations = async () => {
    setIsLoading(true);
    try {
      const data = await RecommendationsService.getCartRecommendations(cartItemIds);
      setProducts(data);
    } catch (error) {
      console.error('Error loading cart recommendations:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const formatPrice = (price: number) => {
    return `Rp ${price.toLocaleString('id-ID')}`;
  };

  if (isLoading || products.length === 0) {
    return null;
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Complete Your Purchase</Text>
        <Text style={styles.subtitle}>Don't forget these items</Text>
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {products.map((product) => (
          <TouchableOpacity
            key={product.id}
            style={styles.productCard}
            onPress={() => onProductPress?.(product.id)}
            activeOpacity={0.7}
          >
            <View style={styles.imageContainer}>
              <Ionicons name="image-outline" size={40} color="#9CA3AF" />
            </View>

            <View style={styles.productInfo}>
              <Text style={styles.productName} numberOfLines={2}>
                {product.name}
              </Text>
              <Text style={styles.productPrice}>{formatPrice(product.price)}</Text>
              {product.reason && (
                <Text style={styles.productReason}>{product.reason}</Text>
              )}
            </View>

            <TouchableOpacity
              style={styles.addToCartButton}
              onPress={(e) => {
                e.stopPropagation();
                onAddToCart?.(product.id);
              }}
            >
              <Ionicons name="cart" size={18} color="#FFFFFF" />
            </TouchableOpacity>
          </TouchableOpacity>
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FEF3C7',
    paddingVertical: 16,
    paddingHorizontal: 16,
    marginBottom: 8,
  },
  header: {
    marginBottom: 12,
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    color: '#92400E',
    marginBottom: 2,
  },
  subtitle: {
    fontSize: 13,
    color: '#B45309',
  },
  scrollContent: {
    gap: 12,
  },
  productCard: {
    width: 160,
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    overflow: 'hidden',
    position: 'relative',
  },
  imageContainer: {
    height: 120,
    backgroundColor: '#F3F4F6',
    justifyContent: 'center',
    alignItems: 'center',
  },
  productInfo: {
    padding: 12,
  },
  productName: {
    fontSize: 13,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 4,
    height: 32,
  },
  productPrice: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4F46E5',
    marginBottom: 4,
  },
  productReason: {
    fontSize: 10,
    color: '#6B7280',
    fontStyle: 'italic',
  },
  addToCartButton: {
    position: 'absolute',
    bottom: 12,
    right: 12,
    width: 32,
    height: 32,
    backgroundColor: '#4F46E5',
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
  },
});
