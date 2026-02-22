import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Image,
  Animated,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import Swipeable from 'react-native-gesture-handler/Swipeable';

interface CartItemProps {
  item: {
    id: string;
    product: {
      name: string;
      images?: Array<{ url: string }>;
    };
    price: number;
    quantity: number;
    unit?: string;
  };
  onQuantityChange?: (itemId: string, quantity: number) => void;
  onRemove?: (itemId: string) => void;
  isUpdating?: boolean;
}

export default function CartItem({
  item,
  onQuantityChange,
  onRemove,
  isUpdating = false,
}: CartItemProps) {
  const swipeableRef = React.useRef<Swipeable>(null);

  const handleDecrease = () => {
    if (item.quantity > 1) {
      onQuantityChange?.(item.id, item.quantity - 1);
    }
  };

  const handleIncrease = () => {
    onQuantityChange?.(item.id, item.quantity + 1);
  };

  const handleRemove = () => {
    onRemove?.(item.id);
    swipeableRef.current?.close();
  };

  const renderRightActions = (progress: Animated.AnimatedInterpolation<number>, dragX: Animated.AnimatedInterpolation<number>) => {
    return (
      <TouchableOpacity
        style={styles.deleteButton}
        onPress={handleRemove}
        activeOpacity={0.8}
      >
        <Ionicons name="trash" size={24} color="#FFFFFF" />
        <Text style={styles.deleteButtonText}>Delete</Text>
      </TouchableOpacity>
    );
  };

  const subtotal = item.price * item.quantity;

  return (
    <Swipeable
      ref={swipeableRef}
      renderRightActions={renderRightActions}
      rightThreshold={40}
      overshootRight={false}
    >
      <View style={styles.container}>
        {/* Product Image */}
        <View style={styles.imageContainer}>
          {item.product.images?.[0]?.url ? (
            <Image
              source={{ uri: item.product.images[0].url }}
              style={styles.image}
              resizeMode="cover"
            />
          ) : (
            <View style={styles.imagePlaceholder}>
              <Ionicons name="image-outline" size={32} color="#9CA3AF" />
            </View>
          )}
        </View>

        {/* Product Details */}
        <View style={styles.detailsContainer}>
          <Text style={styles.productName} numberOfLines={2}>
            {item.product.name}
          </Text>
          
          {item.unit && (
            <Text style={styles.unitText}>{item.unit}</Text>
          )}
          
          <Text style={styles.price}>
            Rp {item.price.toLocaleString('id-ID')}
          </Text>

          {/* Quantity Control */}
          <View style={styles.quantityContainer}>
            <TouchableOpacity
              style={[
                styles.quantityBtn,
                item.quantity <= 1 && styles.quantityBtnDisabled,
              ]}
              onPress={handleDecrease}
              disabled={isUpdating || item.quantity <= 1}
            >
              <Ionicons
                name="remove"
                size={16}
                color={item.quantity <= 1 ? '#9CA3AF' : '#111827'}
              />
            </TouchableOpacity>
            
            <Text style={styles.quantityText}>{item.quantity}</Text>
            
            <TouchableOpacity
              style={styles.quantityBtn}
              onPress={handleIncrease}
              disabled={isUpdating}
            >
              <Ionicons name="add" size={16} color="#111827" />
            </TouchableOpacity>
          </View>
        </View>

        {/* Subtotal */}
        <View style={styles.subtotalContainer}>
          <Text style={styles.subtotalLabel}>Subtotal</Text>
          <Text style={styles.subtotalAmount}>
            Rp {subtotal.toLocaleString('id-ID')}
          </Text>
        </View>
      </View>
    </Swipeable>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 12,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  imageContainer: {
    width: 80,
    height: 80,
    borderRadius: 8,
    backgroundColor: '#F3F4F6',
    overflow: 'hidden',
  },
  image: {
    width: '100%',
    height: '100%',
  },
  imagePlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  detailsContainer: {
    flex: 1,
    marginLeft: 12,
    justifyContent: 'space-between',
  },
  productName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 4,
    lineHeight: 18,
  },
  unitText: {
    fontSize: 11,
    color: '#9CA3AF',
    marginBottom: 4,
  },
  price: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4F46E5',
    marginBottom: 8,
  },
  quantityContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F3F4F6',
    borderRadius: 8,
    paddingHorizontal: 4,
    alignSelf: 'flex-start',
  },
  quantityBtn: {
    width: 28,
    height: 28,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderRadius: 6,
  },
  quantityBtnDisabled: {
    opacity: 0.5,
  },
  quantityText: {
    width: 32,
    textAlign: 'center',
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  subtotalContainer: {
    justifyContent: 'space-between',
    paddingLeft: 12,
    borderLeftWidth: 1,
    borderLeftColor: '#E5E7EB',
  },
  subtotalLabel: {
    fontSize: 12,
    color: '#6B7280',
    marginBottom: 4,
  },
  subtotalAmount: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  deleteButton: {
    backgroundColor: '#EF4444',
    justifyContent: 'center',
    alignItems: 'center',
    width: 80,
    height: '100%',
    borderRadius: 12,
    marginRight: 8,
  },
  deleteButtonText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '600',
    marginTop: 4,
  },
});
