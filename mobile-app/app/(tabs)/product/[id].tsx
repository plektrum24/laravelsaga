import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  Share,
  Alert,
} from 'react-native';
import { useLocalSearchParams, router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useProductStore } from '../../stores/product.store';
import { useCartStore } from '../../stores/cart.store';
import { Product } from '../../types/api.types';
import ProductGallery from '../../components/product/ProductGallery';
import UnitSelector, { ProductUnit } from '../../components/product/UnitSelector';
import QuantityStepper from '../../components/product/QuantityStepper';
import RelatedProducts from '../../components/product/RelatedProducts';

export default function ProductDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { selectedProduct, fetchProductById, relatedProducts, isLoading } = useProductStore();
  const { addItem } = useCartStore();
  
  // State
  const [quantity, setQuantity] = useState(1);
  const [selectedUnit, setSelectedUnit] = useState<ProductUnit | undefined>();
  const [isAddingToCart, setIsAddingToCart] = useState(false);

  useEffect(() => {
    if (id) {
      fetchProductById(id);
    }
  }, [id]);

  const handleAddToCart = async () => {
    if (!selectedProduct) return;

    setIsAddingToCart(true);
    try {
      await addItem(selectedProduct, quantity, selectedUnit);
      Alert.alert('Success', `Added ${quantity} x ${selectedProduct.name} to cart`);
      setQuantity(1);
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Failed to add to cart');
    } finally {
      setIsAddingToCart(false);
    }
  };

  const handleShare = async () => {
    if (!selectedProduct) return;

    try {
      await Share.share({
        message: `Check out ${selectedProduct.name} - Rp ${selectedProduct.price.toLocaleString('id-ID')}`,
        title: selectedProduct.name,
      });
    } catch (error) {
      console.error('Share error:', error);
    }
  };

  const handleUnitSelect = (unit: ProductUnit) => {
    setSelectedUnit(unit);
    setQuantity(1); // Reset quantity when unit changes
  };

  const calculateTotal = () => {
    if (!selectedProduct) return 0;
    const price = selectedUnit?.price || selectedProduct.price;
    return price * quantity;
  };

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#4F46E5" />
        <Text style={styles.loadingText}>Loading product...</Text>
      </View>
    );
  }

  if (!selectedProduct) {
    return (
      <View style={styles.notFoundContainer}>
        <Ionicons name="store-outline" size={64} color="#9CA3AF" />
        <Text style={styles.notFoundText}>Product not found</Text>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
        >
          <Text style={styles.backButtonText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  // Prepare units array
  const units: ProductUnit[] = selectedProduct.units || [
    {
      id: 1,
      name: selectedProduct.unit || 'Pcs',
      price: selectedProduct.price,
    },
  ];

  return (
    <View style={styles.container}>
      <ScrollView style={styles.scrollView}>
        {/* Product Image Gallery */}
        <ProductGallery
          images={selectedProduct.images || []}
          onImagePress={(index) => console.log('Image pressed:', index)}
        />

        {/* Product Info */}
        <View style={styles.productInfo}>
          <View style={styles.productHeader}>
            <Text style={styles.productName}>{selectedProduct.name}</Text>
            <TouchableOpacity style={styles.shareBtn} onPress={handleShare}>
              <Ionicons name="share-outline" size={24} color="#6B7280" />
            </TouchableOpacity>
          </View>

          {selectedProduct.category && (
            <TouchableOpacity
              style={styles.categoryBadge}
              onPress={() => router.push({
                pathname: '/shop',
                params: { category: selectedProduct.category_id }
              })}
            >
              <Ionicons name="folder" size={14} color="#4F46E5" />
              <Text style={styles.categoryText}>{selectedProduct.category.name}</Text>
            </TouchableOpacity>
          )}

          {/* Price */}
          <View style={styles.priceContainer}>
            <Text style={styles.productPrice}>
              Rp {calculateTotal().toLocaleString('id-ID')}
            </Text>
            {selectedProduct.sale_price && selectedProduct.sale_price < selectedProduct.price && (
              <>
                <Text style={styles.originalPrice}>
                  Rp {selectedProduct.price.toLocaleString('id-ID')}
                </Text>
                <View style={styles.discountBadge}>
                  <Text style={styles.discountText}>
                    -{Math.round((1 - selectedProduct.sale_price / selectedProduct.price) * 100)}%
                  </Text>
                </View>
              </>
            )}
          </View>

          {/* Stock Status */}
          <View style={[
            styles.stockStatus,
            selectedProduct.stock > 0 ? styles.inStock : styles.outOfStock,
          ]}>
            <Ionicons
              name={selectedProduct.stock > 0 ? 'checkmark-circle' : 'close-circle'}
              size={20}
              color={selectedProduct.stock > 0 ? '#10B981' : '#EF4444'}
            />
            <Text
              style={[
                styles.stockText,
                selectedProduct.stock > 0 ? styles.inStockText : styles.outOfStockText,
              ]}
            >
              {selectedProduct.stock > 0
                ? `In Stock (${selectedProduct.stock} available)`
                : 'Out of Stock'}
            </Text>
          </View>

          {/* Rating */}
          <View style={styles.ratingContainer}>
            <View style={styles.stars}>
              {[1, 2, 3, 4, 5].map((star) => (
                <Ionicons
                  key={star}
                  name={star <= (selectedProduct.rating || 0) ? 'star' : 'star-outline'}
                  size={18}
                  color="#F59E0B"
                />
              ))}
            </View>
            <Text style={styles.ratingText}>
              {selectedProduct.rating?.toFixed(1) || '0.0'} ({selectedProduct.reviews_count || 0} reviews)
            </Text>
          </View>

          {/* Unit Selector */}
          {units.length > 1 && (
            <UnitSelector
              units={units}
              selectedUnitId={selectedUnit?.id}
              onUnitSelect={handleUnitSelect}
            />
          )}

          {/* Quantity Selector */}
          <QuantityStepper
            quantity={quantity}
            maxQuantity={selectedProduct.stock}
            minQuantity={1}
            onQuantityChange={setQuantity}
          />

          {/* Description */}
          {selectedProduct.description && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Description</Text>
              <Text style={styles.description}>{selectedProduct.description}</Text>
            </View>
          )}

          {/* Specifications */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Specifications</Text>
            <View style={styles.specsGrid}>
              <View style={styles.specItem}>
                <Text style={styles.specLabel}>SKU</Text>
                <Text style={styles.specValue}>{selectedProduct.sku || 'N/A'}</Text>
              </View>
              <View style={styles.specItem}>
                <Text style={styles.specLabel}>Unit</Text>
                <Text style={styles.specValue}>{selectedProduct.unit || 'Pcs'}</Text>
              </View>
              <View style={styles.specItem}>
                <Text style={styles.specLabel}>Status</Text>
                <Text style={[
                  styles.specValue,
                  selectedProduct.status === 'active' ? styles.activeStatus : styles.inactiveStatus,
                ]}>
                  {selectedProduct.status === 'active' ? 'Active' : 'Inactive'}
                </Text>
              </View>
            </View>
          </View>

          {/* Related Products */}
          {relatedProducts && relatedProducts.length > 0 && (
            <RelatedProducts
              products={relatedProducts}
              onProductPress={(product) => router.push(`/product/${product.id}`)}
              onSeeAllPress={() => router.push({
                pathname: '/shop',
                params: { category: selectedProduct.category_id }
              })}
            />
          )}

          <View style={{ height: 100 }} />
        </View>
      </ScrollView>

      {/* Add to Cart Bar */}
      <View style={styles.bottomBar}>
        <View style={styles.bottomInfo}>
          <Text style={styles.totalLabel}>Total</Text>
          <Text style={styles.totalAmount}>
            Rp {calculateTotal().toLocaleString('id-ID')}
          </Text>
        </View>
        <TouchableOpacity
          style={[
            styles.addToCartBtn,
            selectedProduct.stock === 0 && styles.addToCartBtnDisabled,
            isAddingToCart && styles.addToCartBtnAdding,
          ]}
          onPress={handleAddToCart}
          disabled={selectedProduct.stock === 0 || isAddingToCart}
        >
          {isAddingToCart ? (
            <ActivityIndicator color="#FFFFFF" />
          ) : (
            <>
              <Ionicons name="cart" size={20} color="#FFFFFF" />
              <Text style={styles.addToCartText}>
                {selectedProduct.stock > 0 ? 'Add to Cart' : 'Out of Stock'}
              </Text>
            </>
          )}
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
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
  notFoundContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  notFoundText: {
    fontSize: 16,
    color: '#9CA3AF',
    marginTop: 16,
    marginBottom: 24,
  },
  backButton: {
    backgroundColor: '#4F46E5',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 12,
  },
  backButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  scrollView: {
    flex: 1,
  },
  imageContainer: {
    height: 350,
    backgroundColor: '#F3F4F6',
    position: 'relative',
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  imagePlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  featuredBadge: {
    position: 'absolute',
    top: 16,
    right: 16,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F59E0B',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    gap: 4,
  },
  featuredBadgeText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '600',
  },
  productInfo: {
    padding: 16,
  },
  productHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 8,
  },
  productName: {
    flex: 1,
    fontSize: 22,
    fontWeight: 'bold',
    color: '#111827',
    marginRight: 12,
  },
  shareBtn: {
    padding: 4,
  },
  categoryBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    alignSelf: 'flex-start',
    marginBottom: 16,
    gap: 6,
  },
  categoryText: {
    fontSize: 13,
    color: '#4F46E5',
    fontWeight: '500',
  },
  priceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 16,
  },
  productPrice: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#4F46E5',
  },
  originalPrice: {
    fontSize: 16,
    color: '#9CA3AF',
    textDecorationLine: 'line-through',
  },
  discountBadge: {
    backgroundColor: '#FEF2F2',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  discountText: {
    fontSize: 12,
    color: '#EF4444',
    fontWeight: '600',
  },
  stockStatus: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 16,
  },
  inStock: {
    backgroundColor: '#ECFDF5',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
  },
  outOfStock: {
    backgroundColor: '#FEF2F2',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
  },
  stockText: {
    fontSize: 14,
    fontWeight: '500',
  },
  inStockText: {
    color: '#10B981',
  },
  outOfStockText: {
    color: '#EF4444',
  },
  ratingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 16,
  },
  stars: {
    flexDirection: 'row',
    gap: 2,
  },
  ratingText: {
    fontSize: 14,
    color: '#6B7280',
  },
  section: {
    marginTop: 24,
    borderTopWidth: 1,
    borderTopColor: '#F3F4F6',
    paddingTop: 16,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 12,
  },
  description: {
    fontSize: 14,
    color: '#6B7280',
    lineHeight: 22,
  },
  specsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  specItem: {
    flex: 1,
    minWidth: '30%',
    backgroundColor: '#F9FAFB',
    padding: 12,
    borderRadius: 8,
  },
  specLabel: {
    fontSize: 12,
    color: '#9CA3AF',
    marginBottom: 4,
  },
  specValue: {
    fontSize: 14,
    color: '#111827',
    fontWeight: '500',
  },
  activeStatus: {
    color: '#10B981',
  },
  inactiveStatus: {
    color: '#EF4444',
  },
  quantityContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#F3F4F6',
    borderRadius: 12,
    padding: 4,
    alignSelf: 'flex-start',
  },
  quantityBtn: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
  },
  quantityBtnDisabled: {
    opacity: 0.5,
  },
  quantityValue: {
    width: 48,
    textAlign: 'center',
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  seeAll: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
  },
  comingSoonText: {
    fontSize: 14,
    color: '#9CA3AF',
    fontStyle: 'italic',
  },
  bottomBar: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
    paddingHorizontal: 16,
    paddingVertical: 12,
    paddingBottom: 16,
  },
  bottomInfo: {
    flex: 1,
  },
  totalLabel: {
    fontSize: 12,
    color: '#6B7280',
  },
  totalAmount: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#111827',
  },
  addToCartBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#4F46E5',
    paddingHorizontal: 24,
    paddingVertical: 14,
    borderRadius: 12,
    gap: 8,
    minWidth: 160,
    justifyContent: 'center',
  },
  addToCartBtnDisabled: {
    backgroundColor: '#9CA3AF',
  },
  addToCartBtnAdding: {
    opacity: 0.8,
  },
  addToCartText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
});
