import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Linking,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import MapView, { Marker, PROVIDER_GOOGLE } from 'react-native-maps';
import * as Location from 'expo-location';

export interface Store {
  id: string;
  name: string;
  address: string;
  city: string;
  phone: string;
  latitude: number;
  longitude: number;
  opening_hours: {
    monday: string;
    tuesday: string;
    wednesday: string;
    thursday: string;
    friday: string;
    saturday: string;
    sunday: string;
  };
  services: string[];
  is_open?: boolean;
}

interface StoreLocatorProps {
  stores?: Store[];
  onStoreSelect?: (store: Store) => void;
  title?: string;
}

export default function StoreLocator({
  stores = [],
  onStoreSelect,
  title = 'Our Stores',
}: StoreLocatorProps) {
  const [location, setLocation] = useState<Location.LocationObject | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedStore, setSelectedStore] = useState<Store | null>(null);
  const [viewMode, setViewMode] = useState<'map' | 'list'>('map');

  useEffect(() => {
    getCurrentLocation();
  }, []);

  const getCurrentLocation = async () => {
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        setLoading(false);
        return;
      }

      const currentLocation = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.Balanced,
      });
      setLocation(currentLocation);
    } catch (error) {
      console.error('Error getting location:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleStoreSelect = (store: Store) => {
    setSelectedStore(store);
    onStoreSelect?.(store);
  };

  const handleGetDirections = (store: Store) => {
    const url = `https://www.google.com/maps/dir/?api=1&destination=${store.latitude},${store.longitude}`;
    Linking.openURL(url);
  };

  const handleCallStore = (store: Store) => {
    Linking.openURL(`tel:${store.phone}`);
  };

  const defaultStores: Store[] = stores.length > 0 ? stores : [
    {
      id: '1',
      name: 'SAGA POS Flagship Store',
      address: 'Jl. Sudirman No. 123',
      city: 'Jakarta',
      phone: '+62-21-1234-5678',
      latitude: -6.2088,
      longitude: 106.8456,
      opening_hours: {
        monday: '09:00 - 21:00',
        tuesday: '09:00 - 21:00',
        wednesday: '09:00 - 21:00',
        thursday: '09:00 - 21:00',
        friday: '09:00 - 21:00',
        saturday: '09:00 - 22:00',
        sunday: '10:00 - 20:00',
      },
      services: ['Parking', 'WiFi', 'ATM', 'Restaurant'],
      is_open: true,
    },
  ];

  const displayStores = defaultStores;

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#4F46E5" />
        <Text style={styles.loadingText}>Finding your location...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.title}>{title}</Text>
        <View style={styles.viewToggle}>
          <TouchableOpacity
            style={[styles.toggleButton, viewMode === 'map' && styles.toggleButtonActive]}
            onPress={() => setViewMode('map')}
          >
            <Ionicons
              name="map"
              size={20}
              color={viewMode === 'map' ? '#FFFFFF' : '#6B7280'}
            />
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.toggleButton, viewMode === 'list' && styles.toggleButtonActive]}
            onPress={() => setViewMode('list')}
          >
            <Ionicons
              name="list"
              size={20}
              color={viewMode === 'list' ? '#FFFFFF' : '#6B7280'}
            />
          </TouchableOpacity>
        </View>
      </View>

      {viewMode === 'map' ? (
        <>
          {/* Map */}
          <View style={styles.mapContainer}>
            <MapView
              provider={PROVIDER_GOOGLE}
              style={styles.map}
              initialRegion={{
                latitude: location?.coords.latitude || -6.2088,
                longitude: location?.coords.longitude || 106.8456,
                latitudeDelta: 0.1,
                longitudeDelta: 0.1,
              }}
              showsUserLocation
              showsMyLocationButton
            >
              {displayStores.map((store) => (
                <Marker
                  key={store.id}
                  coordinate={{
                    latitude: store.latitude,
                    longitude: store.longitude,
                  }}
                  title={store.name}
                  description={store.city}
                  onPress={() => handleStoreSelect(store)}
                >
                  <View style={styles.markerContainer}>
                    <Ionicons name="location" size={32} color="#4F46E5" />
                  </View>
                </Marker>
              ))}
            </MapView>
          </View>

          {/* Selected Store Info */}
          {selectedStore && (
            <View style={styles.storeInfoCard}>
              <View style={styles.storeInfoHeader}>
                <View>
                  <Text style={styles.storeInfoName}>{selectedStore.name}</Text>
                  <Text style={styles.storeInfoAddress}>{selectedStore.address}</Text>
                </View>
                <TouchableOpacity
                  style={styles.closeInfoButton}
                  onPress={() => setSelectedStore(null)}
                >
                  <Ionicons name="close" size={20} color="#6B7280" />
                </TouchableOpacity>
              </View>

              <View style={styles.storeInfoActions}>
                <TouchableOpacity
                  style={styles.actionButton}
                  onPress={() => handleGetDirections(selectedStore)}
                >
                  <Ionicons name="navigate" size={20} color="#4F46E5" />
                  <Text style={styles.actionButtonText}>Directions</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={styles.actionButton}
                  onPress={() => handleCallStore(selectedStore)}
                >
                  <Ionicons name="call" size={20} color="#4F46E5" />
                  <Text style={styles.actionButtonText}>Call</Text>
                </TouchableOpacity>
              </View>
            </View>
          )}
        </>
      ) : (
        /* List View */
        <ScrollView style={styles.listContainer}>
          {displayStores.map((store) => (
            <TouchableOpacity
              key={store.id}
              style={styles.storeCard}
              onPress={() => handleStoreSelect(store)}
            >
              <View style={styles.storeCardHeader}>
                <View style={styles.storeCardNameContainer}>
                  <Text style={styles.storeCardName}>{store.name}</Text>
                  {store.is_open !== undefined && (
                    <View
                      style={[
                        styles.statusBadge,
                        store.is_open ? styles.statusOpen : styles.statusClosed,
                      ]}
                    >
                      <Text
                        style={[
                          styles.statusText,
                          store.is_open ? styles.statusTextOpen : styles.statusTextClosed,
                        ]}
                      >
                        {store.is_open ? 'Open' : 'Closed'}
                      </Text>
                    </View>
                  )}
                </View>
                <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
              </View>

              <View style={styles.storeCardInfo}>
                <Ionicons name="location-outline" size={16} color="#6B7280" />
                <Text style={styles.storeCardAddress}>
                  {store.address}, {store.city}
                </Text>
              </View>

              <View style={styles.storeCardInfo}>
                <Ionicons name="call-outline" size={16} color="#6B7280" />
                <Text style={styles.storeCardPhone}>{store.phone}</Text>
              </View>

              <View style={styles.storeCardServices}>
                {store.services.slice(0, 3).map((service, index) => (
                  <View key={index} style={styles.serviceBadge}>
                    <Text style={styles.serviceText}>{service}</Text>
                  </View>
                ))}
              </View>

              <View style={styles.storeCardActions}>
                <TouchableOpacity
                  style={styles.cardActionButton}
                  onPress={() => handleGetDirections(store)}
                >
                  <Ionicons name="navigate" size={18} color="#4F46E5" />
                  <Text style={styles.cardActionButtonText}>Directions</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={styles.cardActionButton}
                  onPress={() => handleCallStore(store)}
                >
                  <Ionicons name="call" size={18} color="#4F46E5" />
                  <Text style={styles.cardActionButtonText}>Call</Text>
                </TouchableOpacity>
              </View>
            </TouchableOpacity>
          ))}
        </ScrollView>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 14,
    color: '#9CA3AF',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    paddingTop: 50,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#111827',
  },
  viewToggle: {
    flexDirection: 'row',
    gap: 8,
  },
  toggleButton: {
    width: 40,
    height: 40,
    borderRadius: 10,
    backgroundColor: '#F3F4F6',
    justifyContent: 'center',
    alignItems: 'center',
  },
  toggleButtonActive: {
    backgroundColor: '#4F46E5',
  },
  mapContainer: {
    flex: 1,
  },
  map: {
    width: '100%',
    height: '100%',
  },
  markerContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  storeInfoCard: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 8,
  },
  storeInfoHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  storeInfoName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#111827',
    marginBottom: 4,
  },
  storeInfoAddress: {
    fontSize: 14,
    color: '#6B7280',
  },
  closeInfoButton: {
    padding: 4,
  },
  storeInfoActions: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 8,
  },
  actionButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EEF2FF',
    paddingVertical: 12,
    borderRadius: 10,
    gap: 8,
  },
  actionButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4F46E5',
  },
  listContainer: {
    flex: 1,
  },
  storeCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginVertical: 8,
    padding: 16,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  storeCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  storeCardNameContainer: {
    flex: 1,
  },
  storeCardName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#111827',
    marginBottom: 4,
  },
  statusBadge: {
    alignSelf: 'flex-start',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  statusOpen: {
    backgroundColor: '#ECFDF5',
  },
  statusClosed: {
    backgroundColor: '#FEF2F2',
  },
  statusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  statusTextOpen: {
    color: '#10B981',
  },
  statusTextClosed: {
    color: '#EF4444',
  },
  storeCardInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  storeCardAddress: {
    flex: 1,
    fontSize: 13,
    color: '#6B7280',
  },
  storeCardPhone: {
    fontSize: 13,
    color: '#6B7280',
  },
  storeCardServices: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
    marginBottom: 12,
  },
  serviceBadge: {
    backgroundColor: '#F3F4F6',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  serviceText: {
    fontSize: 11,
    color: '#6B7280',
  },
  storeCardActions: {
    flexDirection: 'row',
    gap: 8,
  },
  cardActionButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EEF2FF',
    paddingVertical: 10,
    borderRadius: 8,
    gap: 6,
  },
  cardActionButtonText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#4F46E5',
  },
});
