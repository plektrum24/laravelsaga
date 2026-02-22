import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export interface ProductUnit {
  id: number;
  name: string;
  abbreviation?: string;
  price?: number;
  conversion_factor?: number;
}

interface UnitSelectorProps {
  units?: ProductUnit[];
  selectedUnitId?: number;
  onUnitSelect?: (unit: ProductUnit) => void;
}

export default function UnitSelector({
  units = [],
  selectedUnitId,
  onUnitSelect,
}: UnitSelectorProps) {
  const handleSelect = (unit: ProductUnit) => {
    onUnitSelect?.(unit);
  };

  if (!units || units.length <= 1) {
    return null; // Don't show if only one unit or no units
  }

  return (
    <View style={styles.container}>
      <Text style={styles.label}>Select Unit</Text>
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.unitsContainer}
      >
        {units.map((unit) => {
          const isSelected = selectedUnitId === unit.id;
          
          return (
            <TouchableOpacity
              key={unit.id}
              style={[
                styles.unitCard,
                isSelected && styles.unitCardSelected,
              ]}
              onPress={() => handleSelect(unit)}
              activeOpacity={0.7}
            >
              <View style={styles.unitContent}>
                <Text
                  style={[
                    styles.unitName,
                    isSelected && styles.unitNameSelected,
                  ]}
                  numberOfLines={1}
                >
                  {unit.name}
                </Text>
                
                {unit.abbreviation && (
                  <Text style={styles.unitAbbreviation}>
                    {unit.abbreviation}
                  </Text>
                )}
                
                {unit.price !== undefined && (
                  <View style={styles.unitPriceContainer}>
                    <Text style={styles.unitPriceLabel}>Price:</Text>
                    <Text
                      style={[
                        styles.unitPrice,
                        isSelected && styles.unitPriceSelected,
                      ]}
                    >
                      Rp {unit.price.toLocaleString('id-ID')}
                    </Text>
                  </View>
                )}
                
                {unit.conversion_factor && unit.conversion_factor !== 1 && (
                  <Text style={styles.unitConversion}>
                    = {unit.conversion_factor} base unit{unit.conversion_factor > 1 ? 's' : ''}
                  </Text>
                )}
                
                {isSelected && (
                  <View style={styles.selectedIndicator}>
                    <Ionicons name="checkmark-circle" size={16} color="#4F46E5" />
                  </View>
                )}
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
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6B7280',
    marginBottom: 8,
  },
  unitsContainer: {
    flexDirection: 'row',
    gap: 8,
  },
  unitCard: {
    minWidth: 100,
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    padding: 12,
    position: 'relative',
  },
  unitCardSelected: {
    backgroundColor: '#EEF2FF',
    borderColor: '#4F46E5',
    borderWidth: 2,
  },
  unitContent: {
    flex: 1,
  },
  unitName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 2,
  },
  unitNameSelected: {
    color: '#4F46E5',
    fontWeight: '600',
  },
  unitAbbreviation: {
    fontSize: 11,
    color: '#9CA3AF',
    marginBottom: 4,
  },
  unitPriceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 4,
  },
  unitPriceLabel: {
    fontSize: 10,
    color: '#9CA3AF',
  },
  unitPrice: {
    fontSize: 13,
    fontWeight: '600',
    color: '#111827',
  },
  unitPriceSelected: {
    color: '#4F46E5',
    fontWeight: '700',
  },
  unitConversion: {
    fontSize: 10,
    color: '#9CA3AF',
    marginTop: 2,
    fontStyle: 'italic',
  },
  selectedIndicator: {
    position: 'absolute',
    top: 4,
    right: 4,
  },
});
