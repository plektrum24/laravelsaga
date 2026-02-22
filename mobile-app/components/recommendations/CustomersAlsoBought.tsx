import React, { useEffect, useState } from 'react';
import { View, StyleSheet } from 'react-native';
import RecommendationCarousel from './RecommendationCarousel';
import * as RecommendationsService from '../../services/recommendations.service';

interface CustomersAlsoBoughtProps {
  productId: string;
  onProductPress?: (productId: string) => void;
  onAddToCart?: (productId: string) => void;
}

export default function CustomersAlsoBought({
  productId,
  onProductPress,
  onAddToCart,
}: CustomersAlsoBoughtProps) {
  const [products, setProducts] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadRecommendations();
  }, [productId]);

  const loadRecommendations = async () => {
    setIsLoading(true);
    try {
      const data = await RecommendationsService.getCustomersAlsoBought(productId);
      setProducts(data);
    } catch (error) {
      console.error('Error loading customers also bought:', error);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading || products.length === 0) {
    return null;
  }

  return (
    <View style={styles.container}>
      <RecommendationCarousel
        title="Customers Also Bought"
        subtitle="Frequently purchased together"
        products={products}
        onProductPress={onProductPress}
        onAddToCart={onAddToCart}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginBottom: 8,
  },
});
