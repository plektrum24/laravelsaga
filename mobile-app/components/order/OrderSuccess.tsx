import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

interface OrderSuccessProps {
  orderNumber: string;
  orderDate: string;
  total: number;
  estimatedDelivery?: string;
  paymentMethod?: string;
  deliveryAddress?: string;
  onTrackOrder?: () => void;
  onContinueShopping?: () => void;
  onDownloadReceipt?: () => void;
}

export default function OrderSuccess({
  orderNumber,
  orderDate,
  total,
  estimatedDelivery,
  paymentMethod,
  deliveryAddress,
  onTrackOrder,
  onContinueShopping,
  onDownloadReceipt,
}: OrderSuccessProps) {
  const formatCurrency = (amount: number) => {
    return `Rp ${amount.toLocaleString('id-ID')}`;
  };

  const handleTrackOrder = () => {
    if (onTrackOrder) {
      onTrackOrder();
    } else {
      router.push(`/order/${orderNumber}` as any);
    }
  };

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Success Icon */}
      <View style={styles.successContainer}>
        <View style={styles.iconCircle}>
          <Ionicons name="checkmark" size={48} color="#FFFFFF" />
        </View>
        <Text style={styles.successTitle}>Order Confirmed!</Text>
        <Text style={styles.successSubtitle}>
          Your order has been placed successfully
        </Text>
      </View>

      {/* Order Number */}
      <View style={styles.orderNumberContainer}>
        <Text style={styles.orderNumberLabel}>Order Number</Text>
        <Text style={styles.orderNumber}>{orderNumber}</Text>
        <TouchableOpacity style={styles.copyButton}>
          <Ionicons name="copy-outline" size={16} color="#4F46E5" />
          <Text style={styles.copyText}>Copy</Text>
        </TouchableOpacity>
      </View>

      {/* Order Details */}
      <View style={styles.detailsContainer}>
        <View style={styles.detailRow}>
          <Ionicons name="calendar-outline" size={18} color="#6B7280" />
          <Text style={styles.detailLabel}>Order Date:</Text>
          <Text style={styles.detailValue}>{orderDate}</Text>
        </View>
        <View style={styles.detailRow}>
          <Ionicons name="wallet-outline" size={18} color="#6B7280" />
          <Text style={styles.detailLabel}>Total Paid:</Text>
          <Text style={styles.totalValue}>{formatCurrency(total)}</Text>
        </View>
        {paymentMethod && (
          <View style={styles.detailRow}>
            <Ionicons name="card-outline" size={18} color="#6B7280" />
            <Text style={styles.detailLabel}>Payment:</Text>
            <Text style={styles.detailValue}>{paymentMethod}</Text>
          </View>
        )}
      </View>

      {/* Delivery Information */}
      {estimatedDelivery && (
        <View style={styles.deliveryContainer}>
          <View style={styles.deliveryHeader}>
            <Ionicons name="time-outline" size={20} color="#10B981" />
            <Text style={styles.deliveryTitle}>Estimated Delivery</Text>
          </View>
          <Text style={styles.deliveryTime}>{estimatedDelivery}</Text>
          <Text style={styles.deliveryNote}>
            We'll notify you when your order is on the way
          </Text>
        </View>
      )}

      {/* Delivery Address */}
      {deliveryAddress && (
        <View style={styles.addressContainer}>
          <View style={styles.addressHeader}>
            <Ionicons name="location-outline" size={20} color="#4F46E5" />
            <Text style={styles.addressTitle}>Delivery Address</Text>
          </View>
          <Text style={styles.addressText}>{deliveryAddress}</Text>
        </View>
      )}

      {/* Action Buttons */}
      <View style={styles.buttonContainer}>
        <TouchableOpacity
          style={styles.trackButton}
          onPress={handleTrackOrder}
        >
          <Ionicons name="location" size={20} color="#FFFFFF" />
          <Text style={styles.trackButtonText}>Track Order</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.receiptButton}
          onPress={onDownloadReceipt}
        >
          <Ionicons name="download-outline" size={20} color="#4F46E5" />
          <Text style={styles.receiptButtonText}>Download Receipt</Text>
        </TouchableOpacity>
      </View>

      {/* Continue Shopping */}
      <TouchableOpacity
        style={styles.continueButton}
        onPress={onContinueShopping}
      >
        <Text style={styles.continueButtonText}>Continue Shopping</Text>
        <Ionicons name="arrow-forward" size={20} color="#6B7280" />
      </TouchableOpacity>

      {/* What's Next */}
      <View style={styles.nextStepsContainer}>
        <Text style={styles.nextStepsTitle}>What's Next?</Text>
        <View style={styles.stepContainer}>
          <View style={styles.stepNumber}>
            <Text style={styles.stepNumberText}>1</Text>
          </View>
          <Text style={styles.stepText}>
            We'll confirm your order via email and SMS
          </Text>
        </View>
        <View style={styles.stepContainer}>
          <View style={styles.stepNumber}>
            <Text style={styles.stepNumberText}>2</Text>
          </View>
          <Text style={styles.stepText}>
            Your order will be processed and packed
          </Text>
        </View>
        <View style={styles.stepContainer}>
          <View style={styles.stepNumber}>
            <Text style={styles.stepNumberText}>3</Text>
          </View>
          <Text style={styles.stepText}>
            Track your delivery in real-time
          </Text>
        </View>
        <View style={styles.stepContainer}>
          <View style={styles.stepNumber}>
            <Text style={styles.stepNumberText}>4</Text>
          </View>
          <Text style={styles.stepText}>
            Receive your order at your doorstep
          </Text>
        </View>
      </View>

      {/* Support */}
      <View style={styles.supportContainer}>
        <Ionicons name="headset" size={20} color="#6B7280" />
        <Text style={styles.supportText}>Need help?</Text>
        <TouchableOpacity>
          <Text style={styles.supportLink}>Contact Support</Text>
        </TouchableOpacity>
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
  successContainer: {
    backgroundColor: '#FFFFFF',
    alignItems: 'center',
    padding: 32,
  },
  iconCircle: {
    width: 96,
    height: 96,
    borderRadius: 48,
    backgroundColor: '#10B981',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  successTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#111827',
    marginBottom: 8,
  },
  successSubtitle: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
  },
  orderNumberContainer: {
    backgroundColor: '#EEF2FF',
    margin: 16,
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
  },
  orderNumberLabel: {
    fontSize: 12,
    color: '#6B7280',
    marginBottom: 4,
  },
  orderNumber: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#4F46E5',
    marginBottom: 8,
  },
  copyButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  copyText: {
    fontSize: 12,
    color: '#4F46E5',
    fontWeight: '500',
  },
  detailsContainer: {
    backgroundColor: '#FFFFFF',
    padding: 16,
    marginBottom: 8,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  detailLabel: {
    fontSize: 13,
    color: '#6B7280',
  },
  detailValue: {
    fontSize: 13,
    color: '#111827',
    fontWeight: '500',
  },
  totalValue: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#4F46E5',
  },
  deliveryContainer: {
    backgroundColor: '#ECFDF5',
    marginHorizontal: 16,
    marginBottom: 8,
    padding: 16,
    borderRadius: 12,
  },
  deliveryHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  deliveryTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  deliveryTime: {
    fontSize: 16,
    fontWeight: '600',
    color: '#10B981',
    marginBottom: 4,
  },
  deliveryNote: {
    fontSize: 12,
    color: '#6B7280',
  },
  addressContainer: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 8,
    padding: 16,
    borderRadius: 12,
  },
  addressHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  addressTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  addressText: {
    fontSize: 13,
    color: '#6B7280',
    lineHeight: 20,
  },
  buttonContainer: {
    flexDirection: 'row',
    gap: 12,
    margin: 16,
  },
  trackButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#4F46E5',
    paddingVertical: 14,
    borderRadius: 12,
    gap: 8,
  },
  trackButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  receiptButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EEF2FF',
    paddingVertical: 14,
    borderRadius: 12,
    gap: 8,
  },
  receiptButtonText: {
    color: '#4F46E5',
    fontSize: 16,
    fontWeight: '600',
  },
  continueButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginHorizontal: 16,
    marginBottom: 16,
    gap: 8,
  },
  continueButtonText: {
    fontSize: 14,
    color: '#6B7280',
    fontWeight: '500',
  },
  nextStepsContainer: {
    backgroundColor: '#FFFFFF',
    margin: 16,
    padding: 16,
    borderRadius: 12,
  },
  nextStepsTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 16,
  },
  stepContainer: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    marginBottom: 12,
  },
  stepNumber: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
  },
  stepNumberText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#4F46E5',
  },
  stepText: {
    flex: 1,
    fontSize: 13,
    color: '#6B7280',
    lineHeight: 18,
  },
  supportContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    marginHorizontal: 16,
  },
  supportText: {
    fontSize: 13,
    color: '#6B7280',
  },
  supportLink: {
    fontSize: 13,
    color: '#4F46E5',
    fontWeight: '500',
  },
});
