import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

interface RecommendationCarouselProps {
  title: string;
  subtitle?: string;
  products: Array<{
    id: string;
    name: string;
    price: number;
    image_url?: string;
    score?: number;
    reason?: string;
  }>;
  onViewAll?: () => void;
  onProductPress?: (productId: string) => void;
  onAddToCart?: (productId: string) => void;
  isLoading?: boolean;
}

export default function RecommendationCarousel({
  title,
  subtitle,
  products = [],
  onViewAll,
  onProductPress,
  onAddToCart,
  isLoading = false,
}: RecommendationCarouselProps) {
  const handleProductPress = (productId: string) => {
    if (onProductPress) {
      onProductPress(productId);
    } else {
      router.push(`/product/${productId}` as any);
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
        <View>
          <Text style={styles.title}>{title}</Text>
          {subtitle && <Text style={styles.subtitle}>{subtitle}</Text>}
        </View>
        {onViewAll && (
          <TouchableOpacity onPress={onViewAll}>
            <Text style={styles.viewAll}>View All</Text>
            <Ionicons name="chevron-forward" size={16} color="#4F46E5" />
          </TouchableOpacity>
        )}
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
            onPress={() => handleProductPress(product.id)}
            activeOpacity={0.7}
          >
            <View style={styles.imageContainer}>
              {product.image_url ? (
                <View style={styles.imagePlaceholder}>
                  <Ionicons name="image-outline" size={40} color="#9CA3AF" />
                </View>
              ) : (
                <View style={styles.imagePlaceholder}>
                  <Ionicons name="image-outline" size={40} color="#9CA3AF" />
                </View>
              )}
              {product.score && product.score > 0.9 && (
                <View style={styles.scoreBadge}>
                  <Text style={styles.scoreText}>{(product.score * 100).toFixed(0)}% match</Text>
                </View>
              )}
            </View>

            <View style={styles.productInfo}>
              <Text style={styles.productName} numberOfLines={2}>
                {product.name}
              </Text>
              <Text style={styles.productPrice}>{formatPrice(product.price)}</Text>
              {product.reason && (
                <Text style={styles.productReason} numberOfLines={1}>
                  {product.reason}
                </Text>
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
    backgroundColor: '#FFFFFF',
    paddingVertical: 16,
    marginBottom: 8,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    marginBottom: 12,
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  subtitle: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 2,
  },
  viewAll: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
    flexDirection: 'row',
    alignItems: 'center',
  },
  scrollContent: {
    paddingHorizontal: 16,
  },
  productCard: {
    width: 160,
    marginRight: 12,
    backgroundColor: '#F9FAFB',
    borderRadius: 12,
    overflow: 'hidden',
    position: 'relative',
  },
  imageContainer: {
    height: 140,
    backgroundColor: '#F3F4F6',
    position: 'relative',
  },
  imagePlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scoreBadge: {
    position: 'absolute',
    top: 8,
    right: 8,
    backgroundColor: '#10B981',
    paddingHorizontal: 6,
    paddingVertical: 3,
    borderRadius: 4,
  },
  scoreText: {
    fontSize: 10,
    color: '#FFFFFF',
    fontWeight: '600',
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
