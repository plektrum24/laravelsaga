import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface QuantityStepperProps {
  quantity: number;
  maxQuantity?: number;
  minQuantity?: number;
  onQuantityChange?: (quantity: number) => void;
  showMaxWarning?: boolean;
}

export default function QuantityStepper({
  quantity,
  maxQuantity,
  minQuantity = 1,
  onQuantityChange,
  showMaxWarning = true,
}: QuantityStepperProps) {
  const handleDecrement = () => {
    if (quantity > minQuantity) {
      onQuantityChange?.(quantity - 1);
    }
  };

  const handleIncrement = () => {
    if (!maxQuantity || quantity < maxQuantity) {
      onQuantityChange?.(quantity + 1);
    }
  };

  const handleDirectInput = () => {
    // Could open a modal for direct input
    // For now, just use stepper
  };

  const isMaxReached = maxQuantity !== undefined && quantity >= maxQuantity;
  const isMinReached = quantity <= minQuantity;

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.label}>Quantity</Text>
        {maxQuantity !== undefined && maxQuantity > 0 && (
          <Text style={styles.maxLabel}>
            Max: {maxQuantity}
          </Text>
        )}
      </View>

      <View style={styles.stepperContainer}>
        {/* Decrease Button */}
        <TouchableOpacity
          style={[
            styles.stepperButton,
            isMinReached && styles.stepperButtonDisabled,
          ]}
          onPress={handleDecrement}
          disabled={isMinReached}
          activeOpacity={0.7}
        >
          <Ionicons
            name="remove"
            size={20}
            color={isMinReached ? '#9CA3AF' : '#111827'}
          />
        </TouchableOpacity>

        {/* Quantity Display/Input */}
        <TouchableOpacity
          style={styles.quantityDisplay}
          onPress={handleDirectInput}
          activeOpacity={0.7}
        >
          <Text style={styles.quantityValue}>{quantity}</Text>
        </TouchableOpacity>

        {/* Increase Button */}
        <TouchableOpacity
          style={[
            styles.stepperButton,
            isMaxReached && styles.stepperButtonDisabled,
          ]}
          onPress={handleIncrement}
          disabled={isMaxReached}
          activeOpacity={0.7}
        >
          <Ionicons
            name={isMaxReached ? 'lock-closed' : 'add'}
            size={20}
            color={isMaxReached ? '#9CA3AF' : '#111827'}
          />
        </TouchableOpacity>
      </View>

      {/* Max Quantity Warning */}
      {showMaxWarning && isMaxReached && maxQuantity !== undefined && (
        <View style={styles.warningContainer}>
          <Ionicons name="warning" size={16} color="#F59E0B" />
          <Text style={styles.warningText}>
            Maximum quantity reached ({maxQuantity} items)
          </Text>
        </View>
      )}

      {/* Stock Available Info */}
      {maxQuantity !== undefined && maxQuantity > 0 && (
        <Text style={styles.stockInfo}>
          {maxQuantity - quantity} items remaining in stock
        </Text>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginBottom: 16,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6B7280',
  },
  maxLabel: {
    fontSize: 12,
    color: '#9CA3AF',
  },
  stepperContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F3F4F6',
    borderRadius: 12,
    padding: 4,
    alignSelf: 'flex-start',
  },
  stepperButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  stepperButtonDisabled: {
    backgroundColor: '#E5E7EB',
    shadowOpacity: 0,
    elevation: 0,
  },
  quantityDisplay: {
    width: 64,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  quantityValue: {
    fontSize: 18,
    fontWeight: '700',
    color: '#111827',
  },
  warningContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEF3C7',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
    marginTop: 8,
    gap: 6,
  },
  warningText: {
    fontSize: 12,
    color: '#92400E',
    fontWeight: '500',
  },
  stockInfo: {
    fontSize: 11,
    color: '#10B981',
    marginTop: 6,
    fontStyle: 'italic',
  },
});
