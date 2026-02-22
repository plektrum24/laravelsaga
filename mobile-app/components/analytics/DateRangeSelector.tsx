import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Modal, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { format } from 'date-fns';

export interface DateRangePreset {
  label: string;
  value: string;
  days?: number;
}

export interface DateRange {
  start: string;
  end: string;
  preset?: string;
}

interface DateRangeSelectorProps {
  dateRange: DateRange;
  onDateRangeChange: (dateRange: DateRange) => void;
}

const DATE_RANGES: DateRangePreset[] = [
  { label: 'Today', value: 'today' },
  { label: 'Yesterday', value: 'yesterday' },
  { label: 'Last 7 Days', value: 'last_7_days', days: 7 },
  { label: 'Last 30 Days', value: 'last_30_days', days: 30 },
  { label: 'Month to Date', value: 'mtd' },
  { label: 'Quarter to Date', value: 'qtd' },
  { label: 'Year to Date', value: 'ytd' },
  { label: 'Custom', value: 'custom' },
];

export default function DateRangeSelector({
  dateRange,
  onDateRangeChange,
}: DateRangeSelectorProps) {
  const [showModal, setShowModal] = useState(false);

  const getCurrentPreset = () => {
    const preset = DATE_RANGES.find((r) => r.value === dateRange.preset);
    return preset?.label || 'Custom';
  };

  const calculateDateRange = (preset: string): DateRange => {
    const today = new Date();
    let start = new Date();
    let end = today;

    switch (preset) {
      case 'today':
        start = today;
        end = today;
        break;
      case 'yesterday':
        start = new Date(today);
        start.setDate(start.getDate() - 1);
        end = start;
        break;
      case 'last_7_days':
        start = new Date(today);
        start.setDate(start.getDate() - 7);
        end = today;
        break;
      case 'last_30_days':
        start = new Date(today);
        start.setDate(start.getDate() - 30);
        end = today;
        break;
      case 'mtd':
        start = new Date(today.getFullYear(), today.getMonth(), 1);
        end = today;
        break;
      case 'qtd':
        const quarter = Math.floor(today.getMonth() / 3);
        start = new Date(today.getFullYear(), quarter * 3, 1);
        end = today;
        break;
      case 'ytd':
        start = new Date(today.getFullYear(), 0, 1);
        end = today;
        break;
    }

    return {
      start: format(start, 'yyyy-MM-dd'),
      end: format(end, 'yyyy-MM-dd'),
      preset,
    };
  };

  const handlePresetSelect = (preset: string) => {
    const newDateRange = calculateDateRange(preset);
    onDateRangeChange(newDateRange);
    if (preset !== 'custom') {
      setShowModal(false);
    }
  };

  const formatDateRange = () => {
    const start = new Date(dateRange.start);
    const end = new Date(dateRange.end);
    
    if (dateRange.start === dateRange.end) {
      return format(start, 'MMM d, yyyy');
    }
    
    return `${format(start, 'MMM d')} - ${format(end, 'MMM d, yyyy')}`;
  };

  return (
    <>
      <TouchableOpacity
        style={styles.container}
        onPress={() => setShowModal(true)}
        activeOpacity={0.7}
      >
        <Ionicons name="calendar-outline" size={20} color="#6B7280" />
        <Text style={styles.dateText}>{formatDateRange()}</Text>
        <Ionicons name="chevron-down" size={16} color="#6B7280" />
      </TouchableOpacity>

      <Modal
        visible={showModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Date Range</Text>
              <TouchableOpacity onPress={() => setShowModal(false)}>
                <Ionicons name="close" size={24} color="#111827" />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalContent}>
              {DATE_RANGES.map((preset) => (
                <TouchableOpacity
                  key={preset.value}
                  style={[
                    styles.presetButton,
                    dateRange.preset === preset.value && styles.presetButtonActive,
                  ]}
                  onPress={() => handlePresetSelect(preset.value)}
                >
                  <Text
                    style={[
                      styles.presetButtonText,
                      dateRange.preset === preset.value && styles.presetButtonTextActive,
                    ]}
                  >
                    {preset.label}
                  </Text>
                  {dateRange.preset === preset.value && (
                    <Ionicons name="checkmark" size={20} color="#4F46E5" />
                  )}
                </TouchableOpacity>
              ))}
            </ScrollView>

            <View style={styles.modalFooter}>
              <Text style={styles.modalHint}>
                Selected: {formatDateRange()}
              </Text>
            </View>
          </View>
        </View>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 8,
    gap: 8,
  },
  dateText: {
    fontSize: 14,
    color: '#111827',
    fontWeight: '500',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContainer: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '70%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
  },
  modalContent: {
    padding: 16,
  },
  presetButton: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
    borderRadius: 10,
    marginBottom: 8,
  },
  presetButtonActive: {
    backgroundColor: '#EEF2FF',
  },
  presetButtonText: {
    fontSize: 16,
    color: '#111827',
  },
  presetButtonTextActive: {
    color: '#4F46E5',
    fontWeight: '600',
  },
  modalFooter: {
    padding: 20,
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
  },
  modalHint: {
    fontSize: 13,
    color: '#6B7280',
    textAlign: 'center',
  },
});
