import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface Product {
  id: number;
  name: string;
  price: number;
  original_price?: number;
  image_url?: string;
  stock?: number;
  discount_percent?: number;
  is_featured?: boolean;
}

interface ProductCardProps {
  product: Product;
  viewMode?: 'grid' | 'list';
  onPress?: (product: Product) => void;
  onAddToCart?: (product: Product) => void;
}

export default function ProductCard({
  product,
  viewMode = 'grid',
  onPress,
  onAddToCart,
}: ProductCardProps) {
  const handlePress = () => {
    onPress?.(product);
  };

  const handleAddToCart = () => {
    onAddToCart?.(product);
  };

  if (viewMode === 'list') {
    return (
      <TouchableOpacity
        style={styles.listCard}
        onPress={handlePress}
        activeOpacity={0.7}
      >
        <View style={styles.listImageContainer}>
          {product.image_url ? (
            <Image
              source={{ uri: product.image_url }}
              style={styles.listImage}
              resizeMode="cover"
            />
          ) : (
            <View style={styles.listImagePlaceholder}>
              <Ionicons name="image-outline" size={40} color="#9CA3AF" />
            </View>
          )}
          {product.discount_percent && product.discount_percent > 0 && (
            <View style={styles.discountBadge}>
              <Text style={styles.discountText}>-{product.discount_percent}%</Text>
            </View>
          )}
        </View>

        <View style={styles.listContent}>
          <Text style={styles.listName} numberOfLines={2}>
            {product.name}
          </Text>

          <View style={styles.listPriceContainer}>
            <Text style={styles.listPrice}>
              Rp {product.price.toLocaleString('id-ID')}
            </Text>
            {product.original_price && product.original_price > product.price && (
              <Text style={styles.listOriginalPrice}>
                Rp {product.original_price.toLocaleString('id-ID')}
              </Text>
            )}
          </View>

          <View style={styles.listFooter}>
            {product.stock !== undefined && (
              <Text
                style={[
                  styles.listStock,
                  product.stock > 0 ? styles.inStock : styles.outOfStock,
                ]}
              >
                {product.stock > 0
                  ? product.stock <= 10
                    ? `Only ${product.stock} left`
                    : 'In Stock'
                  : 'Out of Stock'}
              </Text>
            )}

            <TouchableOpacity
              style={[
                styles.listAddToCartBtn,
                product.stock === 0 && styles.listAddToCartBtnDisabled,
              ]}
              onPress={handleAddToCart}
              disabled={product.stock === 0}
            >
              <Ionicons
                name={product.stock === 0 ? 'lock-closed' : 'cart'}
                size={18}
                color="#FFFFFF"
              />
            </TouchableOpacity>
          </View>
        </View>
      </TouchableOpacity>
    );
  }

  // Grid view (default)
  return (
    <TouchableOpacity
      style={styles.gridCard}
      onPress={handlePress}
      activeOpacity={0.7}
    >
      <View style={styles.gridImageContainer}>
        {product.image_url ? (
          <Image
            source={{ uri: product.image_url }}
            style={styles.gridImage}
            resizeMode="cover"
          />
        ) : (
          <View style={styles.gridImagePlaceholder}>
            <Ionicons name="image-outline" size={40} color="#9CA3AF" />
          </View>
        )}

        {/* Badges */}
        {product.is_featured && (
          <View style={styles.featuredBadge}>
            <Ionicons name="star" size={12} color="#FFFFFF" />
          </View>
        )}

        {product.discount_percent && product.discount_percent > 0 && (
          <View style={styles.discountBadge}>
            <Text style={styles.discountText}>-{product.discount_percent}%</Text>
          </View>
        )}

        {product.stock !== undefined && product.stock <= 0 && (
          <View style={styles.outOfStockBadge}>
            <Text style={styles.outOfStockBadgeText}>OUT</Text>
          </View>
        )}
      </View>

      <View style={styles.gridContent}>
        <Text style={styles.gridName} numberOfLines={2}>
          {product.name}
        </Text>

        <View style={styles.gridPriceContainer}>
          <Text style={styles.gridPrice}>
            Rp {product.price.toLocaleString('id-ID')}
          </Text>
          {product.original_price && product.original_price > product.price && (
            <Text style={styles.gridOriginalPrice}>
              Rp {product.original_price.toLocaleString('id-ID')}
            </Text>
          )}
        </View>

        <View style={styles.gridFooter}>
          {product.stock !== undefined && (
            <Text
              style={[
                styles.gridStock,
                product.stock > 0 ? styles.inStock : styles.outOfStock,
              ]}
              numberOfLines={1}
            >
              {product.stock > 0
                ? product.stock <= 10
                  ? `${product.stock} left`
                  : 'In Stock'
                : 'Out'}
            </Text>
          )}

          <TouchableOpacity
            style={[
              styles.gridAddToCartBtn,
              product.stock === 0 && styles.gridAddToCartBtnDisabled,
            ]}
            onPress={handleAddToCart}
            disabled={product.stock === 0}
          >
            <Ionicons
              name={product.stock === 0 ? 'lock-closed' : 'cart'}
              size={16}
              color="#FFFFFF"
            />
          </TouchableOpacity>
        </View>
      </View>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  // Grid View Styles
  gridCard: {
    width: '48%',
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    marginBottom: 8,
  },
  gridImageContainer: {
    height: 160,
    backgroundColor: '#F3F4F6',
    position: 'relative',
  },
  gridImage: {
    width: '100%',
    height: '100%',
  },
  gridImagePlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  featuredBadge: {
    position: 'absolute',
    top: 8,
    left: 8,
    backgroundColor: '#F59E0B',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
  },
  discountBadge: {
    position: 'absolute',
    top: 8,
    right: 8,
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
    bottom: 8,
    right: 8,
    backgroundColor: '#6B7280',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  outOfStockBadgeText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: 'bold',
  },
  gridContent: {
    padding: 12,
  },
  gridName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 8,
    minHeight: 36,
    lineHeight: 18,
  },
  gridPriceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  gridPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#4F46E5',
    marginRight: 6,
  },
  gridOriginalPrice: {
    fontSize: 12,
    color: '#9CA3AF',
    textDecorationLine: 'line-through',
  },
  gridFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  gridStock: {
    fontSize: 11,
    fontWeight: '500',
    flex: 1,
  },
  inStock: {
    color: '#10B981',
  },
  outOfStock: {
    color: '#EF4444',
  },
  gridAddToCartBtn: {
    width: 32,
    height: 32,
    backgroundColor: '#4F46E5',
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
  },
  gridAddToCartBtnDisabled: {
    backgroundColor: '#9CA3AF',
  },

  // List View Styles
  listCard: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    marginBottom: 8,
    height: 140,
  },
  listImageContainer: {
    width: 140,
    height: '100%',
    backgroundColor: '#F3F4F6',
    position: 'relative',
  },
  listImage: {
    width: '100%',
    height: '100%',
  },
  listImagePlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  listContent: {
    flex: 1,
    padding: 12,
  },
  listName: {
    fontSize: 15,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 8,
    lineHeight: 20,
  },
  listPriceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  listPrice: {
    fontSize: 17,
    fontWeight: 'bold',
    color: '#4F46E5',
    marginRight: 6,
  },
  listOriginalPrice: {
    fontSize: 13,
    color: '#9CA3AF',
    textDecorationLine: 'line-through',
  },
  listFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 'auto',
  },
  listStock: {
    fontSize: 12,
    fontWeight: '500',
    flex: 1,
  },
  listAddToCartBtn: {
    width: 40,
    height: 40,
    backgroundColor: '#4F46E5',
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  listAddToCartBtnDisabled: {
    backgroundColor: '#9CA3AF',
  },
});
