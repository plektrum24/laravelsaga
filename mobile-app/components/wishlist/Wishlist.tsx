import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface WishlistItem {
  id: string;
  product: {
    id: string;
    name: string;
    price: number;
    image_url?: string;
    stock?: number;
  };
  added_at: string;
}

interface WishlistProps {
  items?: WishlistItem[];
  onAddToCart?: (item: WishlistItem) => void;
  onRemove?: (item: WishlistItem) => void;
  onProductPress?: (productId: string) => void;
}

export default function Wishlist({
  items = [],
  onAddToCart,
  onRemove,
  onProductPress,
}: WishlistProps) {
  const formatCurrency = (amount: number) => {
    return `Rp ${amount.toLocaleString('id-ID')}`;
  };

  if (items.length === 0) {
    return (
      <View style={styles.emptyContainer}>
        <Ionicons name="heart-dislike-outline" size={80} color="#D1D5DB" />
        <Text style={styles.emptyTitle}>Your wishlist is empty</Text>
        <Text style={styles.emptySubtitle}>
          Save items you love to your wishlist
        </Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Header Stats */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>My Wishlist</Text>
        <View style={styles.statsContainer}>
          <Text style={styles.statsText}>{items.length} items</Text>
        </View>
      </View>

      {/* Wishlist Items */}
      {items.map((item) => (
        <View key={item.id} style={styles.itemCard}>
          <TouchableOpacity
            style={styles.itemImageContainer}
            onPress={() => onProductPress?.(item.product.id)}
          >
            {item.product.image_url ? (
              <Image
                source={{ uri: item.product.image_url }}
                style={styles.itemImage}
                resizeMode="cover"
              />
            ) : (
              <View style={styles.itemImagePlaceholder}>
                <Ionicons name="image-outline" size={40} color="#9CA3AF" />
              </View>
            )}
            {item.product.stock === 0 && (
              <View style={styles.outOfStockBadge}>
                <Text style={styles.outOfStockText}>Out of Stock</Text>
              </View>
            )}
          </TouchableOpacity>

          <View style={styles.itemDetails}>
            <Text
              style={styles.itemName}
              numberOfLines={2}
              onPress={() => onProductPress?.(item.product.id)}
            >
              {item.product.name}
            </Text>
            <Text style={styles.itemPrice}>{formatCurrency(item.product.price)}</Text>
            <Text style={styles.addedDate}>
              Added {new Date(item.added_at).toLocaleDateString('id-ID')}
            </Text>

            <View style={styles.itemActions}>
              <TouchableOpacity
                style={[
                  styles.addToCartButton,
                  item.product.stock === 0 && styles.addToCartButtonDisabled,
                ]}
                onPress={() => onAddToCart?.(item)}
                disabled={item.product.stock === 0}
              >
                <Ionicons
                  name="cart"
                  size={18}
                  color={item.product.stock === 0 ? '#9CA3AF' : '#FFFFFF'}
                />
                <Text
                  style={[
                    styles.addToCartButtonText,
                    item.product.stock === 0 && styles.addToCartButtonTextDisabled,
                  ]}
                >
                  Add to Cart
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.removeButton}
                onPress={() => onRemove?.(item)}
              >
                <Ionicons name="trash-outline" size={20} color="#EF4444" />
              </TouchableOpacity>
            </View>
          </View>
        </View>
      ))}

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
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#111827',
  },
  statsContainer: {
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
  },
  statsText: {
    fontSize: 13,
    color: '#4F46E5',
    fontWeight: '600',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 32,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#111827',
    marginTop: 24,
  },
  emptySubtitle: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 8,
    textAlign: 'center',
  },
  itemCard: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginVertical: 8,
    borderRadius: 12,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  itemImageContainer: {
    width: 120,
    height: 120,
    backgroundColor: '#F3F4F6',
    position: 'relative',
  },
  itemImage: {
    width: '100%',
    height: '100%',
  },
  itemImagePlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  outOfStockBadge: {
    position: 'absolute',
    top: 8,
    left: 8,
    backgroundColor: '#EF4444',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  outOfStockText: {
    fontSize: 10,
    color: '#FFFFFF',
    fontWeight: '600',
  },
  itemDetails: {
    flex: 1,
    padding: 12,
  },
  itemName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 8,
    lineHeight: 18,
  },
  itemPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#4F46E5',
    marginBottom: 4,
  },
  addedDate: {
    fontSize: 11,
    color: '#9CA3AF',
    marginBottom: 12,
  },
  itemActions: {
    flexDirection: 'row',
    gap: 8,
  },
  addToCartButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#4F46E5',
    paddingVertical: 10,
    borderRadius: 8,
    gap: 6,
  },
  addToCartButtonDisabled: {
    backgroundColor: '#D1D5DB',
  },
  addToCartButtonText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  addToCartButtonTextDisabled: {
    color: '#9CA3AF',
  },
  removeButton: {
    width: 40,
    height: 40,
    borderRadius: 8,
    backgroundColor: '#FEF2F2',
    justifyContent: 'center',
    alignItems: 'center',
  },
});
