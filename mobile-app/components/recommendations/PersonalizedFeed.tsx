import React, { useEffect, useState } from 'react';
import { View, StyleSheet, ActivityIndicator, Text } from 'react-native';
import RecommendationCarousel from './RecommendationCarousel';
import * as RecommendationsService from '../../services/recommendations.service';

interface PersonalizedFeedProps {
  userId: string;
  onProductPress?: (productId: string) => void;
  onAddToCart?: (productId: string) => void;
}

export default function PersonalizedFeed({
  userId,
  onProductPress,
  onAddToCart,
}: PersonalizedFeedProps) {
  const [products, setProducts] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadRecommendations();
  }, [userId]);

  const loadRecommendations = async () => {
    setIsLoading(true);
    try {
      const data = await RecommendationsService.getPersonalizedRecommendations(userId);
      setProducts(data);
    } catch (error) {
      console.error('Error loading personalized recommendations:', error);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="small" color="#4F46E5" />
        <Text style={styles.loadingText}>Loading recommendations...</Text>
      </View>
    );
  }

  if (products.length === 0) {
    return null;
  }

  return (
    <View style={styles.container}>
      <RecommendationCarousel
        title="Recommended For You"
        subtitle="Personalized based on your preferences"
        products={products}
        onProductPress={onProductPress}
        onAddToCart={onAddToCart}
        onViewAll={() => {}}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    paddingVertical: 16,
    marginBottom: 8,
  },
  loadingContainer: {
    padding: 20,
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 8,
    fontSize: 13,
    color: '#6B7280',
  },
});
