import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useProductStore } from '../../stores/product.store';
import { useCartStore } from '../../stores/cart.store';
import { Product } from '../../types/api.types';
import SearchBar from '../../components/product/SearchBar';
import FilterModal, { FilterOptions } from '../../components/product/FilterModal';
import SortModal, { SortOption } from '../../components/product/SortModal';
import ProductCard from '../../components/product/ProductCard';

export default function ShopScreen() {
  const { category, category_name } = useLocalSearchParams<{ category?: string; category_name?: string }>();
  const { products, categories, isLoading, hasMore, fetchProducts, searchProducts } = useProductStore();
  const { addItem } = useCartStore();
  
  // State
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string | undefined>(category);
  const [sortBy, setSortBy] = useState<SortOption>('default');
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [showFilterModal, setShowFilterModal] = useState(false);
  const [showSortModal, setShowSortModal] = useState(false);
  const [filters, setFilters] = useState<FilterOptions>({});
  const [page, setPage] = useState(1);

  // Load initial data
  useEffect(() => {
    if (category) {
      setSelectedCategory(category);
    }
    loadProducts(1);
  }, []);

  // Reload when category changes
  useEffect(() => {
    if (category !== selectedCategory) {
      setSelectedCategory(category);
      loadProducts(1);
    }
  }, [category]);

  const loadProducts = async (pageNum: number = 1) => {
    const params: any = {
      page: pageNum,
      category_id: selectedCategory,
      sort: sortBy !== 'default' ? sortBy : undefined,
    };

    if (filters.min_price) params.min_price = filters.min_price;
    if (filters.max_price) params.max_price = filters.max_price;
    if (filters.in_stock_only) params.in_stock_only = '1';
    if (filters.featured_only) params.featured_only = '1';
    if (filters.on_sale) params.on_sale = '1';

    await fetchProducts(params);
    setPage(pageNum);
  };

  const handleSearch = async (query: string) => {
    setSearchQuery(query);
    if (query.trim()) {
      await searchProducts(query, {
        category_id: selectedCategory,
        sort: sortBy !== 'default' ? sortBy : undefined,
      });
    } else {
      await loadProducts(1);
    }
  };

  const handleAddToCart = async (product: Product) => {
    try {
      await addItem(product, 1);
      // Could add toast/notification here
    } catch (error) {
      console.error('Failed to add to cart:', error);
    }
  };

  const handleApplyFilters = async (newFilters: FilterOptions) => {
    setFilters(newFilters);
    await loadProducts(1);
  };

  const handleResetFilters = async () => {
    setFilters({});
    await loadProducts(1);
  };

  const handleSortSelect = async (sort: SortOption) => {
    setSortBy(sort);
    await loadProducts(1);
  };

  const handleLoadMore = () => {
    if (hasMore && !isLoading) {
      loadProducts(page + 1);
    }
  };

  const renderProductCard = ({ item }: { item: Product }) => (
    <ProductCard
      product={item}
      viewMode={viewMode}
      onPress={(product) => router.push(`/product/${product.id}`)}
      onAddToCart={handleAddToCart}
    />
  );

  const activeFilterCount = Object.keys(filters).length;

  return (
    <View style={styles.container}>
      {/* Search Bar */}
      <View style={styles.searchContainer}>
        <SearchBar
          initialValue={searchQuery}
          onSearch={handleSearch}
          onClear={() => {
            setSearchQuery('');
            loadProducts(1);
          }}
        />
        
        {/* View Mode Toggle */}
        <View style={styles.viewModeContainer}>
          <TouchableOpacity
            style={[styles.viewModeBtn, viewMode === 'grid' && styles.viewModeBtnActive]}
            onPress={() => setViewMode('grid')}
          >
            <Ionicons
              name={viewMode === 'grid' ? 'grid' : 'grid-outline'}
              size={20}
              color={viewMode === 'grid' ? '#4F46E5' : '#6B7280'}
            />
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.viewModeBtn, viewMode === 'list' && styles.viewModeBtnActive]}
            onPress={() => setViewMode('list')}
          >
            <Ionicons
              name={viewMode === 'list' ? 'list' : 'list-outline'}
              size={20}
              color={viewMode === 'list' ? '#4F46E5' : '#6B7280'}
            />
          </TouchableOpacity>
        </View>

        {/* Filter Button */}
        <TouchableOpacity
          style={styles.filterBtn}
          onPress={() => setShowFilterModal(true)}
        >
          <Ionicons name="filter" size={20} color="#6B7280" />
          {activeFilterCount > 0 && (
            <View style={styles.filterBadge}>
              <Text style={styles.filterBadgeText}>{activeFilterCount}</Text>
            </View>
          )}
        </TouchableOpacity>
      </View>

      {/* Category Pills */}
      <FlatList
        data={categories}
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.categoriesList}
        renderItem={({ item }) => (
          <TouchableOpacity
            style={[
              styles.categoryPill,
              selectedCategory === String(item.id) && styles.categoryPillActive,
            ]}
            onPress={() =>
              setSelectedCategory(selectedCategory === String(item.id) ? undefined : String(item.id))
            }
          >
            <Text
              style={[
                styles.categoryPillText,
                selectedCategory === String(item.id) && styles.categoryPillTextActive,
              ]}
            >
              {item.name}
            </Text>
          </TouchableOpacity>
        )}
      />

      {/* Sort Options */}
      <View style={styles.sortContainer}>
        <Text style={styles.sortLabel}>Sort:</Text>
        <TouchableOpacity
          style={styles.sortButton}
          onPress={() => setShowSortModal(true)}
        >
          <Text style={styles.sortButtonText}>
            {sortBy === 'default' && 'Recommended'}
            {sortBy === 'newest' && 'Newest'}
            {sortBy === 'price_asc' && 'Price ↑'}
            {sortBy === 'price_desc' && 'Price ↓'}
            {sortBy === 'name_asc' && 'Name A-Z'}
            {sortBy === 'name_desc' && 'Name Z-A'}
            {sortBy === 'best_seller' && 'Best Selling'}
          </Text>
          <Ionicons name="chevron-down" size={16} color="#4F46E5" />
        </TouchableOpacity>
      </View>

      {/* Products Grid/List */}
      {isLoading && products.length === 0 ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#4F46E5" />
          <Text style={styles.loadingText}>Loading products...</Text>
        </View>
      ) : (
        <FlatList
          data={products}
          renderItem={renderProductCard}
          keyExtractor={(item) => item.id.toString()}
          numColumns={viewMode === 'grid' ? 2 : 1}
          contentContainerStyle={
            viewMode === 'grid' ? styles.productsGrid : styles.productsList
          }
          columnWrapperStyle={viewMode === 'grid' ? styles.productsRow : undefined}
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.5}
          refreshControl={
            <RefreshControl
              refreshing={isLoading}
              onRefresh={() => loadProducts(1)}
              tintColor="#4F46E5"
            />
          }
          ListEmptyComponent={
            <View style={styles.emptyState}>
              <Ionicons name="store-outline" size={48} color="#9CA3AF" />
              <Text style={styles.emptyText}>No products found</Text>
              {searchQuery.length > 0 && (
                <Text style={styles.emptySubtext}>Try a different search term</Text>
              )}
            </View>
          }
          ListFooterComponent={
            isLoading && products.length > 0 ? (
              <View style={styles.loadingMore}>
                <ActivityIndicator size="small" color="#4F46E5" />
              </View>
            ) : null
          }
        />
      )}

      {/* Filter Modal */}
      <FilterModal
        visible={showFilterModal}
        filters={filters}
        categories={categories}
        onApply={handleApplyFilters}
        onClose={() => setShowFilterModal(false)}
        onReset={handleResetFilters}
      />

      {/* Sort Modal */}
      <SortModal
        visible={showSortModal}
        currentSort={sortBy}
        onSelect={handleSortSelect}
        onClose={() => setShowSortModal(false)}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  searchContainer: {
    flexDirection: 'row',
    padding: 16,
    gap: 12,
    alignItems: 'center',
  },
  viewModeContainer: {
    flexDirection: 'row',
    gap: 8,
  },
  viewModeBtn: {
    width: 40,
    height: 44,
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  viewModeBtnActive: {
    backgroundColor: '#EEF2FF',
    borderColor: '#4F46E5',
  },
  filterBtn: {
    width: 44,
    height: 44,
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  filterBadge: {
    position: 'absolute',
    top: -4,
    right: -4,
    backgroundColor: '#EF4444',
    width: 18,
    height: 18,
    borderRadius: 9,
    justifyContent: 'center',
    alignItems: 'center',
  },
  filterBadgeText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: 'bold',
  },
  categoriesList: {
    paddingHorizontal: 16,
    paddingVertical: 8,
  },
  categoryPill: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    marginRight: 8,
  },
  categoryPillActive: {
    backgroundColor: '#4F46E5',
    borderColor: '#4F46E5',
  },
  categoryPillText: {
    fontSize: 14,
    color: '#6B7280',
    fontWeight: '500',
  },
  categoryPillTextActive: {
    color: '#FFFFFF',
  },
  sortContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
  },
  sortLabel: {
    fontSize: 14,
    color: '#6B7280',
    marginRight: 12,
  },
  sortButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: '#EEF2FF',
    gap: 4,
  },
  sortButtonText: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '600',
  },
  productsGrid: {
    padding: 8,
  },
  productsRow: {
    justifyContent: 'space-between',
    marginBottom: 0,
  },
  productsList: {
    padding: 8,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingTop: 100,
  },
  loadingText: {
    marginTop: 16,
    color: '#9CA3AF',
    fontSize: 14,
  },
  loadingMore: {
    paddingVertical: 16,
    alignItems: 'center',
  },
  emptyState: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingTop: 100,
    paddingHorizontal: 32,
  },
  emptyText: {
    marginTop: 16,
    color: '#9CA3AF',
    fontSize: 16,
    textAlign: 'center',
  },
  emptySubtext: {
    marginTop: 8,
    color: '#9CA3AF',
    fontSize: 14,
    textAlign: 'center',
  },
});
