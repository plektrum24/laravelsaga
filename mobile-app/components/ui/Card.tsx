import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ViewStyle } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

type CardVariant = 'product' | 'metric' | 'summary' | 'promotional';

interface CardProps {
  variant?: CardVariant;
  title?: string;
  subtitle?: string;
  children?: React.ReactNode;
  onPress?: () => void;
  style?: ViewStyle;
}

export default function Card({
  variant = 'summary',
  title,
  subtitle,
  children,
  onPress,
  style,
}: CardProps) {
  const containerStyles = [
    styles.container,
    styles[variant],
    onPress && styles.pressable,
    style,
  ];

  return (
    <TouchableOpacity
      style={containerStyles}
      onPress={onPress}
      disabled={!onPress}
      activeOpacity={onPress ? 0.7 : 1}
    >
      {(title || subtitle) && (
        <View style={styles.header}>
          {title && <Text style={styles.title}>{title}</Text>}
          {subtitle && <Text style={styles.subtitle}>{subtitle}</Text>}
        </View>
      )}
      {children}
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
  },
  pressable: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  // Variants
  product: {
    width: 160,
    padding: 12,
  },
  metric: {
    flex: 1,
    minWidth: 140,
    padding: 16,
  },
  summary: {},
  promotional: {
    backgroundColor: '#4F46E5',
    padding: 20,
  },
  header: {
    marginBottom: 12,
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 13,
    color: '#6B7280',
  },
});

// Metric Card Sub-component
export function MetricCard({
  icon,
  label,
  value,
  trend,
  color = '#4F46E5',
}: {
  icon: string;
  label: string;
  value: string | number;
  trend?: number;
  color?: string;
}) {
  return (
    <Card variant="metric">
      <View style={[styles.metricIcon, { backgroundColor: `${color}15` }]}>
        <Ionicons name={icon as any} size={20} color={color} />
      </View>
      <Text style={styles.metricLabel}>{label}</Text>
      <Text style={styles.metricValue}>{value}</Text>
      {trend !== undefined && (
        <View style={styles.trendContainer}>
          <Ionicons
            name={trend >= 0 ? 'trending-up' : 'trending-down'}
            size={12}
            color={trend >= 0 ? '#10B981' : '#EF4444'}
          />
          <Text
            style={[
              styles.trendText,
              trend >= 0 ? styles.trendPositive : styles.trendNegative,
            ]}
          >
            {trend >= 0 ? '+' : ''}{trend}%
          </Text>
        </View>
      )}
    </Card>
  );
}

const metricStyles = StyleSheet.create({
  metricIcon: {
    width: 36,
    height: 36,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  metricLabel: {
    fontSize: 12,
    color: '#6B7280',
    marginBottom: 4,
  },
  metricValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#111827',
  },
  trendContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
    gap: 4,
  },
  trendText: {
    fontSize: 11,
    fontWeight: '600',
  },
  trendPositive: {
    color: '#10B981',
  },
  trendNegative: {
    color: '#EF4444',
  },
});

Object.assign(styles, metricStyles);
