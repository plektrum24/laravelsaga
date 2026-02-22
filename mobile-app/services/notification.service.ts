import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';
import { apiClient, API_ENDPOINTS } from '../api';

export interface NotificationPermission {
  granted: boolean;
  status: 'granted' | 'denied' | 'undetermined';
}

export interface NotificationSettings {
  orderUpdates: boolean;
  promotionalOffers: boolean;
  pointsExpiry: boolean;
  newRewards: boolean;
  priceDropAlerts: boolean;
}

/**
 * Request notification permission
 */
export async function requestNotificationPermission(): Promise<NotificationPermission> {
  if (!Device.isDevice) {
    return {
      granted: false,
      status: 'undetermined',
    };
  }

  const { status: existingStatus } = await Notifications.getPermissionsAsync();
  let finalStatus = existingStatus;

  if (existingStatus !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }

  if (finalStatus !== 'granted') {
    return {
      granted: false,
      status: finalStatus,
    };
  }

  return {
    granted: true,
    status: 'granted',
  };
}

/**
 * Get FCM push token
 */
export async function getPushToken(): Promise<string | null> {
  try {
    if (!Device.isDevice) {
      console.log('Push notifications not available on simulator');
      return null;
    }

    const { data: token } = await Notifications.getExpoPushTokenAsync({
      projectId: process.env.EXPO_PUBLIC_FIREBASE_PROJECT_ID,
    });

    return token;
  } catch (error) {
    console.error('Error getting push token:', error);
    return null;
  }
}

/**
 * Register device for push notifications
 */
export async function registerDeviceForPushNotifications(): Promise<boolean> {
  try {
    const permission = await requestNotificationPermission();
    
    if (!permission.granted) {
      return false;
    }

    const pushToken = await getPushToken();
    
    if (!pushToken) {
      return false;
    }

    // Register with backend
    await apiClient.post(API_ENDPOINTS.NOTIFICATIONS_REGISTER, {
      push_token: pushToken,
      device_type: Platform.OS,
      device_model: Device.modelName,
    });

    return true;
  } catch (error) {
    console.error('Error registering device:', error);
    return false;
  }
}

/**
 * Update notification preferences
 */
export async function updateNotificationPreferences(
  settings: NotificationSettings
): Promise<boolean> {
  try {
    await apiClient.put(API_ENDPOINTS.NOTIFICATIONS_PREFERENCES, settings);
    return true;
  } catch (error) {
    console.error('Error updating preferences:', error);
    return false;
  }
}

/**
 * Get notification preferences
 */
export async function getNotificationPreferences(): Promise<NotificationSettings> {
  try {
    const response = await apiClient.get(API_ENDPOINTS.NOTIFICATIONS_PREFERENCES);
    return response.data || response;
  } catch (error) {
    console.error('Error getting preferences:', error);
    // Return defaults
    return {
      orderUpdates: true,
      promotionalOffers: true,
      pointsExpiry: true,
      newRewards: true,
      priceDropAlerts: false,
    };
  }
}

/**
 * Configure notification handler (for foreground notifications)
 */
export function configureNotificationHandler() {
  // Handle notifications received while app is foregrounded
  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldShowAlert: true,
      shouldPlaySound: true,
      shouldSetBadge: true,
    }),
  });
}

/**
 * Send local notification (for testing)
 */
export async function sendLocalNotification({
  title,
  body,
  data,
}: {
  title: string;
  body: string;
  data?: any;
}) {
  await Notifications.scheduleNotificationAsync({
    content: {
      title,
      body,
      data,
      sound: true,
    },
    trigger: null, // Send immediately
  });
}

/**
 * Schedule notification for later
 */
export async function scheduleNotification({
  title,
  body,
  triggerSeconds,
  data,
}: {
  title: string;
  body: string;
  triggerSeconds: number;
  data?: any;
}) {
  await Notifications.scheduleNotificationAsync({
    content: {
      title,
      body,
      data,
      sound: true,
    },
    trigger: {
      seconds: triggerSeconds,
    },
  });
}

/**
 * Cancel all scheduled notifications
 */
export async function cancelAllNotifications() {
  await Notifications.cancelAllScheduledNotificationsAsync();
}

/**
 * Get notification badge count
 */
export async function getBadgeCount(): Promise<number> {
  return await Notifications.getBadgeCountAsync();
}

/**
 * Set notification badge count
 */
export async function setBadgeCount(count: number) {
  await Notifications.setBadgeCountAsync(count);
}

/**
 * Clear badge count
 */
export async function clearBadgeCount() {
  await Notifications.setBadgeCountAsync(0);
}
