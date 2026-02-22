import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Image,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

interface Product {
  id: number;
  name: string;
  price: number;
  original_price?: number;
  image_url?: string;
  stock?: number;
  discount_percent?: number;
}

interface ProductSectionProps {
  title: string;
  products?: Product[];
  isLoading?: boolean;
  seeAllRoute?: string;
  onProductPress?: (product: Product) => void;
  onSeeAllPress?: () => void;
}

export default function ProductSection({
  title,
  products = [],
  isLoading = false,
  seeAllRoute = '/shop',
  onProductPress,
  onSeeAllPress,
}: ProductSectionProps) {
  const handleProductPress = (product: Product) => {
    if (onProductPress) {
      onProductPress(product);
    } else {
      // Default navigation to product detail
      router.push(`/product/${product.id}` as any);
    }
  };

  const handleSeeAllPress = () => {
    if (onSeeAllPress) {
      onSeeAllPress();
    } else {
      router.push(seeAllRoute as any);
    }
  };

  const renderProductCard = (product: Product) => (
    <TouchableOpacity
      key={product.id}
      style={styles.productCard}
      onPress={() => handleProductPress(product)}
      activeOpacity={0.7}
    >
      <View style={styles.productImageContainer}>
        {product.image_url ? (
          <Image
            source={{ uri: product.image_url }}
            style={styles.productImage}
            resizeMode="cover"
          />
        ) : (
          <View style={styles.productImagePlaceholder}>
            <Ionicons name="image-outline" size={40} color="#9CA3AF" />
          </View>
        )}
        
        {/* Discount Badge */}
        {product.discount_percent && product.discount_percent > 0 && (
          <View style={styles.discountBadge}>
            <Text style={styles.discountText}>
              {product.discount_percent}% OFF
            </Text>
          </View>
        )}

        {/* Stock Badge */}
        {product.stock !== undefined && product.stock <= 0 && (
          <View style={styles.outOfStockBadge}>
            <Text style={styles.outOfStockText}>OUT</Text>
          </View>
        )}
      </View>

      <View style={styles.productInfo}>
        <Text style={styles.productName} numberOfLines={2}>
          {product.name}
        </Text>
        
        <View style={styles.priceContainer}>
          <Text style={styles.productPrice}>
            Rp {product.price.toLocaleString('id-ID')}
          </Text>
          {product.original_price && product.original_price > product.price && (
            <Text style={styles.originalPrice}>
              Rp {product.original_price.toLocaleString('id-ID')}
            </Text>
          )}
        </View>

        {product.stock !== undefined && (
          <View style={styles.stockContainer}>
            {product.stock > 0 ? (
              product.stock <= 10 ? (
                <Text style={styles.lowStock}>
                  Only {product.stock} left
                </Text>
              ) : (
                <Text style={styles.inStock}>In Stock</Text>
              )
            ) : (
              <Text style={styles.outOfStock}>Out of Stock</Text>
            )}
          </View>
        )}
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>{title}</Text>
        <TouchableOpacity onPress={handleSeeAllPress}>
          <Text style={styles.seeAll}>See All</Text>
        </TouchableOpacity>
      </View>

      {isLoading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="small" color="#4F46E5" />
          <Text style={styles.loadingText}>Loading...</Text>
        </View>
      ) : products.length > 0 ? (
        <ScrollView 
          horizontal 
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.scrollContent}
        >
          {products.map((product) => renderProductCard(product))}
        </ScrollView>
      ) : (
        <View style={styles.emptyState}>
          <Ionicons name="store-outline" size={48} color="#9CA3AF" />
          <Text style={styles.emptyText}>No products yet</Text>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    paddingVertical: 16,
    marginBottom: 8,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
  },
  seeAll: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
  },
  scrollContent: {
    paddingHorizontal: 12,
  },
  productCard: {
    width: 160,
    marginHorizontal: 6,
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    overflow: 'hidden',
  },
  productImageContainer: {
    height: 160,
    backgroundColor: '#F3F4F6',
    position: 'relative',
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  productImagePlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  discountBadge: {
    position: 'absolute',
    top: 8,
    left: 8,
    backgroundColor: '#EF4444',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  discountText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: 'bold',
  },
  outOfStockBadge: {
    position: 'absolute',
    top: 8,
    right: 8,
    backgroundColor: '#6B7280',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  outOfStockText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: 'bold',
  },
  productInfo: {
    padding: 12,
  },
  productName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 8,
    lineHeight: 18,
  },
  priceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 4,
  },
  productPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#4F46E5',
    marginRight: 6,
  },
  originalPrice: {
    fontSize: 12,
    color: '#9CA3AF',
    textDecorationLine: 'line-through',
  },
  stockContainer: {
    marginTop: 4,
  },
  inStock: {
    fontSize: 11,
    color: '#10B981',
    fontWeight: '500',
  },
  lowStock: {
    fontSize: 11,
    color: '#F59E0B',
    fontWeight: '500',
  },
  outOfStock: {
    fontSize: 11,
    color: '#EF4444',
    fontWeight: '500',
  },
  loadingContainer: {
    padding: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingText: {
    color: '#9CA3AF',
    fontSize: 14,
    marginTop: 8,
  },
  emptyState: {
    padding: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyText: {
    color: '#9CA3AF',
    fontSize: 14,
    marginTop: 8,
  },
});
