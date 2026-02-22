import React from 'react';
import { View, Text, StyleSheet, Modal, ActivityIndicator, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import Button from './Button';

interface ModalProps {
  visible: boolean;
  title?: string;
  children?: React.ReactNode;
  onClose?: () => void;
  onConfirm?: () => void;
  confirmTitle?: string;
  cancelTitle?: string;
  showCancel?: boolean;
  showConfirm?: boolean;
  isLoading?: boolean;
}

export default function ModalComponent({
  visible,
  title,
  children,
  onClose,
  onConfirm,
  confirmTitle = 'Confirm',
  cancelTitle = 'Cancel',
  showCancel = true,
  showConfirm = true,
  isLoading = false,
}: ModalProps) {
  return (
    <Modal
      visible={visible}
      transparent={true}
      animationType="fade"
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <View style={styles.container}>
          {title && <Text style={styles.title}>{title}</Text>}
          
          <View style={styles.content}>
            {children}
          </View>
          
          {(showCancel || showConfirm) && (
            <View style={styles.actions}>
              {showCancel && (
                <Button
                  title={cancelTitle}
                  variant="ghost"
                  size="md"
                  onPress={onClose}
                  style={styles.actionButton}
                />
              )}
              {showConfirm && (
                <Button
                  title={confirmTitle}
                  variant="primary"
                  size="md"
                  onPress={onConfirm}
                  loading={isLoading}
                  style={styles.actionButton}
                />
              )}
            </View>
          )}
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  container: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 20,
    width: '100%',
    maxWidth: 400,
  },
  title: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 16,
  },
  content: {
    marginBottom: 20,
  },
  actions: {
    flexDirection: 'row',
    gap: 12,
  },
  actionButton: {
    flex: 1,
  },
});

// Loading Modal Sub-component
export function LoadingModal({ visible, message = 'Loading...' }: { visible: boolean; message?: string }) {
  return (
    <Modal visible={visible} transparent={true} animationType="fade">
      <View style={loadingStyles.overlay}>
        <View style={loadingStyles.container}>
          <ActivityIndicator size="large" color="#4F46E5" />
          <Text style={loadingStyles.message}>{message}</Text>
        </View>
      </View>
    </Modal>
  );
}

const loadingStyles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  container: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 24,
    alignItems: 'center',
    minWidth: 150,
  },
  message: {
    marginTop: 16,
    fontSize: 14,
    color: '#6B7280',
  },
});

// Alert Modal Sub-component
export function AlertModal({
  visible,
  type = 'info',
  title,
  message,
  onClose,
}: {
  visible: boolean;
  type?: 'success' | 'warning' | 'error' | 'info';
  title: string;
  message: string;
  onClose: () => void;
}) {
  const typeConfig = {
    success: { icon: 'checkmark-circle', color: '#10B981' },
    warning: { icon: 'warning', color: '#F59E0B' },
    error: { icon: 'close-circle', color: '#EF4444' },
    info: { icon: 'information-circle', color: '#3B82F6' },
  };

  const config = typeConfig[type];

  return (
    <Modal visible={visible} transparent={true} animationType="fade">
      <View style={alertStyles.overlay}>
        <View style={alertStyles.container}>
          <Ionicons name={config.icon as any} size={48} color={config.color} />
          <Text style={alertStyles.title}>{title}</Text>
          <Text style={alertStyles.message}>{message}</Text>
          <Button title="OK" variant="primary" size="md" onPress={onClose} style={alertStyles.button} />
        </View>
      </View>
    </Modal>
  );
}

const alertStyles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  container: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 24,
    alignItems: 'center',
    width: '100%',
    maxWidth: 350,
  },
  title: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
    marginTop: 16,
    marginBottom: 8,
  },
  message: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: 20,
  },
  button: {
    minWidth: 120,
  },
});
