import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

interface EmptyCartProps {
  onShopNowPress?: () => void;
}

export default function EmptyCart({ onShopNowPress }: EmptyCartProps) {
  const handleShopNow = () => {
    if (onShopNowPress) {
      onShopNowPress();
    } else {
      router.push('/shop' as any);
    }
  };

  return (
    <View style={styles.container}>
      {/* Cart Icon */}
      <View style={styles.iconContainer}>
        <Ionicons name="cart-outline" size={80} color="#D1D5DB" />
      </View>

      {/* Title */}
      <Text style={styles.title}>Your cart is empty</Text>

      {/* Subtitle */}
      <Text style={styles.subtitle}>
        Looks like you haven't added anything to your cart yet.
      </Text>

      {/* Suggestions */}
      <View style={styles.suggestionsContainer}>
        <View style={styles.suggestionItem}>
          <Ionicons name="sparkles" size={20} color="#F59E0B" />
          <Text style={styles.suggestionText}>Browse our latest products</Text>
        </View>
        <View style={styles.suggestionItem}>
          <Ionicons name="pricetag" size={20} color="#10B981" />
          <Text style={styles.suggestionText}>Check out special offers</Text>
        </View>
        <View style={styles.suggestionItem}>
          <Ionicons name="star" size={20} color="#3B82F6" />
          <Text style={styles.suggestionText}>View best sellers</Text>
        </View>
      </View>

      {/* Shop Now Button */}
      <TouchableOpacity
        style={styles.shopButton}
        onPress={handleShopNow}
        activeOpacity={0.8}
      >
        <Ionicons name="bag" size={20} color="#FFFFFF" />
        <Text style={styles.shopButtonText}>Start Shopping</Text>
      </TouchableOpacity>

      {/* Continue Browsing */}
      <TouchableOpacity
        style={styles.continueButton}
        onPress={() => router.push('/shop' as any)}
      >
        <Text style={styles.continueButtonText}>
          Or browse categories
        </Text>
        <Ionicons name="arrow-forward" size={16} color="#6B7280" />
      </TouchableOpacity>

      {/* Benefits */}
      <View style={styles.benefitsContainer}>
        <View style={styles.benefitItem}>
          <Ionicons name="checkmark-circle" size={16} color="#10B981" />
          <Text style={styles.benefitText}>Free shipping on orders over Rp 500K</Text>
        </View>
        <View style={styles.benefitItem}>
          <Ionicons name="checkmark-circle" size={16} color="#10B981" />
          <Text style={styles.benefitText}>Easy returns within 30 days</Text>
        </View>
        <View style={styles.benefitItem}>
          <Ionicons name="checkmark-circle" size={16} color="#10B981" />
          <Text style={styles.benefitText}>Secure payment methods</Text>
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
    backgroundColor: '#F9FAFB',
  },
  iconContainer: {
    marginBottom: 24,
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#111827',
    marginBottom: 8,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: 24,
    lineHeight: 20,
  },
  suggestionsContainer: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 24,
    width: '100%',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  suggestionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 12,
  },
  suggestionItemLast: {
    marginBottom: 0,
  },
  suggestionText: {
    fontSize: 14,
    color: '#374151',
    fontWeight: '500',
  },
  shopButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#4F46E5',
    paddingHorizontal: 32,
    paddingVertical: 16,
    borderRadius: 12,
    gap: 8,
    marginBottom: 16,
    shadowColor: '#4F46E5',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  shopButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  continueButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 32,
  },
  continueButtonText: {
    fontSize: 14,
    color: '#6B7280',
    fontWeight: '500',
  },
  benefitsContainer: {
    width: '100%',
    paddingHorizontal: 16,
  },
  benefitItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  benefitText: {
    fontSize: 13,
    color: '#6B7280',
  },
});
