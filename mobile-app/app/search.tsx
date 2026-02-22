import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
} from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useProductStore } from '../../stores/product.store';
import { Product } from '../../types/api.types';

export default function SearchScreen() {
  const [searchQuery, setSearchQuery] = useState('');
  const { products, isLoading, searchProducts, clearError } = useProductStore();
  const [isSearching, setIsSearching] = useState(false);

  const handleSearch = async () => {
    if (!searchQuery.trim()) {
      return;
    }
    setIsSearching(true);
    try {
      await searchProducts(searchQuery, { limit: 50 });
    } catch (error) {
      console.error('Search error:', error);
    } finally {
      setIsSearching(false);
    }
  };

  const handleClearSearch = () => {
    setSearchQuery('');
    clearError();
  };

  const renderProductCard = ({ item }: { item: Product }) => (
    <TouchableOpacity
      style={styles.productCard}
      onPress={() => router.push(`/product/${item.id}`)}
    >
      <View style={styles.productImageContainer}>
        {item.images?.[0]?.url ? (
          <Image
            source={{ uri: item.images[0].url }}
            style={styles.productImage}
            resizeMode="cover"
          />
        ) : (
          <View style={styles.productImagePlaceholder}>
            <Ionicons name="image-outline" size={40} color="#9CA3AF" />
          </View>
        )}
      </View>
      <View style={styles.productInfo}>
        <Text style={styles.productName} numberOfLines={2}>
          {item.name}
        </Text>
        <Text style={styles.productPrice}>
          Rp {item.price.toLocaleString('id-ID')}
        </Text>
        <View style={styles.productFooter}>
          <Text style={item.stock > 0 ? styles.inStock : styles.outOfStock}>
            {item.stock > 0 ? `In Stock (${item.stock})` : 'Out of Stock'}
          </Text>
        </View>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      {/* Search Header */}
      <View style={styles.searchHeader}>
        <View style={styles.searchInputContainer}>
          <Ionicons name="search" size={20} color="#9CA3AF" />
          <TextInput
            style={styles.searchInput}
            placeholder="Search products..."
            placeholderTextColor="#9CA3AF"
            value={searchQuery}
            onChangeText={setSearchQuery}
            onSubmitEditing={handleSearch}
            autoFocus
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={handleClearSearch} style={styles.clearBtn}>
              <Ionicons name="close-circle" size={20} color="#9CA3AF" />
            </TouchableOpacity>
          )}
        </View>
        <TouchableOpacity
          style={[styles.searchBtn, !searchQuery.trim() && styles.searchBtnDisabled]}
          onPress={handleSearch}
          disabled={!searchQuery.trim()}
        >
          <Text style={[styles.searchBtnText, !searchQuery.trim() && styles.searchBtnTextDisabled]}>
            Search
          </Text>
        </TouchableOpacity>
      </View>

      {/* Recent Searches (placeholder) */}
      {!searchQuery && products.length === 0 && (
        <View style={styles.recentContainer}>
          <View style={styles.recentHeader}>
            <Ionicons name="time-outline" size={20} color="#9CA3AF" />
            <Text style={styles.recentTitle}>Recent Searches</Text>
          </View>
          <View style={styles.recentTags}>
            <TouchableOpacity style={styles.recentTag}>
              <Text style={styles.recentTagText}>Product example</Text>
            </TouchableOpacity>
          </View>
          <Text style={styles.emptyHint}>
            Type a product name to search
          </Text>
        </View>
      )}

      {/* Search Results */}
      {isSearching ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#4F46E5" />
          <Text style={styles.loadingText}>Searching...</Text>
        </View>
      ) : products.length > 0 ? (
        <FlatList
          data={products}
          renderItem={renderProductCard}
          keyExtractor={(item) => item.id}
          contentContainerStyle={styles.resultsList}
          ListHeaderComponent={
            <Text style={styles.resultsCount}>
              {products.length} result{products.length !== 1 ? 's' : ''} found
            </Text>
          }
        />
      ) : searchQuery.length > 0 ? (
        <View style={styles.emptyContainer}>
          <Ionicons name="search-outline" size={64} color="#9CA3AF" />
          <Text style={styles.emptyTitle}>No products found</Text>
          <Text style={styles.emptySubtitle}>
            Try different keywords or check the spelling
          </Text>
        </View>
      ) : null}
    </View>
  );
}

import { Image } from 'react-native';

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  searchHeader: {
    flexDirection: 'row',
    padding: 16,
    gap: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  searchInputContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F3F4F6',
    borderRadius: 12,
    paddingHorizontal: 12,
  },
  searchInput: {
    flex: 1,
    height: 44,
    fontSize: 16,
    color: '#111827',
    marginLeft: 8,
  },
  clearBtn: {
    padding: 4,
  },
  searchBtn: {
    backgroundColor: '#4F46E5',
    paddingHorizontal: 20,
    borderRadius: 12,
    justifyContent: 'center',
  },
  searchBtnDisabled: {
    backgroundColor: '#9CA3AF',
  },
  searchBtnText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  searchBtnTextDisabled: {
    opacity: 0.6,
  },
  recentContainer: {
    padding: 16,
  },
  recentHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  recentTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#6B7280',
  },
  recentTags: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  recentTag: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
  },
  recentTagText: {
    fontSize: 14,
    color: '#6B7280',
  },
  emptyHint: {
    fontSize: 14,
    color: '#9CA3AF',
    marginTop: 16,
    fontStyle: 'italic',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 16,
    color: '#9CA3AF',
    fontSize: 14,
  },
  resultsList: {
    padding: 16,
  },
  resultsCount: {
    fontSize: 14,
    color: '#6B7280',
    marginBottom: 12,
  },
  productCard: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 12,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  productImageContainer: {
    width: 100,
    height: 100,
    borderRadius: 8,
    backgroundColor: '#F3F4F6',
    overflow: 'hidden',
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  productImagePlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  productInfo: {
    flex: 1,
    marginLeft: 12,
    justifyContent: 'space-between',
  },
  productName: {
    fontSize: 15,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 8,
  },
  productPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#4F46E5',
  },
  productFooter: {
    marginTop: 4,
  },
  inStock: {
    fontSize: 12,
    color: '#10B981',
    fontWeight: '500',
  },
  outOfStock: {
    fontSize: 12,
    color: '#EF4444',
    fontWeight: '500',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
    marginTop: 16,
  },
  emptySubtitle: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginTop: 8,
  },
});
