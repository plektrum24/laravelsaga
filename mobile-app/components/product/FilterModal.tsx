import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  ScrollView,
  Switch,
  TextInput,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export interface FilterOptions {
  category_id?: string;
  min_price?: string;
  max_price?: string;
  in_stock_only?: boolean;
  featured_only?: boolean;
  on_sale?: boolean;
}

interface FilterModalProps {
  visible: boolean;
  filters: FilterOptions;
  categories?: Array<{ id: number; name: string }>;
  onApply: (filters: FilterOptions) => void;
  onClose: () => void;
  onReset: () => void;
}

export default function FilterModal({
  visible,
  filters,
  categories = [],
  onApply,
  onClose,
  onReset,
}: FilterModalProps) {
  const [localFilters, setLocalFilters] = useState<FilterOptions>({ ...filters });

  const updateFilter = (key: keyof FilterOptions, value: any) => {
    setLocalFilters(prev => ({ ...prev, [key]: value }));
  };

  const handleApply = () => {
    onApply(localFilters);
    onClose();
  };

  const handleReset = () => {
    onReset();
    setLocalFilters({});
    onClose();
  };

  const hasActiveFilters = 
    localFilters.category_id ||
    localFilters.min_price ||
    localFilters.max_price ||
    localFilters.in_stock_only ||
    localFilters.featured_only ||
    localFilters.on_sale;

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent={true}
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <View style={styles.container}>
          {/* Header */}
          <View style={styles.header}>
            <Text style={styles.headerTitle}>Filters</Text>
            <TouchableOpacity onPress={onClose}>
              <Ionicons name="close" size={24} color="#111827" />
            </TouchableOpacity>
          </View>

          <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
            {/* Category Filter */}
            {categories.length > 0 && (
              <View style={styles.section}>
                <Text style={styles.sectionTitle}>Category</Text>
                <ScrollView horizontal showsHorizontalScrollIndicator={false}>
                  <TouchableOpacity
                    style={[
                      styles.categoryPill,
                      !localFilters.category_id && styles.categoryPillActive,
                    ]}
                    onPress={() => updateFilter('category_id', undefined)}
                  >
                    <Text
                      style={[
                        styles.categoryPillText,
                        !localFilters.category_id && styles.categoryPillTextActive,
                      ]}
                    >
                      All
                    </Text>
                  </TouchableOpacity>
                  {categories.map((category) => (
                    <TouchableOpacity
                      key={category.id}
                      style={[
                        styles.categoryPill,
                        localFilters.category_id === String(category.id) && styles.categoryPillActive,
                      ]}
                      onPress={() =>
                        updateFilter(
                          'category_id',
                          localFilters.category_id === String(category.id) ? undefined : String(category.id)
                        )
                      }
                    >
                      <Text
                        style={[
                          styles.categoryPillText,
                          localFilters.category_id === String(category.id) && styles.categoryPillTextActive,
                        ]}
                      >
                        {category.name}
                      </Text>
                    </TouchableOpacity>
                  ))}
                </ScrollView>
              </View>
            )}

            {/* Price Range */}
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Price Range</Text>
              <View style={styles.priceRangeContainer}>
                <View style={styles.priceInputContainer}>
                  <Text style={styles.priceLabel}>Min</Text>
                  <TextInput
                    style={styles.priceInput}
                    placeholder="0"
                    placeholderTextColor="#9CA3AF"
                    keyboardType="numeric"
                    value={localFilters.min_price}
                    onChangeText={(value) => updateFilter('min_price', value)}
                  />
                  <Text style={styles.priceSymbol}>Rp</Text>
                </View>
                <Text style={styles.priceSeparator}>-</Text>
                <View style={styles.priceInputContainer}>
                  <Text style={styles.priceLabel}>Max</Text>
                  <TextInput
                    style={styles.priceInput}
                    placeholder="Any"
                    placeholderTextColor="#9CA3AF"
                    keyboardType="numeric"
                    value={localFilters.max_price}
                    onChangeText={(value) => updateFilter('max_price', value)}
                  />
                  <Text style={styles.priceSymbol}>Rp</Text>
                </View>
              </View>
            </View>

            {/* Availability & Special */}
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Availability & Special</Text>
              
              <View style={styles.toggleRow}>
                <View style={styles.toggleInfo}>
                  <Text style={styles.toggleLabel}>In Stock Only</Text>
                  <Text style={styles.toggleDescription}>Show only available products</Text>
                </View>
                <Switch
                  value={localFilters.in_stock_only || false}
                  onValueChange={(value) => updateFilter('in_stock_only', value)}
                  trackColor={{ false: '#D1D5DB', true: '#A78BFA' }}
                  thumbColor={localFilters.in_stock_only ? '#4F46E5' : '#F4F3F4'}
                />
              </View>

              <View style={styles.toggleRow}>
                <View style={styles.toggleInfo}>
                  <Text style={styles.toggleLabel}>Featured Only</Text>
                  <Text style={styles.toggleDescription}>Show featured products</Text>
                </View>
                <Switch
                  value={localFilters.featured_only || false}
                  onValueChange={(value) => updateFilter('featured_only', value)}
                  trackColor={{ false: '#D1D5DB', true: '#A78BFA' }}
                  thumbColor={localFilters.featured_only ? '#4F46E5' : '#F4F3F4'}
                />
              </View>

              <View style={styles.toggleRow}>
                <View style={styles.toggleInfo}>
                  <Text style={styles.toggleLabel}>On Sale</Text>
                  <Text style={styles.toggleDescription}>Show products with discounts</Text>
                </View>
                <Switch
                  value={localFilters.on_sale || false}
                  onValueChange={(value) => updateFilter('on_sale', value)}
                  trackColor={{ false: '#D1D5DB', true: '#A78BFA' }}
                  thumbColor={localFilters.on_sale ? '#4F46E5' : '#F4F3F4'}
                />
              </View>
            </View>
          </ScrollView>

          {/* Footer */}
          <View style={styles.footer}>
            <TouchableOpacity style={styles.resetButton} onPress={handleReset}>
              <Text style={styles.resetButtonText}>Reset All</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.applyButton, !hasActiveFilters && styles.applyButtonDisabled]}
              onPress={handleApply}
              disabled={!hasActiveFilters}
            >
              <Text style={[styles.applyButtonText, !hasActiveFilters && styles.applyButtonTextDisabled]}>
                Apply Filters
                {hasActiveFilters && (
                  <Text style={styles.filterCount}>
                    ({Object.keys(localFilters).length})
                  </Text>
                )}
              </Text>
            </TouchableOpacity>
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
    justifyContent: 'flex-end',
  },
  container: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '85%',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#111827',
  },
  content: {
    padding: 20,
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 12,
  },
  categoryPill: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#F3F4F6',
    marginRight: 8,
  },
  categoryPillActive: {
    backgroundColor: '#4F46E5',
  },
  categoryPillText: {
    fontSize: 14,
    color: '#6B7280',
    fontWeight: '500',
  },
  categoryPillTextActive: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
  priceRangeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  priceInputContainer: {
    flex: 1,
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    padding: 12,
  },
  priceLabel: {
    fontSize: 12,
    color: '#6B7280',
    marginBottom: 4,
  },
  priceInput: {
    fontSize: 16,
    color: '#111827',
    fontWeight: '600',
  },
  priceSymbol: {
    position: 'absolute',
    right: 12,
    bottom: 12,
    fontSize: 14,
    color: '#9CA3AF',
  },
  priceSeparator: {
    fontSize: 18,
    color: '#9CA3AF',
    fontWeight: '600',
  },
  toggleRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  toggleInfo: {
    flex: 1,
    paddingRight: 16,
  },
  toggleLabel: {
    fontSize: 15,
    color: '#111827',
    fontWeight: '500',
    marginBottom: 2,
  },
  toggleDescription: {
    fontSize: 13,
    color: '#9CA3AF',
  },
  footer: {
    flexDirection: 'row',
    padding: 20,
    gap: 12,
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
  },
  resetButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 12,
    backgroundColor: '#F3F4F6',
    alignItems: 'center',
  },
  resetButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#6B7280',
  },
  applyButton: {
    flex: 2,
    paddingVertical: 14,
    borderRadius: 12,
    backgroundColor: '#4F46E5',
    alignItems: 'center',
  },
  applyButtonDisabled: {
    backgroundColor: '#D1D5DB',
  },
  applyButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  applyButtonTextDisabled: {
    color: '#9CA3AF',
  },
  filterCount: {
    fontWeight: 'normal',
    marginLeft: 4,
  },
});
