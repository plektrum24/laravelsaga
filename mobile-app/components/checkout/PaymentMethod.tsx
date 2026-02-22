import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export type PaymentType = 'card' | 'transfer' | 'ewallet' | 'cod';

export interface PaymentMethod {
  id: string;
  type: PaymentType;
  name: string;
  description: string;
  icon: string;
  fee?: number;
  disabled?: boolean;
}

interface PaymentMethodSelectorProps {
  methods?: PaymentMethod[];
  selectedMethodId?: string;
  onSelectMethod?: (method: PaymentMethod) => void;
}

export default function PaymentMethodSelector({
  methods = [],
  selectedMethodId,
  onSelectMethod,
}: PaymentMethodSelectorProps) {
  const handleSelect = (method: PaymentMethod) => {
    if (method.disabled) return;
    onSelectMethod?.(method);
  };

  const defaultMethods: PaymentMethod[] = [
    {
      id: 'gopay',
      type: 'ewallet',
      name: 'GoPay',
      description: 'Pay with GoPay balance',
      icon: 'wallet',
      fee: 0,
    },
    {
      id: 'shopeepay',
      type: 'ewallet',
      name: 'ShopeePay',
      description: 'Pay with ShopeePay',
      icon: 'shopping-bag',
      fee: 0,
    },
    {
      id: 'card',
      type: 'card',
      name: 'Credit/Debit Card',
      description: 'Visa, Mastercard, JCB',
      icon: 'card',
      fee: 0,
    },
    {
      id: 'transfer',
      type: 'transfer',
      name: 'Bank Transfer',
      description: 'BCA, Mandiri, BNI, BRI',
      icon: 'buildings',
      fee: 0,
    },
    {
      id: 'cod',
      type: 'cod',
      name: 'Cash on Delivery',
      description: 'Pay when you receive',
      icon: 'cash',
      fee: 5000,
    },
  ];

  const displayMethods = methods.length > 0 ? methods : defaultMethods;

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Payment Method</Text>

      <ScrollView showsVerticalScrollIndicator={false}>
        {displayMethods.map((method) => {
          const isSelected = selectedMethodId === method.id;

          return (
            <TouchableOpacity
              key={method.id}
              style={[
                styles.methodCard,
                isSelected && styles.methodCardSelected,
                method.disabled && styles.methodCardDisabled,
              ]}
              onPress={() => handleSelect(method)}
              disabled={method.disabled}
              activeOpacity={0.7}
            >
              <View style={styles.methodIconContainer}>
                <Ionicons
                  name={method.icon as any}
                  size={28}
                  color={
                    method.disabled
                      ? '#9CA3AF'
                      : isSelected
                      ? '#4F46E5'
                      : '#6B7280'
                  }
                />
              </View>

              <View style={styles.methodDetails}>
                <View style={styles.methodHeader}>
                  <Text
                    style={[
                      styles.methodName,
                      method.disabled && styles.methodNameDisabled,
                    ]}
                  >
                    {method.name}
                  </Text>
                  {method.fee && method.fee > 0 ? (
                    <Text style={styles.methodFee}>
                      + Rp {method.fee.toLocaleString('id-ID')}
                    </Text>
                  ) : method.disabled ? (
                    <Text style={styles.unavailableText}>Unavailable</Text>
                  ) : (
                    <Text style={styles.noFeeText}>No fee</Text>
                  )}
                </View>

                <Text
                  style={[
                    styles.methodDescription,
                    method.disabled && styles.methodDescriptionDisabled,
                  ]}
                >
                  {method.description}
                </Text>
              </View>

              <View style={styles.radioContainer}>
                <View
                  style={[
                    styles.radio,
                    isSelected && styles.radioSelected,
                    method.disabled && styles.radioDisabled,
                  ]}
                >
                  {isSelected && <View style={styles.radioInner} />}
                </View>
              </View>
            </TouchableOpacity>
          );
        })}
      </ScrollView>

      {/* Security Notice */}
      <View style={styles.securityNotice}>
        <Ionicons name="shield-checkmark" size={16} color="#10B981" />
        <Text style={styles.securityText}>
          All payments are secure and encrypted
        </Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 12,
  },
  methodCard: {
    flexDirection: 'row',
    backgroundColor: '#F9FAFB',
    borderRadius: 12,
    padding: 12,
    marginBottom: 8,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    alignItems: 'center',
  },
  methodCardSelected: {
    backgroundColor: '#EEF2FF',
    borderColor: '#4F46E5',
    borderWidth: 2,
  },
  methodCardDisabled: {
    opacity: 0.6,
    backgroundColor: '#F3F4F6',
  },
  methodIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#FFFFFF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  methodDetails: {
    flex: 1,
  },
  methodHeader: {
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
  methodNameDisabled: {
    color: '#9CA3AF',
  },
  methodFee: {
    fontSize: 13,
    fontWeight: '600',
    color: '#EF4444',
  },
  unavailableText: {
    fontSize: 12,
    color: '#9CA3AF',
    fontStyle: 'italic',
  },
  noFeeText: {
    fontSize: 12,
    color: '#10B981',
  },
  methodDescription: {
    fontSize: 12,
    color: '#6B7280',
  },
  methodDescriptionDisabled: {
    color: '#9CA3AF',
  },
  radioContainer: {
    marginLeft: 8,
  },
  radio: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 2,
    borderColor: '#D1D5DB',
    justifyContent: 'center',
    alignItems: 'center',
  },
  radioSelected: {
    borderColor: '#4F46E5',
  },
  radioDisabled: {
    borderColor: '#9CA3AF',
  },
  radioInner: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#4F46E5',
  },
  securityNotice: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#ECFDF5',
    borderRadius: 8,
    padding: 12,
    marginTop: 8,
    gap: 6,
  },
  securityText: {
    fontSize: 12,
    color: '#059669',
    fontWeight: '500',
  },
});
