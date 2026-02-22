import React from 'react';
import { View, Text, StyleSheet, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface MetricCardProps {
  label: string;
  value: string | number;
  trend?: 'up' | 'down' | 'stable';
  change?: number;
  icon?: string;
  color?: string;
  isLoading?: boolean;
  subtitle?: string;
}

export default function MetricCard({
  label,
  value,
  trend,
  change,
  icon = 'trending-up',
  color = '#4F46E5',
  isLoading = false,
  subtitle,
}: MetricCardProps) {
  const getTrendIcon = () => {
    if (trend === 'up') return 'arrow-up';
    if (trend === 'down') return 'arrow-down';
    return 'remove';
  };

  const getTrendColor = () => {
    if (trend === 'up') return '#10B981';
    if (trend === 'down') return '#EF4444';
    return '#6B7280';
  };

  const formatValue = (val: string | number) => {
    if (typeof val === 'number') {
      if (val >= 1000000000) {
        return `${(val / 1000000000).toFixed(1)}B`;
      }
      if (val >= 1000000) {
        return `${(val / 1000000).toFixed(1)}M`;
      }
      if (val >= 1000) {
        return val.toLocaleString('id-ID');
      }
      return val.toString();
    }
    return val;
  };

  if (isLoading) {
    return (
      <View style={[styles.container, { backgroundColor: `${color}08` }]}>
        <ActivityIndicator size="small" color={color} />
        <Text style={styles.label}>{label}</Text>
      </View>
    );
  }

  return (
    <View style={[styles.container, { backgroundColor: `${color}08` }]}>
      <View style={styles.header}>
        <View style={[styles.iconContainer, { backgroundColor: `${color}15` }]}>
          <Ionicons name={icon as any} size={20} color={color} />
        </View>
        {change !== undefined && (
          <View style={[styles.trendContainer, { backgroundColor: `${getTrendColor()}15` }]}>
            <Ionicons name={getTrendIcon()} size={12} color={getTrendColor()} />
            <Text style={[styles.trendText, { color: getTrendColor() }]}>
              {change > 0 ? '+' : ''}{change.toFixed(1)}%
            </Text>
          </View>
        )}
      </View>
      
      <Text style={styles.value}>{formatValue(value)}</Text>
      <Text style={styles.label}>{label}</Text>
      
      {subtitle && (
        <Text style={styles.subtitle}>{subtitle}</Text>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    minWidth: 140,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  iconContainer: {
    width: 36,
    height: 36,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  trendContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 4,
  },
  trendText: {
    fontSize: 11,
    fontWeight: '600',
  },
  value: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#111827',
    marginBottom: 4,
  },
  label: {
    fontSize: 12,
    color: '#6B7280',
    fontWeight: '500',
  },
  subtitle: {
    fontSize: 10,
    color: '#9CA3AF',
    marginTop: 4,
  },
});
