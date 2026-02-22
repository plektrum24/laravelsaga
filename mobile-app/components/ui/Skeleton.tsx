import React from 'react';
import { View, Text, StyleSheet, ActivityIndicator } from 'react-native';

interface SkeletonProps {
  type?: 'text' | 'image' | 'card' | 'list';
  width?: number | string;
  height?: number;
  lines?: number;
}

export default function Skeleton({
  type = 'text',
  width = '100%',
  height = 16,
  lines = 1,
}: SkeletonProps) {
  if (type === 'image') {
    return <View style={[styles.image, { width, height }]} />;
  }

  if (type === 'card') {
    return (
      <View style={styles.card}>
        <View style={[styles.cardImage, { height }]} />
        <View style={styles.cardContent}>
          <Skeleton type="text" width="70%" height={16} />
          <Skeleton type="text" width="50%" height={14} />
          <Skeleton type="text" width="90%" height={14} />
        </View>
      </View>
    );
  }

  if (type === 'list') {
    return (
      <View style={styles.list}>
        {Array.from({ length: lines }).map((_, i) => (
          <View key={i} style={styles.listItem}>
            <View style={[styles.listImage, { width: height, height }]} />
            <View style={styles.listContent}>
              <Skeleton type="text" width="60%" height={16} />
              <Skeleton type="text" width="40%" height={14} />
            </View>
          </View>
        ))}
      </View>
    );
  }

  return (
    <View style={{ width }}>
      {Array.from({ length: lines }).map((_, i) => (
        <View
          key={i}
          style={[
            styles.text,
            {
              height,
              marginBottom: i === lines - 1 ? 0 : 8,
              width: lines > 1 && i === lines - 1 ? '60%' : '100%',
            },
          ]}
        />
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  text: {
    backgroundColor: '#E5E7EB',
    borderRadius: 4,
  },
  image: {
    backgroundColor: '#E5E7EB',
    borderRadius: 8,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    overflow: 'hidden',
    marginBottom: 16,
  },
  cardImage: {
    backgroundColor: '#E5E7EB',
  },
  cardContent: {
    padding: 16,
    gap: 8,
  },
  list: {
    gap: 12,
  },
  listItem: {
    flexDirection: 'row',
    gap: 12,
  },
  listImage: {
    backgroundColor: '#E5E7EB',
    borderRadius: 8,
  },
  listContent: {
    flex: 1,
    gap: 8,
  },
});

// Loading State Component
export function LoadingState({ message = 'Loading...' }: { message?: string }) {
  return (
    <View style={loadingStyles.container}>
      <ActivityIndicator size="large" color="#4F46E5" />
      <Text style={loadingStyles.message}>{message}</Text>
    </View>
  );
}

const loadingStyles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  message: {
    marginTop: 16,
    fontSize: 14,
    color: '#6B7280',
  },
});

// Empty State Component
export function EmptyState({
  icon = 'document-outline',
  title,
  message,
  actionLabel,
  onAction,
}: {
  icon?: string;
  title: string;
  message: string;
  actionLabel?: string;
  onAction?: () => void;
}) {
  return (
    <View style={emptyStyles.container}>
      <Ionicons name={icon as any} size={64} color="#D1D5DB" />
      <Text style={emptyStyles.title}>{title}</Text>
      <Text style={emptyStyles.message}>{message}</Text>
      {actionLabel && onAction && (
        <Button title={actionLabel} variant="primary" size="md" onPress={onAction} style={emptyStyles.button} />
      )}
    </View>
  );
}

import { Ionicons } from '@expo/vector-icons';
import Button from './Button';

const emptyStyles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 32,
  },
  title: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
    marginTop: 24,
    marginBottom: 8,
  },
  message: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: 24,
  },
  button: {
    minWidth: 150,
  },
});

// Error State Component
export function ErrorState({
  title = 'Something went wrong',
  message,
  onRetry,
}: {
  title?: string;
  message?: string;
  onRetry?: () => void;
}) {
  return (
    <View style={errorStyles.container}>
      <Ionicons name="alert-circle" size={48} color="#EF4444" />
      <Text style={errorStyles.title}>{title}</Text>
      {message && <Text style={errorStyles.message}>{message}</Text>}
      {onRetry && (
        <Button title="Retry" variant="primary" size="md" onPress={onRetry} style={errorStyles.button} />
      )}
    </View>
  );
}

const errorStyles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 32,
  },
  title: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
    marginTop: 24,
    marginBottom: 8,
  },
  message: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: 24,
  },
  button: {
    minWidth: 150,
  },
});
