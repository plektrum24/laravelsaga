import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Modal,
  Alert,
  Vibration,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { CameraView, useCameraPermissions } from 'expo-camera';

interface BarcodeScannerProps {
  visible: boolean;
  onScan?: (data: string, type: string) => void;
  onClose?: () => void;
  torch?: boolean;
  showGallery?: boolean;
  onGalleryPress?: () => void;
}

export type BarcodeType =
  | 'barcode'
  | 'qr'
  | 'ean13'
  | 'ean8'
  | 'upc'
  | 'code128'
  | 'code39'
  | 'code93'
  | 'datamatrix'
  | 'pdf417';

export default function BarcodeScanner({
  visible,
  onScan,
  onClose,
  torch = true,
  showGallery = true,
  onGalleryPress,
}: BarcodeScannerProps) {
  const [permission, requestPermission] = useCameraPermissions();
  const [scanned, setScanned] = useState(false);
  const [torchOn, setTorchesOn] = useState(false);

  useEffect(() => {
    if (visible && !permission) {
      requestPermission();
    }
    if (!visible) {
      setScanned(false);
      setTorchesOn(false);
    }
  }, [visible]);

  const handleBarCodeScanned = ({ type, data }: { type: string; data: string }) => {
    if (scanned) return;

    setScanned(true);
    Vibration.vibrate();

    if (onScan) {
      onScan(data, type);
    } else {
      Alert.alert(
        'Barcode Scanned',
        `Type: ${type}\nData: ${data}`,
        [
          { text: 'Scan Again', onPress: () => setScanned(false) },
          { text: 'Close', onPress: () => onClose?.() },
        ]
      );
    }
  };

  const toggleTorch = () => {
    setTorchesOn(!torchOn);
  };

  if (!visible) return null;

  if (!permission) {
    return (
      <Modal visible={visible} animationType="slide" transparent>
        <View style={styles.container}>
          <View style={styles.permissionContainer}>
            <Ionicons name="camera-outline" size={64} color="#9CA3AF" />
            <Text style={styles.permissionTitle}>Camera Permission Required</Text>
            <Text style={styles.permissionText}>
              We need your permission to scan barcodes
            </Text>
            <TouchableOpacity style={styles.grantButton} onPress={requestPermission}>
              <Text style={styles.grantButtonText}>Grant Permission</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.closeButton} onPress={() => onClose?.()}>
              <Text style={styles.closeButtonText}>Close</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    );
  }

  if (!permission.granted) {
    return (
      <Modal visible={visible} animationType="slide" transparent>
        <View style={styles.container}>
          <View style={styles.permissionContainer}>
            <Ionicons name="warning" size={64} color="#F59E0B" />
            <Text style={styles.permissionTitle}>Camera Access Denied</Text>
            <Text style={styles.permissionText}>
              Please enable camera access in your device settings
            </Text>
            <TouchableOpacity style={styles.closeButton} onPress={() => onClose?.()}>
              <Text style={styles.closeButtonText}>Close</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    );
  }

  return (
    <Modal visible={visible} animationType="slide" transparent>
      <View style={styles.container}>
        <View style={styles.scannerContainer}>
          {/* Header */}
          <View style={styles.header}>
            <Text style={styles.headerTitle}>Scan Barcode</Text>
            <TouchableOpacity onPress={() => onClose?.()}>
              <Ionicons name="close" size={28} color="#FFFFFF" />
            </TouchableOpacity>
          </View>

          {/* Camera View */}
          <View style={styles.cameraContainer}>
            <CameraView
              style={styles.camera}
              barcodeScannerSettings={{
                barcodeTypes: [
                  'ean13',
                  'ean8',
                  'upc_e',
                  'upc_a',
                  'code128',
                  'code39',
                  'code93',
                  'qr',
                  'datamatrix',
                  'pdf417',
                ],
              }}
              enableTorch={torchOn}
              onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
            />
            
            {/* Scan Overlay */}
            <View style={styles.overlay}>
              <View style={styles.scanFrame}>
                <View style={[styles.corner, styles.topLeft]} />
                <View style={[styles.corner, styles.topRight]} />
                <View style={[styles.corner, styles.bottomLeft]} />
                <View style={[styles.corner, styles.bottomRight]} />
              </View>
            </View>

            {/* Scan Instructions */}
            <View style={styles.instructions}>
              <Text style={styles.instructionsText}>
                Position the barcode within the frame
              </Text>
            </View>
          </View>

          {/* Controls */}
          <View style={styles.controls}>
            {torch && (
              <TouchableOpacity style={styles.controlButton} onPress={toggleTorch}>
                <Ionicons
                  name={torchOn ? 'flash' : 'flash-outline'}
                  size={28}
                  color="#FFFFFF"
                />
                <Text style={styles.controlText}>
                  {torchOn ? 'Flash On' : 'Flash Off'}
                </Text>
              </TouchableOpacity>
            )}

            {showGallery && (
              <TouchableOpacity
                style={styles.controlButton}
                onPress={onGalleryPress || (() => {})}
              >
                <Ionicons name="images-outline" size={28} color="#FFFFFF" />
                <Text style={styles.controlText}>Gallery</Text>
              </TouchableOpacity>
            )}

            <TouchableOpacity
              style={[styles.controlButton, scanned && styles.controlButtonActive]}
              onPress={() => setScanned(false)}
            >
              <Ionicons name="refresh" size={28} color="#FFFFFF" />
              <Text style={styles.controlText}>Rescan</Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.9)',
  },
  permissionContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 32,
    backgroundColor: '#1F2937',
  },
  permissionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginTop: 24,
    marginBottom: 12,
  },
  permissionText: {
    fontSize: 14,
    color: '#9CA3AF',
    textAlign: 'center',
    marginBottom: 32,
  },
  grantButton: {
    backgroundColor: '#4F46E5',
    paddingHorizontal: 32,
    paddingVertical: 14,
    borderRadius: 12,
    marginBottom: 16,
  },
  grantButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  closeButton: {
    paddingHorizontal: 32,
    paddingVertical: 14,
  },
  closeButtonText: {
    color: '#9CA3AF',
    fontSize: 16,
  },
  scannerContainer: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    paddingTop: 50,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  cameraContainer: {
    flex: 1,
    position: 'relative',
  },
  camera: {
    flex: 1,
  },
  overlay: {
    ...StyleSheet.absoluteFillObject,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scanFrame: {
    width: 280,
    height: 280,
    position: 'relative',
  },
  corner: {
    position: 'absolute',
    width: 40,
    height: 40,
    borderColor: '#4F46E5',
  },
  topLeft: {
    top: 0,
    left: 0,
    borderTopWidth: 4,
    borderLeftWidth: 4,
    borderTopLeftRadius: 8,
  },
  topRight: {
    top: 0,
    right: 0,
    borderTopWidth: 4,
    borderRightWidth: 4,
    borderTopRightRadius: 8,
  },
  bottomLeft: {
    bottom: 0,
    left: 0,
    borderBottomWidth: 4,
    borderLeftWidth: 4,
    borderBottomLeftRadius: 8,
  },
  bottomRight: {
    bottom: 0,
    right: 0,
    borderBottomWidth: 4,
    borderRightWidth: 4,
    borderBottomRightRadius: 8,
  },
  instructions: {
    position: 'absolute',
    bottom: 80,
    left: 0,
    right: 0,
    alignItems: 'center',
  },
  instructionsText: {
    fontSize: 14,
    color: '#FFFFFF',
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 16,
  },
  controls: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    alignItems: 'center',
    padding: 24,
    paddingBottom: 40,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
  },
  controlButton: {
    alignItems: 'center',
    gap: 8,
  },
  controlButtonActive: {
    opacity: 0.5,
  },
  controlText: {
    fontSize: 12,
    color: '#FFFFFF',
    fontWeight: '500',
  },
});
