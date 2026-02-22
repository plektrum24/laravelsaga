import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface CartSummaryProps {
  subtotal: number;
  shippingFee?: number;
  discount?: number;
  tax?: number;
  isLoading?: boolean;
  onCheckout?: () => void;
  onApplyPromo?: (code: string) => void;
}

export default function CartSummary({
  subtotal,
  shippingFee = 0,
  discount = 0,
  tax = 0,
  isLoading = false,
  onCheckout,
  onApplyPromo,
}: CartSummaryProps) {
  const [promoCode, setPromoCode] = useState('');
  const [isApplyingPromo, setIsApplyingPromo] = useState(false);

  const total = subtotal + shippingFee + tax - discount;

  const handleApplyPromo = () => {
    if (!promoCode.trim() || !onApplyPromo) return;
    
    setIsApplyingPromo(true);
    onApplyPromo(promoCode.trim());
    // Reset after applying (will be handled by parent)
    setTimeout(() => setIsApplyingPromo(false), 1000);
  };

  const formatCurrency = (amount: number) => {
    return `Rp ${amount.toLocaleString('id-ID')}`;
  };

  return (
    <View style={styles.container}>
      {/* Promo Code Input */}
      {onApplyPromo && (
        <View style={styles.promoContainer}>
          <View style={styles.promoInputContainer}>
            <Ionicons name="pricetag-outline" size={20} color="#9CA3AF" />
            <TextInput
              style={styles.promoInput}
              placeholder="Promo code"
              placeholderTextColor="#9CA3AF"
              value={promoCode}
              onChangeText={setPromoCode}
              autoCapitalize="none"
              autoCorrect={false}
            />
          </View>
          <TouchableOpacity
            style={[
              styles.applyPromoBtn,
              (!promoCode.trim() || isApplyingPromo) && styles.applyPromoBtnDisabled,
            ]}
            onPress={handleApplyPromo}
            disabled={!promoCode.trim() || isApplyingPromo}
          >
            {isApplyingPromo ? (
              <ActivityIndicator size="small" color="#4F46E5" />
            ) : (
              <Text style={styles.applyPromoText}>Apply</Text>
            )}
          </TouchableOpacity>
        </View>
      )}

      {/* Summary Rows */}
      <View style={styles.summaryRow}>
        <Text style={styles.summaryLabel}>Subtotal</Text>
        <Text style={styles.summaryValue}>{formatCurrency(subtotal)}</Text>
      </View>

      {shippingFee > 0 && (
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Shipping</Text>
          <Text style={styles.summaryValue}>{formatCurrency(shippingFee)}</Text>
        </View>
      )}

      {shippingFee === 0 && (
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Shipping</Text>
          <Text style={styles.shippingFree}>Calculated at checkout</Text>
        </View>
      )}

      {tax > 0 && (
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Tax (10%)</Text>
          <Text style={styles.summaryValue}>{formatCurrency(tax)}</Text>
        </View>
      )}

      {discount > 0 && (
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Discount</Text>
          <Text style={styles.discountValue}>- {formatCurrency(discount)}</Text>
        </View>
      )}

      {/* Divider */}
      <View style={styles.divider} />

      {/* Total */}
      <View style={styles.totalRow}>
        <Text style={styles.totalLabel}>Total</Text>
        <View style={styles.totalContainer}>
          <Text style={styles.totalValue}>{formatCurrency(total)}</Text>
          {discount > 0 && (
            <View style={styles.savingsBadge}>
              <Text style={styles.savingsText}>
                Save {formatCurrency(discount)}
              </Text>
            </View>
          )}
        </View>
      </View>

      {/* Checkout Button */}
      <TouchableOpacity
        style={[
          styles.checkoutButton,
          isLoading && styles.checkoutButtonDisabled,
        ]}
        onPress={onCheckout}
        disabled={isLoading}
      >
        {isLoading ? (
          <ActivityIndicator color="#FFFFFF" />
        ) : (
          <>
            <Text style={styles.checkoutButtonText}>Proceed to Checkout</Text>
            <Ionicons name="arrow-forward" size={20} color="#FFFFFF" />
          </>
        )}
      </TouchableOpacity>

      {/* Info Text */}
      <Text style={styles.infoText}>
        Secure checkout • Free returns within 30 days
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginTop: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  promoContainer: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 16,
  },
  promoInputContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    paddingHorizontal: 12,
    height: 44,
    gap: 8,
  },
  promoInput: {
    flex: 1,
    fontSize: 14,
    color: '#111827',
  },
  applyPromoBtn: {
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 20,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  applyPromoBtnDisabled: {
    opacity: 0.6,
  },
  applyPromoText: {
    color: '#4F46E5',
    fontSize: 14,
    fontWeight: '600',
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
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
  shippingFree: {
    fontSize: 13,
    color: '#10B981',
    fontStyle: 'italic',
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
    marginBottom: 16,
  },
  totalLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  totalContainer: {
    alignItems: 'flex-end',
  },
  totalValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#4F46E5',
  },
  savingsBadge: {
    backgroundColor: '#ECFDF5',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
    marginTop: 4,
  },
  savingsText: {
    fontSize: 11,
    color: '#10B981',
    fontWeight: '600',
  },
  checkoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#4F46E5',
    height: 54,
    borderRadius: 12,
    gap: 8,
  },
  checkoutButtonDisabled: {
    opacity: 0.6,
  },
  checkoutButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  infoText: {
    fontSize: 12,
    color: '#9CA3AF',
    textAlign: 'center',
    marginTop: 12,
  },
});
