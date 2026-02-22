import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

type PaymentStatus = 'processing' | 'success' | 'pending' | 'failed';

interface PaymentStatusModalProps {
  visible: boolean;
  status: PaymentStatus;
  amount?: number;
  transactionId?: string;
  message?: string;
  onRetry?: () => void;
  onClose?: () => void;
  onContinue?: () => void;
}

export default function PaymentStatusModal({
  visible,
  status,
  amount,
  transactionId,
  message,
  onRetry,
  onClose,
  onContinue,
}: PaymentStatusModalProps) {
  const getStatusConfig = () => {
    switch (status) {
      case 'processing':
        return {
          icon: 'hourglass-outline',
          iconColor: '#F59E0B',
          title: 'Processing Payment',
          subtitle: 'Please wait while we process your payment',
          button: undefined,
        };
      case 'success':
        return {
          icon: 'checkmark-circle',
          iconColor: '#10B981',
          title: 'Payment Successful!',
          subtitle: `Transaction ID: ${transactionId || 'N/A'}`,
          button: 'Continue',
        };
      case 'pending':
        return {
          icon: 'time-outline',
          iconColor: '#F59E0B',
          title: 'Payment Pending',
          subtitle: 'Please complete the payment to confirm your order',
          button: 'Check Status',
        };
      case 'failed':
        return {
          icon: 'close-circle',
          iconColor: '#EF4444',
          title: 'Payment Failed',
          subtitle: message || 'Something went wrong. Please try again.',
          button: 'Retry',
        };
    }
  };

  const config = getStatusConfig();

  return (
    <Modal
      visible={visible}
      transparent={true}
      animationType="fade"
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <View style={styles.container}>
          {/* Icon */}
          <View style={styles.iconContainer}>
            {status === 'processing' ? (
              <ActivityIndicator size="large" color={config.iconColor} />
            ) : (
              <Ionicons
                name={config.icon as any}
                size={64}
                color={config.iconColor}
              />
            )}
          </View>

          {/* Title */}
          <Text style={styles.title}>{config.title}</Text>

          {/* Subtitle */}
          <Text style={styles.subtitle}>{config.subtitle}</Text>

          {/* Amount */}
          {amount !== undefined && (
            <View style={styles.amountContainer}>
              <Text style={styles.amountLabel}>Amount Paid</Text>
              <Text style={styles.amount}>Rp {amount.toLocaleString('id-ID')}</Text>
            </View>
          )}

          {/* Message */}
          {message && status === 'failed' && (
            <View style={styles.messageContainer}>
              <Ionicons name="warning" size={16} color="#EF4444" />
              <Text style={styles.message}>{message}</Text>
            </View>
          )}

          {/* Buttons */}
          <View style={styles.buttonContainer}>
            {status === 'failed' && onRetry && (
              <TouchableOpacity
                style={styles.retryButton}
                onPress={onRetry}
              >
                <Ionicons name="refresh" size={20} color="#FFFFFF" />
                <Text style={styles.retryButtonText}>Retry Payment</Text>
              </TouchableOpacity>
            )}

            {config.button && (
              <TouchableOpacity
                style={styles.continueButton}
                onPress={status === 'failed' ? onRetry : onContinue}
              >
                <Text style={styles.continueButtonText}>
                  {config.button}
                </Text>
                <Ionicons name="arrow-forward" size={20} color="#FFFFFF" />
              </TouchableOpacity>
            )}

            {status !== 'processing' && (
              <TouchableOpacity
                style={styles.closeButton}
                onPress={onClose}
              >
                <Text style={styles.closeButtonText}>Close</Text>
              </TouchableOpacity>
            )}
          </View>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  container: {
    backgroundColor: '#FFFFFF',
    borderRadius: 20,
    padding: 24,
    width: '100%',
    maxWidth: 400,
    alignItems: 'center',
  },
  iconContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: '#F9FAFB',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#111827',
    textAlign: 'center',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: 20,
    lineHeight: 20,
  },
  amountContainer: {
    backgroundColor: '#F9FAFB',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 12,
    marginBottom: 16,
    alignItems: 'center',
  },
  amountLabel: {
    fontSize: 12,
    color: '#9CA3AF',
    marginBottom: 4,
  },
  amount: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#4F46E5',
  },
  messageContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEF2F2',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
    marginBottom: 16,
    gap: 6,
  },
  message: {
    fontSize: 12,
    color: '#DC2626',
    flex: 1,
  },
  buttonContainer: {
    width: '100%',
    gap: 12,
  },
  retryButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EF4444',
    paddingVertical: 14,
    borderRadius: 12,
    gap: 8,
  },
  retryButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  continueButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#4F46E5',
    paddingVertical: 14,
    borderRadius: 12,
    gap: 8,
  },
  continueButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  closeButton: {
    paddingVertical: 12,
    alignItems: 'center',
  },
  closeButtonText: {
    fontSize: 14,
    color: '#6B7280',
    fontWeight: '500',
  },
});
