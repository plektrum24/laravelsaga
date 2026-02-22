import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface OrderItem {
  id: string;
  product: {
    name: string;
    image_url?: string;
    price: number;
  };
  quantity: number;
  subtotal: number;
}

interface OrderReviewProps {
  items: OrderItem[];
  subtotal: number;
  shippingFee: number;
  discount?: number;
  total: number;
  address?: {
    name: string;
    phone: string;
    address: string;
    city: string;
  };
  deliveryMethod?: {
    name: string;
    estimated_time: string;
    fee: number;
  };
  paymentMethod?: {
    name: string;
    type: string;
  };
  onPlaceOrder?: () => void;
  isPlacingOrder?: boolean;
}

export default function OrderReview({
  items,
  subtotal,
  shippingFee,
  discount = 0,
  total,
  address,
  deliveryMethod,
  paymentMethod,
  onPlaceOrder,
  isPlacingOrder = false,
}: OrderReviewProps) {
  const formatCurrency = (amount: number) => {
    return `Rp ${amount.toLocaleString('id-ID')}`;
  };

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Delivery Address */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <Ionicons name="location" size={20} color="#4F46E5" />
          <Text style={styles.sectionTitle}>Delivery Address</Text>
        </View>
        {address ? (
          <View style={styles.addressContainer}>
            <Text style={styles.addressName}>{address.name}</Text>
            <Text style={styles.addressPhone}>{address.phone}</Text>
            <Text style={styles.addressFull}>{address.address}</Text>
            <Text style={styles.addressCity}>{address.city}</Text>
          </View>
        ) : (
          <Text style={styles.emptyText}>No address selected</Text>
        )}
      </View>

      {/* Delivery Method */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <Ionicons name="bicycle" size={20} color="#4F46E5" />
          <Text style={styles.sectionTitle}>Delivery Method</Text>
        </View>
        {deliveryMethod ? (
          <View style={styles.methodContainer}>
            <View style={styles.methodRow}>
              <Text style={styles.methodName}>{deliveryMethod.name}</Text>
              {deliveryMethod.fee === 0 ? (
                <Text style={styles.freeText}>FREE</Text>
              ) : (
                <Text style={styles.methodFee}>{formatCurrency(deliveryMethod.fee)}</Text>
              )}
            </View>
            <Text style={styles.methodTime}>{deliveryMethod.estimated_time}</Text>
          </View>
        ) : (
          <Text style={styles.emptyText}>No delivery method selected</Text>
        )}
      </View>

      {/* Payment Method */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <Ionicons name="card" size={20} color="#4F46E5" />
          <Text style={styles.sectionTitle}>Payment Method</Text>
        </View>
        {paymentMethod ? (
          <View style={styles.paymentContainer}>
            <Text style={styles.paymentName}>{paymentMethod.name}</Text>
            <Text style={styles.paymentType}>{paymentMethod.type}</Text>
          </View>
        ) : (
          <Text style={styles.emptyText}>No payment method selected</Text>
        )}
      </View>

      {/* Order Items */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <Ionicons name="cart" size={20} color="#4F46E5" />
          <Text style={styles.sectionTitle}>Order Items ({items.length})</Text>
        </View>
        {items.map((item) => (
          <View key={item.id} style={styles.itemRow}>
            <View style={styles.itemInfo}>
              <Text style={styles.itemName}>{item.product.name}</Text>
              <Text style={styles.itemQuantity}>x {item.quantity}</Text>
            </View>
            <Text style={styles.itemPrice}>{formatCurrency(item.subtotal)}</Text>
          </View>
        ))}
      </View>

      {/* Price Summary */}
      <View style={styles.summarySection}>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Subtotal</Text>
          <Text style={styles.summaryValue}>{formatCurrency(subtotal)}</Text>
        </View>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Shipping</Text>
          <Text style={styles.summaryValue}>
            {shippingFee === 0 ? 'FREE' : formatCurrency(shippingFee)}
          </Text>
        </View>
        {discount > 0 && (
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Discount</Text>
            <Text style={styles.discountValue}>- {formatCurrency(discount)}</Text>
          </View>
        )}
        <View style={styles.divider} />
        <View style={styles.totalRow}>
          <Text style={styles.totalLabel}>Total</Text>
          <Text style={styles.totalValue}>{formatCurrency(total)}</Text>
        </View>
      </View>

      {/* Place Order Button */}
      <TouchableOpacity
        style={[
          styles.placeOrderButton,
          isPlacingOrder && styles.placeOrderButtonDisabled,
        ]}
        onPress={onPlaceOrder}
        disabled={isPlacingOrder}
      >
        {isPlacingOrder ? (
          <ActivityIndicator color="#FFFFFF" />
        ) : (
          <>
            <Text style={styles.placeOrderText}>Place Order</Text>
            <Ionicons name="lock-closed" size={18} color="#FFFFFF" />
          </>
        )}
      </TouchableOpacity>

      {/* Security Notice */}
      <View style={styles.securityNotice}>
        <Ionicons name="shield-checkmark" size={16} color="#10B981" />
        <Text style={styles.securityText}>
          Your order is secure and encrypted
        </Text>
      </View>

      <View style={{ height: 40 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  section: {
    backgroundColor: '#FFFFFF',
    padding: 16,
    marginBottom: 8,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  addressContainer: {
    backgroundColor: '#F9FAFB',
    padding: 12,
    borderRadius: 8,
  },
  addressName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 4,
  },
  addressPhone: {
    fontSize: 13,
    color: '#6B7280',
    marginBottom: 4,
  },
  addressFull: {
    fontSize: 13,
    color: '#6B7280',
    marginBottom: 4,
  },
  addressCity: {
    fontSize: 13,
    color: '#6B7280',
  },
  emptyText: {
    fontSize: 13,
    color: '#9CA3AF',
    fontStyle: 'italic',
  },
  methodContainer: {
    backgroundColor: '#F9FAFB',
    padding: 12,
    borderRadius: 8,
  },
  methodRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  methodName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  methodFee: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4F46E5',
  },
  freeText: {
    fontSize: 12,
    color: '#10B981',
    fontWeight: '700',
  },
  methodTime: {
    fontSize: 12,
    color: '#9CA3AF',
  },
  paymentContainer: {
    backgroundColor: '#F9FAFB',
    padding: 12,
    borderRadius: 8,
  },
  paymentName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  paymentType: {
    fontSize: 12,
    color: '#6B7280',
    textTransform: 'capitalize',
  },
  itemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  itemInfo: {
    flex: 1,
  },
  itemName: {
    fontSize: 14,
    color: '#111827',
    fontWeight: '500',
  },
  itemQuantity: {
    fontSize: 12,
    color: '#9CA3AF',
    marginTop: 2,
  },
  itemPrice: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  summarySection: {
    backgroundColor: '#FFFFFF',
    padding: 16,
    marginBottom: 8,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  summaryLabel: {
    fontSize: 14,
    color: '#6B7280',
  },
  summaryValue: {
    fontSize: 14,
    color: '#111827',
    fontWeight: '500',
  },
  discountValue: {
    fontSize: 14,
    color: '#10B981',
    fontWeight: '600',
  },
  divider: {
    height: 1,
    backgroundColor: '#E5E7EB',
    marginVertical: 12,
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  totalLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  totalValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#4F46E5',
  },
  placeOrderButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#4F46E5',
    margin: 16,
    paddingVertical: 16,
    borderRadius: 12,
    gap: 8,
    shadowColor: '#4F46E5',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  placeOrderButtonDisabled: {
    opacity: 0.6,
  },
  placeOrderText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  securityNotice: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#ECFDF5',
    marginHorizontal: 16,
    padding: 12,
    borderRadius: 8,
    gap: 6,
  },
  securityText: {
    fontSize: 12,
    color: '#059669',
    fontWeight: '500',
  },
});
