import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export type DeliveryType = 'pickup' | 'delivery';

export interface DeliveryMethod {
  id: string;
  type: DeliveryType;
  name: string;
  description: string;
  fee: number;
  estimated_time: string;
  icon: string;
}

interface DeliveryMethodSelectorProps {
  methods?: DeliveryMethod[];
  selectedMethodId?: string;
  onSelectMethod?: (method: DeliveryMethod) => void;
}

export default function DeliveryMethodSelector({
  methods = [],
  selectedMethodId,
  onSelectMethod,
}: DeliveryMethodSelectorProps) {
  const handleSelect = (method: DeliveryMethod) => {
    onSelectMethod?.(method);
  };

  const defaultMethods: DeliveryMethod[] = [
    {
      id: 'pickup',
      type: 'pickup',
      name: 'Store Pickup',
      description: 'Pick up from our store',
      fee: 0,
      estimated_time: '1-2 hours',
      icon: 'storefront',
    },
    {
      id: 'delivery',
      type: 'delivery',
      name: 'Home Delivery',
      description: 'Delivered to your address',
      fee: 15000,
      estimated_time: 'Same day',
      icon: 'bicycle',
    },
    {
      id: 'express',
      type: 'delivery',
      name: 'Express Delivery',
      description: 'Fast delivery within 2 hours',
      fee: 30000,
      estimated_time: 'Within 2 hours',
      icon: 'rocket',
    },
  ];

  const displayMethods = methods.length > 0 ? methods : defaultMethods;

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Delivery Method</Text>

      <ScrollView showsVerticalScrollIndicator={false}>
        {displayMethods.map((method) => {
          const isSelected = selectedMethodId === method.id;

          return (
            <TouchableOpacity
              key={method.id}
              style={[
                styles.methodCard,
                isSelected && styles.methodCardSelected,
              ]}
              onPress={() => handleSelect(method)}
              activeOpacity={0.7}
            >
              <View style={styles.methodIconContainer}>
                <Ionicons
                  name={method.icon as any}
                  size={28}
                  color={isSelected ? '#4F46E5' : '#6B7280'}
                />
              </View>

              <View style={styles.methodDetails}>
                <View style={styles.methodHeader}>
                  <Text style={styles.methodName}>{method.name}</Text>
                  {method.fee === 0 ? (
                    <View style={styles.freeBadge}>
                      <Text style={styles.freeText}>FREE</Text>
                    </View>
                  ) : (
                    <Text style={styles.methodFee}>
                      Rp {method.fee.toLocaleString('id-ID')}
                    </Text>
                  )}
                </View>

                <Text style={styles.methodDescription}>{method.description}</Text>

                <View style={styles.methodFooter}>
                  <View style={styles.estimatedTime}>
                    <Ionicons name="time-outline" size={14} color="#9CA3AF" />
                    <Text style={styles.estimatedTimeText}>
                      {method.estimated_time}
                    </Text>
                  </View>
                </View>
              </View>

              <View style={styles.radioContainer}>
                <View
                  style={[
                    styles.radio,
                    isSelected && styles.radioSelected,
                  ]}
                >
                  {isSelected && <View style={styles.radioInner} />}
                </View>
              </View>
            </TouchableOpacity>
          );
        })}
      </ScrollView>
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
  methodFee: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4F46E5',
  },
  freeBadge: {
    backgroundColor: '#10B981',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
  },
  freeText: {
    fontSize: 11,
    color: '#FFFFFF',
    fontWeight: '700',
  },
  methodDescription: {
    fontSize: 12,
    color: '#6B7280',
    marginBottom: 6,
  },
  methodFooter: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  estimatedTime: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  estimatedTimeText: {
    fontSize: 11,
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
  radioInner: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#4F46E5',
  },
});
