import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export type SortOption = 
  | 'default'
  | 'newest'
  | 'price_asc'
  | 'price_desc'
  | 'name_asc'
  | 'name_desc'
  | 'best_seller';

interface SortOptionItem {
  value: SortOption;
  label: string;
  icon: string;
}

const SORT_OPTIONS: SortOptionItem[] = [
  { value: 'default', label: 'Recommended', icon: 'grid' },
  { value: 'newest', label: 'Newest', icon: 'time' },
  { value: 'price_asc', label: 'Price: Low to High', icon: 'arrow-up' },
  { value: 'price_desc', label: 'Price: High to Low', icon: 'arrow-down' },
  { value: 'name_asc', label: 'Name: A to Z', icon: 'text' },
  { value: 'name_desc', label: 'Name: Z to A', icon: 'text' },
  { value: 'best_seller', label: 'Best Selling', icon: 'star' },
];

interface SortModalProps {
  visible: boolean;
  currentSort: SortOption;
  onSelect: (sort: SortOption) => void;
  onClose: () => void;
}

export default function SortModal({
  visible,
  currentSort,
  onSelect,
  onClose,
}: SortModalProps) {
  const handleSelect = (option: SortOption) => {
    onSelect(option);
    onClose();
  };

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent={true}
      onRequestClose={onClose}
    >
      <TouchableOpacity 
        style={styles.overlay} 
        activeOpacity={1} 
        onPress={onClose}
      >
        <View style={styles.container}>
          {/* Header */}
          <View style={styles.header}>
            <Text style={styles.headerTitle}>Sort By</Text>
            <TouchableOpacity onPress={onClose}>
              <Ionicons name="close" size={24} color="#111827" />
            </TouchableOpacity>
          </View>

          {/* Sort Options */}
          <View style={styles.content}>
            {SORT_OPTIONS.map((option) => (
              <TouchableOpacity
                key={option.value}
                style={styles.option}
                onPress={() => handleSelect(option.value)}
                activeOpacity={0.7}
              >
                <View style={styles.optionLeft}>
                  <Ionicons 
                    name={option.icon as any} 
                    size={20} 
                    color={currentSort === option.value ? '#4F46E5' : '#6B7280'} 
                  />
                  <Text
                    style={[
                      styles.optionText,
                      currentSort === option.value && styles.optionTextActive,
                    ]}
                  >
                    {option.label}
                  </Text>
                </View>
                {currentSort === option.value && (
                  <Ionicons name="checkmark-circle" size={24} color="#4F46E5" />
                )}
              </TouchableOpacity>
            ))}
          </View>
        </View>
      </TouchableOpacity>
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
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
  },
  content: {
    paddingVertical: 8,
  },
  option: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  optionLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  optionText: {
    fontSize: 16,
    color: '#111827',
    fontWeight: '500',
  },
  optionTextActive: {
    color: '#4F46E5',
    fontWeight: '600',
  },
});
