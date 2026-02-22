import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Modal,
  TextInput,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export interface Address {
  id: string;
  label: string;
  name: string;
  phone: string;
  address: string;
  city: string;
  postal_code: string;
  is_default?: boolean;
}

interface AddressSelectorProps {
  addresses?: Address[];
  selectedAddressId?: string;
  onSelectAddress?: (address: Address) => void;
  onAddAddress?: (address: Address) => void;
  onEditAddress?: (address: Address) => void;
}

export default function AddressSelector({
  addresses = [],
  selectedAddressId,
  onSelectAddress,
  onAddAddress,
  onEditAddress,
}: AddressSelectorProps) {
  const [showAddModal, setShowAddModal] = useState(false);
  const [editingAddress, setEditingAddress] = useState<Address | null>(null);

  const handleSelect = (address: Address) => {
    onSelectAddress?.(address);
  };

  const handleAdd = () => {
    setEditingAddress(null);
    setShowAddModal(true);
  };

  const handleEdit = (address: Address) => {
    setEditingAddress(address);
    setShowAddModal(true);
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Delivery Address</Text>
        <TouchableOpacity onPress={handleAdd}>
          <Text style={styles.addText}>+ Add New</Text>
        </TouchableOpacity>
      </View>

      {addresses.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Ionicons name="location-outline" size={48} color="#9CA3AF" />
          <Text style={styles.emptyText}>No addresses yet</Text>
          <TouchableOpacity style={styles.addAddressBtn} onPress={handleAdd}>
            <Ionicons name="add" size={20} color="#4F46E5" />
            <Text style={styles.addAddressBtnText}>Add your first address</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <ScrollView showsVerticalScrollIndicator={false}>
          {addresses.map((address) => (
            <TouchableOpacity
              key={address.id}
              style={[
                styles.addressCard,
                selectedAddressId === address.id && styles.addressCardSelected,
              ]}
              onPress={() => handleSelect(address)}
              activeOpacity={0.7}
            >
              <View style={styles.addressHeader}>
                <View style={styles.addressLabel}>
                  <Text style={styles.labelText}>{address.label}</Text>
                  {address.is_default && (
                    <View style={styles.defaultBadge}>
                      <Text style={styles.defaultText}>Default</Text>
                    </View>
                  )}
                </View>
                <TouchableOpacity onPress={() => handleEdit(address)}>
                  <Ionicons name="create-outline" size={20} color="#6B7280" />
                </TouchableOpacity>
              </View>

              <Text style={styles.addressName}>{address.name}</Text>
              <Text style={styles.addressPhone}>{address.phone}</Text>
              <Text style={styles.addressFull} numberOfLines={2}>
                {address.address}, {address.city} {address.postal_code}
              </Text>

              {selectedAddressId === address.id && (
                <View style={styles.selectedIndicator}>
                  <Ionicons name="checkmark-circle" size={20} color="#4F46E5" />
                </View>
              )}
            </TouchableOpacity>
          ))}
        </ScrollView>
      )}

      {/* Add/Edit Address Modal */}
      <Modal
        visible={showAddModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowAddModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>
                {editingAddress ? 'Edit Address' : 'Add New Address'}
              </Text>
              <TouchableOpacity onPress={() => setShowAddModal(false)}>
                <Ionicons name="close" size={24} color="#111827" />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalContent}>
              <TextInput
                style={styles.input}
                placeholder="Label (e.g., Home, Office)"
                placeholderTextColor="#9CA3AF"
                defaultValue={editingAddress?.label}
              />
              <TextInput
                style={styles.input}
                placeholder="Full Name"
                placeholderTextColor="#9CA3AF"
                defaultValue={editingAddress?.name}
              />
              <TextInput
                style={styles.input}
                placeholder="Phone Number"
                placeholderTextColor="#9CA3AF"
                keyboardType="phone-pad"
                defaultValue={editingAddress?.phone}
              />
              <TextInput
                style={[styles.input, styles.textArea]}
                placeholder="Street Address"
                placeholderTextColor="#9CA3AF"
                multiline
                numberOfLines={3}
                defaultValue={editingAddress?.address}
              />
              <TextInput
                style={styles.input}
                placeholder="City"
                placeholderTextColor="#9CA3AF"
                defaultValue={editingAddress?.city}
              />
              <TextInput
                style={styles.input}
                placeholder="Postal Code"
                placeholderTextColor="#9CA3AF"
                keyboardType="number-pad"
                defaultValue={editingAddress?.postal_code}
              />
            </ScrollView>

            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={styles.cancelBtn}
                onPress={() => setShowAddModal(false)}
              >
                <Text style={styles.cancelBtnText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.saveBtn}
                onPress={() => {
                  // Save logic would go here
                  setShowAddModal(false);
                }}
              >
                <Text style={styles.saveBtnText}>Save Address</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  addText: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
  },
  emptyContainer: {
    alignItems: 'center',
    padding: 24,
  },
  emptyText: {
    fontSize: 14,
    color: '#9CA3AF',
    marginTop: 8,
    marginBottom: 16,
  },
  addAddressBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  addAddressBtnText: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
  },
  addressCard: {
    backgroundColor: '#F9FAFB',
    borderRadius: 12,
    padding: 12,
    marginBottom: 8,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    position: 'relative',
  },
  addressCardSelected: {
    backgroundColor: '#EEF2FF',
    borderColor: '#4F46E5',
    borderWidth: 2,
  },
  addressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  addressLabel: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  labelText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#111827',
  },
  defaultBadge: {
    backgroundColor: '#10B981',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
  },
  defaultText: {
    fontSize: 10,
    color: '#FFFFFF',
    fontWeight: '600',
  },
  addressName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 4,
  },
  addressPhone: {
    fontSize: 13,
    color: '#6B7280',
    marginBottom: 4,
  },
  addressFull: {
    fontSize: 13,
    color: '#6B7280',
    lineHeight: 18,
  },
  selectedIndicator: {
    position: 'absolute',
    top: 12,
    right: 12,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContainer: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '80%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
  },
  modalContent: {
    padding: 20,
  },
  input: {
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    padding: 12,
    fontSize: 14,
    color: '#111827',
    marginBottom: 12,
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  modalFooter: {
    flexDirection: 'row',
    padding: 20,
    gap: 12,
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
  },
  cancelBtn: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 12,
    backgroundColor: '#F3F4F6',
    alignItems: 'center',
  },
  cancelBtnText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#6B7280',
  },
  saveBtn: {
    flex: 2,
    paddingVertical: 14,
    borderRadius: 12,
    backgroundColor: '#4F46E5',
    alignItems: 'center',
  },
  saveBtnText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
  },
});
