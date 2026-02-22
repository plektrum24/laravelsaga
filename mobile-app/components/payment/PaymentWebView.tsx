import React, { useEffect, useRef, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  ActivityIndicator,
  Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { WebView } from 'react-native-webview';

interface PaymentWebViewProps {
  visible: boolean;
  paymentUrl?: string;
  onSuccess?: (transactionId: string) => void;
  onPending?: (transactionId: string) => void;
  onFailure?: (error: string) => void;
  onClose?: () => void;
}

export default function PaymentWebView({
  visible,
  paymentUrl,
  onSuccess,
  onPending,
  onFailure,
  onClose,
}: PaymentWebViewProps) {
  const webViewRef = useRef<WebView>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [currentUrl, setCurrentUrl] = useState('');

  // Midtrans Snap URLs for detection
  const finishUrl = 'https://app.midtrans.com/snap/v2/vtweb/finish';
  const successPatterns = ['finish?status=success', 'transaction_status=settlement'];
  const pendingPatterns = ['finish?status=pending', 'transaction_status=pending'];
  const failurePatterns = ['finish?status=failure', 'finish?status=error'];

  const handleNavigationStateChange = (navState: any) => {
    const url = navState.url;
    setCurrentUrl(url);

    // Check for payment completion
    if (url.includes('finish')) {
      const urlParams = new URLSearchParams(url.split('?')[1]);
      const status = urlParams.get('status');
      const transactionId = urlParams.get('transaction_id') || '';

      if (status === 'success' || successPatterns.some(p => url.includes(p))) {
        onSuccess?.(transactionId);
        handleClose();
      } else if (status === 'pending' || pendingPatterns.some(p => url.includes(p))) {
        onPending?.(transactionId);
        handleClose();
      } else if (status === 'failure' || status === 'error' || failurePatterns.some(p => url.includes(p))) {
        onFailure?.('Payment failed. Please try again.');
        handleClose();
      }
    }

    setIsLoading(navState.loading);
  };

  const handleClose = () => {
    onClose?.();
  };

  const handleBackPress = () => {
    if (webViewRef.current) {
      webViewRef.current.goBack();
    }
  };

  if (!paymentUrl) {
    return null;
  }

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent={false}
      onRequestClose={handleClose}
    >
      <View style={styles.container}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity
            style={styles.backButton}
            onPress={handleClose}
            disabled={isLoading}
          >
            <Ionicons name="close" size={24} color="#111827" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Secure Payment</Text>
          <View style={styles.placeholder} />
        </View>

        {/* Loading Indicator */}
        {isLoading && (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#4F46E5" />
            <Text style={styles.loadingText}>Loading payment page...</Text>
          </View>
        )}

        {/* WebView */}
        <WebView
          ref={webViewRef}
          source={{ uri: paymentUrl }}
          style={styles.webView}
          onNavigationStateChange={handleNavigationStateChange}
          onLoadStart={() => setIsLoading(true)}
          onLoadEnd={() => setIsLoading(false)}
          onError={(syntheticEvent) => {
            const { nativeEvent } = syntheticEvent;
            console.error('WebView error:', nativeEvent);
            onFailure?.('Failed to load payment page');
          }}
          startInLoadingState={true}
          javaScriptEnabled={true}
          domStorageEnabled={true}
          sharedCookiesEnabled={true}
          thirdPartyCookiesEnabled={true}
          cacheEnabled={false}
        />

        {/* Security Notice */}
        <View style={styles.securityNotice}>
          <Ionicons name="shield-checkmark" size={16} color="#10B981" />
          <Text style={styles.securityText}>
            Secured by Midtrans
          </Text>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
    paddingTop: Platform.OS === 'ios' ? 50 : 12,
  },
  backButton: {
    padding: 8,
  },
  headerTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  placeholder: {
    width: 40,
  },
  loadingContainer: {
    position: 'absolute',
    top: '50%',
    left: 0,
    right: 0,
    alignItems: 'center',
    zIndex: 1,
  },
  loadingText: {
    marginTop: 16,
    fontSize: 14,
    color: '#9CA3AF',
  },
  webView: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  securityNotice: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#ECFDF5',
    paddingVertical: 8,
    paddingHorizontal: 16,
    gap: 6,
  },
  securityText: {
    fontSize: 12,
    color: '#059669',
    fontWeight: '500',
  },
});
