import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  Alert,
} from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../hooks/useAuth';
import { useAuthStore } from '../../stores/auth.store';

export default function ProfileScreen() {
  const { user, isAuthenticated, logout } = useAuth();
  const clearAuth = useAuthStore((state) => {
    return { clear: () => state.logout() };
  });

  const handleLogout = () => {
    Alert.alert('Logout', 'Are you sure you want to logout?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Logout',
        style: 'destructive',
        onPress: async () => {
          await logout();
          router.replace('/(auth)/login');
        },
      },
    ]);
  };

  const menuItems = [
    {
      icon: 'person',
      label: 'Edit Profile',
      color: '#4F46E5',
      onPress: () => router.push('/profile/edit'),
    },
    {
      icon: 'location',
      label: 'My Addresses',
      color: '#10B981',
      onPress: () => router.push('/profile/addresses'),
    },
    {
      icon: 'card',
      label: 'Payment Methods',
      color: '#F59E0B',
      onPress: () => router.push('/profile/payments'),
    },
    {
      icon: 'notifications',
      label: 'Notifications',
      color: '#3B82F6',
      onPress: () => router.push('/profile/notifications'),
    },
    {
      icon: 'shield-checkmark',
      label: 'Privacy & Security',
      color: '#8B5CF6',
      onPress: () => router.push('/profile/security'),
    },
    {
      icon: 'help-circle',
      label: 'Help & Support',
      color: '#06B6D4',
      onPress: () => router.push('/profile/help'),
    },
    {
      icon: 'document-text',
      label: 'Terms & Conditions',
      color: '#6B7280',
      onPress: () => router.push('/profile/terms'),
    },
  ];

  const orderMenuItems = [
    {
      icon: 'bag',
      label: 'My Orders',
      color: '#4F46E5',
      onPress: () => router.push('/order/history'),
    },
    {
      icon: 'heart',
      label: 'Wishlist',
      color: '#EF4444',
      onPress: () => router.push('/wishlist'),
    },
    {
      icon: 'star',
      label: 'My Reviews',
      color: '#F59E0B',
      onPress: () => router.push('/profile/reviews'),
    },
  ];

  if (!isAuthenticated || !user) {
    return (
      <View style={styles.notLoggedInContainer}>
        <View style={styles.guestHeader}>
          <Ionicons name="person-circle-outline" size={80} color="#9CA3AF" />
        </View>
        <Text style={styles.guestTitle}>Guest User</Text>
        <Text style={styles.guestSubtitle}>
          Sign in to access your account and unlock all features
        </Text>
        <View style={styles.guestButtons}>
          <TouchableOpacity
            style={styles.loginButton}
            onPress={() => router.push('/(auth)/login')}
          >
            <Ionicons name="log-in" size={20} color="#FFFFFF" />
            <Text style={styles.loginButtonText}>Sign In</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={styles.registerButton}
            onPress={() => router.push('/(auth)/register')}
          >
            <Text style={styles.registerButtonText}>Create Account</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      {/* Profile Header */}
      <View style={styles.profileHeader}>
        <View style={styles.avatarContainer}>
          {user.avatar ? (
            <Image source={{ uri: user.avatar }} style={styles.avatar} />
          ) : (
            <View style={styles.avatarPlaceholder}>
              <Text style={styles.avatarText}>
                {user.name?.charAt(0).toUpperCase() || 'U'}
              </Text>
            </View>
          )}
          <TouchableOpacity style={styles.editAvatarBtn}>
            <Ionicons name="camera" size={16} color="#FFFFFF" />
          </TouchableOpacity>
        </View>
        <Text style={styles.userName}>{user.name}</Text>
        <Text style={styles.userEmail}>{user.email}</Text>
        <TouchableOpacity
          style={styles.editProfileBtn}
          onPress={() => router.push('/profile/edit')}
        >
          <Ionicons name="pencil" size={16} color="#4F46E5" />
          <Text style={styles.editProfileText}>Edit Profile</Text>
        </TouchableOpacity>
      </View>

      {/* Account Section */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Account</Text>
        {menuItems.map((item, index) => (
          <TouchableOpacity
            key={index}
            style={styles.menuItem}
            onPress={item.onPress}
          >
            <View style={[styles.menuIcon, { backgroundColor: `${item.color}15` }]}>
              <Ionicons name={item.icon as any} size={20} color={item.color} />
            </View>
            <Text style={styles.menuLabel}>{item.label}</Text>
            <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
          </TouchableOpacity>
        ))}
      </View>

      {/* Orders Section */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Orders & Activity</Text>
        {orderMenuItems.map((item, index) => (
          <TouchableOpacity
            key={index}
            style={styles.menuItem}
            onPress={item.onPress}
          >
            <View style={[styles.menuIcon, { backgroundColor: `${item.color}15` }]}>
              <Ionicons name={item.icon as any} size={20} color={item.color} />
            </View>
            <Text style={styles.menuLabel}>{item.label}</Text>
            <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
          </TouchableOpacity>
        ))}
      </View>

      {/* App Info */}
      <View style={styles.appInfo}>
        <Text style={styles.appName}>Saga Toko</Text>
        <Text style={styles.appVersion}>Version 1.0.0</Text>
        <Text style={styles.appCopyright}>© 2026 Saga Toko. All rights reserved.</Text>
      </View>

      {/* Logout Button */}
      <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
        <Ionicons name="log-out-outline" size={20} color="#EF4444" />
        <Text style={styles.logoutText}>Logout</Text>
      </TouchableOpacity>

      <View style={{ height: 40 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  notLoggedInContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  guestHeader: {
    marginBottom: 24,
  },
  guestTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 8,
  },
  guestSubtitle: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: 32,
  },
  guestButtons: {
    flexDirection: 'row',
    gap: 12,
  },
  loginButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#4F46E5',
    paddingHorizontal: 24,
    paddingVertical: 14,
    borderRadius: 12,
    gap: 8,
  },
  loginButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  registerButton: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    paddingHorizontal: 24,
    paddingVertical: 14,
    borderRadius: 12,
  },
  registerButtonText: {
    color: '#111827',
    fontSize: 16,
    fontWeight: '600',
  },
  profileHeader: {
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    padding: 24,
    paddingBottom: 16,
  },
  avatarContainer: {
    position: 'relative',
    marginBottom: 16,
  },
  avatar: {
    width: 100,
    height: 100,
    borderRadius: 50,
  },
  avatarPlaceholder: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: '#4F46E5',
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 40,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  editAvatarBtn: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    backgroundColor: '#4F46E5',
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: '#FFFFFF',
  },
  userName: {
    fontSize: 20,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 4,
  },
  userEmail: {
    fontSize: 14,
    color: '#6B7280',
    marginBottom: 16,
  },
  editProfileBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 16,
    paddingVertical: 8,
    backgroundColor: '#EEF2FF',
    borderRadius: 20,
  },
  editProfileText: {
    color: '#4F46E5',
    fontSize: 14,
    fontWeight: '500',
  },
  section: {
    marginTop: 16,
    backgroundColor: '#FFFFFF',
    paddingBottom: 8,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6B7280',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    paddingHorizontal: 16,
    paddingTop: 16,
    marginBottom: 8,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  menuIcon: {
    width: 40,
    height: 40,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  menuLabel: {
    flex: 1,
    fontSize: 15,
    color: '#111827',
  },
  appInfo: {
    alignItems: 'center',
    padding: 24,
  },
  appName: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
  },
  appVersion: {
    fontSize: 14,
    color: '#9CA3AF',
    marginTop: 4,
  },
  appCopyright: {
    fontSize: 12,
    color: '#9CA3AF',
    marginTop: 4,
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginHorizontal: 16,
    paddingVertical: 16,
    gap: 8,
  },
  logoutText: {
    color: '#EF4444',
    fontSize: 16,
    fontWeight: '600',
  },
});
