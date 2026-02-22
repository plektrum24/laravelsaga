import React, { useEffect, useRef, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Dimensions,
  ScrollView,
  NativeScrollEvent,
  NativeSyntheticEvent,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

interface Banner {
  id: number;
  title: string;
  subtitle: string;
  background_color: string;
  icon: string;
  action_url?: string;
}

interface PromotionalBannerProps {
  banners?: Banner[];
  autoScroll?: boolean;
  autoScrollInterval?: number;
  onBannerPress?: (banner: Banner) => void;
}

export default function PromotionalBanner({
  banners = [],
  autoScroll = true,
  autoScrollInterval = 5000,
  onBannerPress,
}: PromotionalBannerProps) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const scrollViewRef = useRef<ScrollView>(null);
  const autoScrollTimer = useRef<NodeJS.Timeout | null>(null);

  // Auto-scroll functionality
  useEffect(() => {
    if (autoScroll && banners.length > 1) {
      startAutoScroll();
    }
    return () => stopAutoScroll();
  }, [autoScroll, banners.length]);

  const startAutoScroll = () => {
    stopAutoScroll(); // Clear existing timer
    autoScrollTimer.current = setInterval(() => {
      const nextIndex = (currentIndex + 1) % banners.length;
      scrollViewRef.current?.scrollTo({
        x: nextIndex * SCREEN_WIDTH,
        animated: true,
      });
      setCurrentIndex(nextIndex);
    }, autoScrollInterval);
  };

  const stopAutoScroll = () => {
    if (autoScrollTimer.current) {
      clearInterval(autoScrollTimer.current);
      autoScrollTimer.current = null;
    }
  };

  const handleScroll = (event: NativeSyntheticEvent<NativeScrollEvent>) => {
    const scrollPosition = event.nativeEvent.contentOffset.x;
    const index = Math.round(scrollPosition / SCREEN_WIDTH);
    setCurrentIndex(index);
  };

  const handleBannerPress = (banner: Banner) => {
    stopAutoScroll();
    if (onBannerPress) {
      onBannerPress(banner);
    } else if (banner.action_url) {
      // Navigate to action URL if provided
      router.push(banner.action_url as any);
    }
  };

  if (banners.length === 0) {
    return null;
  }

  return (
    <View style={styles.container}>
      <ScrollView
        ref={scrollViewRef}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        onScroll={handleScroll}
        scrollEventThrottle={16}
        onTouchStart={stopAutoScroll}
        onTouchEnd={startAutoScroll}
      >
        {banners.map((banner, index) => (
          <TouchableOpacity
            key={banner.id}
            style={[
              styles.banner,
              { backgroundColor: banner.background_color || '#4F46E5' },
            ]}
            activeOpacity={0.8}
            onPress={() => handleBannerPress(banner)}
          >
            <View style={styles.bannerContent}>
              <View style={styles.bannerText}>
                <Text style={styles.bannerTitle}>{banner.title}</Text>
                <Text style={styles.bannerSubtitle}>{banner.subtitle}</Text>
                <View style={styles.bannerCta}>
                  <Text style={styles.bannerCtaText}>Shop Now</Text>
                </View>
              </View>
              <Ionicons
                name={banner.icon as any}
                size={56}
                color="rgba(255, 255, 255, 0.9)"
              />
            </View>
          </TouchableOpacity>
        ))}
      </ScrollView>

      {/* Pagination Dots */}
      {banners.length > 1 && (
        <View style={styles.pagination}>
          {banners.map((_, index) => (
            <View
              key={index}
              style={[
                styles.dot,
                index === currentIndex && styles.activeDot,
              ]}
            />
          ))}
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginVertical: 16,
  },
  banner: {
    width: SCREEN_WIDTH - 32,
    marginHorizontal: 16,
    borderRadius: 16,
    overflow: 'hidden',
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
  },
  bannerContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
  },
  bannerText: {
    flex: 1,
    paddingRight: 16,
  },
  bannerTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: 6,
  },
  bannerSubtitle: {
    fontSize: 14,
    color: 'rgba(255, 255, 255, 0.9)',
    marginBottom: 16,
    lineHeight: 20,
  },
  bannerCta: {
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 24,
    alignSelf: 'flex-start',
  },
  bannerCtaText: {
    color: '#4F46E5',
    fontWeight: '600',
    fontSize: 14,
  },
  pagination: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 12,
  },
  dot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: 'rgba(0, 0, 0, 0.2)',
    marginHorizontal: 4,
  },
  activeDot: {
    width: 24,
    backgroundColor: '#4F46E5',
  },
});
