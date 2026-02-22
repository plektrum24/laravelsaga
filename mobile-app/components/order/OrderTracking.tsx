import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export type OrderStatus = 
  | 'pending'
  | 'confirmed'
  | 'processing'
  | 'shipped'
  | 'out_for_delivery'
  | 'delivered'
  | 'cancelled';

interface TrackingEvent {
  status: OrderStatus;
  title: string;
  description: string;
  timestamp: string;
  completed: boolean;
  current?: boolean;
}

interface OrderTrackingProps {
  orderNumber: string;
  currentStatus: OrderStatus;
  estimatedDelivery: string;
  events?: TrackingEvent[];
  deliveryAddress?: string;
  courierInfo?: {
    name: string;
    phone: string;
    vehicle?: string;
  };
  onContactCourier?: () => void;
  onContactSupport?: () => void;
}

export default function OrderTracking({
  orderNumber,
  currentStatus,
  estimatedDelivery,
  events = [],
  deliveryAddress,
  courierInfo,
  onContactCourier,
  onContactSupport,
}: OrderTrackingProps) {
  const defaultEvents: TrackingEvent[] = [
    {
      status: 'pending',
      title: 'Order Placed',
      description: 'Your order has been received',
      timestamp: new Date().toISOString(),
      completed: true,
    },
    {
      status: 'confirmed',
      title: 'Order Confirmed',
      description: 'Seller has confirmed your order',
      timestamp: new Date().toISOString(),
      completed: currentStatus !== 'pending',
      current: currentStatus === 'confirmed',
    },
    {
      status: 'processing',
      title: 'Processing',
      description: 'Order is being packed',
      timestamp: new Date().toISOString(),
      completed: ['processing', 'shipped', 'out_for_delivery', 'delivered'].includes(currentStatus),
      current: currentStatus === 'processing',
    },
    {
      status: 'shipped',
      title: 'Shipped',
      description: 'Order has been shipped',
      timestamp: new Date().toISOString(),
      completed: ['shipped', 'out_for_delivery', 'delivered'].includes(currentStatus),
      current: currentStatus === 'shipped',
    },
    {
      status: 'out_for_delivery',
      title: 'Out for Delivery',
      description: 'Out for delivery',
      timestamp: new Date().toISOString(),
      completed: ['out_for_delivery', 'delivered'].includes(currentStatus),
      current: currentStatus === 'out_for_delivery',
    },
    {
      status: 'delivered',
      title: 'Delivered',
      description: 'Order has been delivered',
      timestamp: new Date().toISOString(),
      completed: currentStatus === 'delivered',
      current: currentStatus === 'delivered',
    },
  ];

  const displayEvents = events.length > 0 ? events : defaultEvents;

  const getStatusIcon = (status: OrderStatus, completed: boolean, current: boolean) => {
    if (current) {
      return 'radio-button-on';
    }
    if (completed) {
      return 'checkmark-circle';
    }
    return 'radio-button-off';
  };

  const getStatusColor = (completed: boolean, current: boolean) => {
    if (current) return '#4F46E5';
    if (completed) return '#10B981';
    return '#D1D5DB';
  };

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Status Header */}
      <View style={styles.statusHeader}>
        <Text style={styles.orderNumber}>Order #{orderNumber}</Text>
        <View style={styles.statusBadge}>
          <Text style={styles.statusText}>
            {currentStatus.replace(/_/g, ' ').toUpperCase()}
          </Text>
        </View>
      </View>

      {/* Estimated Delivery */}
      <View style={styles.deliveryCard}>
        <View style={styles.deliveryHeader}>
          <Ionicons name="time" size={24} color="#10B981" />
          <Text style={styles.deliveryTitle}>Estimated Delivery</Text>
        </View>
        <Text style={styles.deliveryTime}>{estimatedDelivery}</Text>
        <Text style={styles.deliveryNote}>
          We'll notify you when your order is on the way
        </Text>
      </View>

      {/* Courier Info */}
      {courierInfo && (
        <View style={styles.courierCard}>
          <View style={styles.courierHeader}>
            <Ionicons name="bicycle" size={20} color="#4F46E5" />
            <Text style={styles.courierTitle}>Courier Information</Text>
          </View>
          <View style={styles.courierInfo}>
            <View style={styles.courierRow}>
              <Ionicons name="person" size={16} color="#6B7280" />
              <Text style={styles.courierName}>{courierInfo.name}</Text>
            </View>
            {courierInfo.vehicle && (
              <View style={styles.courierRow}>
                <Ionicons name="bike" size={16} color="#6B7280" />
                <Text style={styles.courierVehicle}>{courierInfo.vehicle}</Text>
              </View>
            )}
            <View style={styles.courierRow}>
              <Ionicons name="call" size={16} color="#6B7280" />
              <TouchableOpacity onPress={onContactCourier}>
                <Text style={styles.courierPhone}>{courierInfo.phone}</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      )}

      {/* Tracking Timeline */}
      <View style={styles.timelineContainer}>
        <Text style={styles.timelineTitle}>Order Timeline</Text>
        <View style={styles.timeline}>
          {displayEvents.map((event, index) => {
            const isLast = index === displayEvents.length - 1;
            const iconColor = getStatusColor(event.completed, event.current || false);

            return (
              <View key={event.status} style={styles.timelineItem}>
                <View style={styles.timelineLine}>
                  {!isLast && (
                    <View
                      style={[
                        styles.line,
                        { backgroundColor: iconColor },
                      ]}
                    />
                  )}
                </View>
                <View style={styles.timelineIcon}>
                  <Ionicons
                    name={getStatusIcon(event.status, event.completed, event.current || false) as any}
                    size={24}
                    color={iconColor}
                  />
                </View>
                <View style={styles.timelineContent}>
                  <Text
                    style={[
                      styles.eventTitle,
                      event.completed && styles.eventTitleCompleted,
                      event.current && styles.eventTitleCurrent,
                    ]}
                  >
                    {event.title}
                  </Text>
                  <Text style={styles.eventDescription}>{event.description}</Text>
                  <Text style={styles.eventTime}>
                    {new Date(event.timestamp).toLocaleString('id-ID')}
                  </Text>
                </View>
              </View>
            );
          })}
        </View>
      </View>

      {/* Delivery Address */}
      {deliveryAddress && (
        <View style={styles.addressContainer}>
          <View style={styles.addressHeader}>
            <Ionicons name="location" size={20} color="#4F46E5" />
            <Text style={styles.addressTitle}>Delivery Address</Text>
          </View>
          <Text style={styles.addressText}>{deliveryAddress}</Text>
        </View>
      )}

      {/* Action Buttons */}
      <View style={styles.actionContainer}>
        <TouchableOpacity
          style={styles.supportButton}
          onPress={onContactSupport}
        >
          <Ionicons name="headset" size={20} color="#4F46E5" />
          <Text style={styles.supportButtonText}>Contact Support</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.helpButton}>
          <Ionicons name="help-circle-outline" size={20} color="#6B7280" />
          <Text style={styles.helpButtonText}>Help Center</Text>
        </TouchableOpacity>
      </View>

      <View style={{ height: 40 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  statusHeader: {
    backgroundColor: '#FFFFFF',
    padding: 16,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  orderNumber: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  statusBadge: {
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#4F46E5',
    textTransform: 'uppercase',
  },
  deliveryCard: {
    backgroundColor: '#ECFDF5',
    margin: 16,
    padding: 16,
    borderRadius: 12,
  },
  deliveryHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  deliveryTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  deliveryTime: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#10B981',
    marginBottom: 4,
  },
  deliveryNote: {
    fontSize: 12,
    color: '#6B7280',
  },
  courierCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 12,
  },
  courierHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  courierTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  courierInfo: {
    gap: 8,
  },
  courierRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  courierName: {
    fontSize: 13,
    color: '#111827',
    fontWeight: '500',
  },
  courierVehicle: {
    fontSize: 13,
    color: '#6B7280',
  },
  courierPhone: {
    fontSize: 13,
    color: '#4F46E5',
    fontWeight: '500',
  },
  timelineContainer: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 12,
  },
  timelineTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 16,
  },
  timeline: {
    paddingLeft: 8,
  },
  timelineItem: {
    flexDirection: 'row',
    marginBottom: 16,
  },
  timelineLine: {
    alignItems: 'center',
    marginRight: 12,
  },
  line: {
    width: 2,
    flex: 1,
    backgroundColor: '#D1D5DB',
  },
  timelineIcon: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#FFFFFF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
    borderWidth: 2,
    borderColor: '#E5E7EB',
  },
  timelineContent: {
    flex: 1,
    paddingTop: 4,
  },
  eventTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#9CA3AF',
    marginBottom: 4,
  },
  eventTitleCompleted: {
    color: '#10B981',
  },
  eventTitleCurrent: {
    color: '#4F46E5',
  },
  eventDescription: {
    fontSize: 13,
    color: '#6B7280',
    marginBottom: 4,
  },
  eventTime: {
    fontSize: 11,
    color: '#9CA3AF',
  },
  addressContainer: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 12,
  },
  addressHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  addressTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  addressText: {
    fontSize: 13,
    color: '#6B7280',
    lineHeight: 20,
  },
  actionContainer: {
    flexDirection: 'row',
    gap: 12,
    marginHorizontal: 16,
    marginBottom: 16,
  },
  supportButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EEF2FF',
    paddingVertical: 14,
    borderRadius: 12,
    gap: 8,
  },
  supportButtonText: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '600',
  },
  helpButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#F9FAFB',
    paddingVertical: 14,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    gap: 8,
  },
  helpButtonText: {
    fontSize: 14,
    color: '#6B7280',
    fontWeight: '600',
  },
});
