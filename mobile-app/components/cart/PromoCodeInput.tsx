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

interface PromoCodeInputProps {
  onApplyPromo?: (code: string) => Promise<boolean>;
  appliedPromo?: {
    code: string;
    discount: number;
    description?: string;
  };
  onRemovePromo?: () => void;
}

export default function PromoCodeInput({
  onApplyPromo,
  appliedPromo,
  onRemovePromo,
}: PromoCodeInputProps) {
  const [promoCode, setPromoCode] = useState('');
  const [isApplying, setIsApplying] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleApply = async () => {
    if (!promoCode.trim() || !onApplyPromo) return;

    setError('');
    setSuccess('');
    setIsApplying(true);

    try {
      const success = await onApplyPromo(promoCode.trim());
      if (success) {
        setSuccess('Promo code applied successfully!');
        setPromoCode('');
      } else {
        setError('Invalid or expired promo code');
      }
    } catch (err) {
      setError('Failed to apply promo code');
    } finally {
      setIsApplying(false);
    }
  };

  const handleRemove = () => {
    setSuccess('');
    setError('');
    onRemovePromo?.();
  };

  if (appliedPromo) {
    return (
      <View style={styles.appliedContainer}>
        <View style={styles.appliedInfo}>
          <Ionicons name="pricetag" size={20} color="#10B981" />
          <View style={styles.appliedTextContainer}>
            <Text style={styles.appliedCode}>{appliedPromo.code}</Text>
            {appliedPromo.description && (
              <Text style={styles.appliedDescription}>
                {appliedPromo.description}
              </Text>
            )}
          </View>
        </View>
        <TouchableOpacity onPress={handleRemove}>
          <Ionicons name="close-circle" size={20} color="#EF4444" />
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.inputContainer}>
        <View style={styles.inputWrapper}>
          <Ionicons name="pricetag-outline" size={20} color="#9CA3AF" />
          <TextInput
            style={styles.input}
            placeholder="Enter promo code"
            placeholderTextColor="#9CA3AF"
            value={promoCode}
            onChangeText={setPromoCode}
            autoCapitalize="none"
            autoCorrect={false}
            editable={!isApplying}
          />
        </View>
        <TouchableOpacity
          style={[
            styles.applyButton,
            (!promoCode.trim() || isApplying) && styles.applyButtonDisabled,
          ]}
          onPress={handleApply}
          disabled={!promoCode.trim() || isApplying}
        >
          {isApplying ? (
            <ActivityIndicator size="small" color="#4F46E5" />
          ) : (
            <Text style={styles.applyButtonText}>Apply</Text>
          )}
        </TouchableOpacity>
      </View>

      {error ? (
        <View style={styles.errorContainer}>
          <Ionicons name="warning" size={16} color="#EF4444" />
          <Text style={styles.errorText}>{error}</Text>
        </View>
      ) : null}

      {success ? (
        <View style={styles.successContainer}>
          <Ionicons name="checkmark-circle" size={16} color="#10B981" />
          <Text style={styles.successText}>{success}</Text>
        </View>
      ) : null}

      {/* Promo Tips */}
      <View style={styles.tipsContainer}>
        <Text style={styles.tipsLabel}>Tips:</Text>
        <Text style={styles.tipsText}>• Check your email for exclusive codes</Text>
        <Text style={styles.tipsText}>• Follow us on social media</Text>
        <Text style={styles.tipsText}>• First purchase? Use WELCOME10</Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginBottom: 16,
  },
  inputContainer: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 8,
  },
  inputWrapper: {
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
  input: {
    flex: 1,
    fontSize: 14,
    color: '#111827',
  },
  applyButton: {
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 20,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    minWidth: 80,
  },
  applyButtonDisabled: {
    opacity: 0.6,
  },
  applyButtonText: {
    color: '#4F46E5',
    fontSize: 14,
    fontWeight: '600',
  },
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEF2F2',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
    gap: 6,
    marginBottom: 8,
  },
  errorText: {
    fontSize: 12,
    color: '#DC2626',
  },
  successContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ECFDF5',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
    gap: 6,
    marginBottom: 8,
  },
  successText: {
    fontSize: 12,
    color: '#059669',
  },
  appliedContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#ECFDF5',
    borderWidth: 1,
    borderColor: '#10B981',
    borderRadius: 12,
    padding: 12,
    marginBottom: 16,
  },
  appliedInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  appliedTextContainer: {
    gap: 2,
  },
  appliedCode: {
    fontSize: 14,
    fontWeight: '600',
    color: '#10B981',
  },
  appliedDescription: {
    fontSize: 12,
    color: '#6B7280',
  },
  tipsContainer: {
    backgroundColor: '#F9FAFB',
    borderRadius: 8,
    padding: 12,
    marginTop: 8,
  },
  tipsLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#6B7280',
    marginBottom: 6,
  },
  tipsText: {
    fontSize: 11,
    color: '#9CA3AF',
    marginBottom: 4,
  },
  tipsTextLast: {
    marginBottom: 0,
  },
});
