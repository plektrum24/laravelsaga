import { Platform } from 'react-native';
import * as Device from 'expo-device';
import Constants from 'expo-constants';

export const config = {
  // API Configuration
  api: {
    baseURL: process.env.EXPO_PUBLIC_API_URL || 'http://localhost:8000/api',
    timeout: parseInt(process.env.EXPO_PUBLIC_API_TIMEOUT || '30000', 10),
  },

  // App Configuration
  app: {
    name: process.env.EXPO_PUBLIC_APP_NAME || 'Saga Toko',
    scheme: process.env.EXPO_PUBLIC_SCHEME || 'sagatoko',
    version: Constants.expoConfig?.version || '1.0.0',
  },

  // Firebase Configuration
  firebase: {
    projectId: process.env.EXPO_PUBLIC_FIREBASE_PROJECT_ID,
    appId: process.env.EXPO_PUBLIC_FIREBASE_APP_ID,
    apiKey: process.env.EXPO_PUBLIC_FIREBASE_API_KEY,
  },

  // Platform detection
  isDevice: Device.isDevice,
  platform: Platform.OS,
  isIOS: Platform.OS === 'ios',
  isAndroid: Platform.OS === 'android',
};

export type Config = typeof config;
