import React, { useRef, useState } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  Dimensions,
  TouchableOpacity,
  Text,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

interface Image {
  id: number;
  url: string;
  alt?: string;
}

interface ProductGalleryProps {
  images?: Image[];
  onImagePress?: (index: number) => void;
}

export default function ProductGallery({
  images = [],
  onImagePress,
}: ProductGalleryProps) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const scrollViewRef = useRef<ScrollView>(null);

  const handleScroll = (event: any) => {
    const scrollPosition = event.nativeEvent.contentOffset.x;
    const index = Math.round(scrollPosition / SCREEN_WIDTH);
    setCurrentIndex(index);
  };

  const handleImagePress = () => {
    onImagePress?.(currentIndex);
  };

  if (!images || images.length === 0) {
    return (
      <View style={styles.container}>
        <View style={styles.imageContainer}>
          <View style={styles.placeholder}>
            <Ionicons name="image-outline" size={80} color="#9CA3AF" />
            <Text style={styles.placeholderText}>No images available</Text>
          </View>
        </View>
      </View>
    );
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
        decelerationRate="fast"
      >
        {images.map((image, index) => (
          <TouchableOpacity
            key={image.id}
            style={styles.imageContainer}
            onPress={handleImagePress}
            activeOpacity={0.9}
          >
            {image.url ? (
              <View style={styles.imageWrapper}>
                <View style={styles.imagePlaceholder}>
                  <Ionicons name="image" size={80} color="#D1D5DB" />
                </View>
                {/* Note: In production, use actual Image component with caching */}
              </View>
            ) : (
              <View style={styles.placeholder}>
                <Ionicons name="image-outline" size={80} color="#9CA3AF" />
              </View>
            )}
          </TouchableOpacity>
        ))}
      </ScrollView>

      {/* Image Counter */}
      {images.length > 1 && (
        <View style={styles.counterContainer}>
          <Text style={styles.counterText}>
            {currentIndex + 1} / {images.length}
          </Text>
        </View>
      )}

      {/* Pagination Dots */}
      {images.length > 1 && (
        <View style={styles.pagination}>
          {images.map((_, index) => (
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

      {/* Navigation Arrows */}
      {images.length > 1 && (
        <>
          {currentIndex > 0 && (
            <TouchableOpacity
              style={[styles.navButton, styles.navButtonLeft]}
              onPress={() => {
                scrollViewRef.current?.scrollTo({
                  x: (currentIndex - 1) * SCREEN_WIDTH,
                  animated: true,
                });
              }}
            >
              <Ionicons name="chevron-back" size={24} color="#FFFFFF" />
            </TouchableOpacity>
          )}

          {currentIndex < images.length - 1 && (
            <TouchableOpacity
              style={[styles.navButton, styles.navButtonRight]}
              onPress={() => {
                scrollViewRef.current?.scrollTo({
                  x: (currentIndex + 1) * SCREEN_WIDTH,
                  animated: true,
                });
              }}
            >
              <Ionicons name="chevron-forward" size={24} color="#FFFFFF" />
            </TouchableOpacity>
          )}
        </>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    height: 350,
    backgroundColor: '#F3F4F6',
    position: 'relative',
  },
  imageContainer: {
    width: SCREEN_WIDTH,
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
  },
  imageWrapper: {
    width: '100%',
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
  },
  imagePlaceholder: {
    flex: 1,
    width: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#E5E7EB',
  },
  placeholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  placeholderText: {
    marginTop: 16,
    fontSize: 14,
    color: '#9CA3AF',
  },
  counterContainer: {
    position: 'absolute',
    top: 16,
    right: 16,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
  },
  counterText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '600',
  },
  pagination: {
    position: 'absolute',
    bottom: 16,
    left: 0,
    right: 0,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    gap: 8,
  },
  dot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: 'rgba(255, 255, 255, 0.5)',
  },
  activeDot: {
    width: 24,
    backgroundColor: '#FFFFFF',
  },
  navButton: {
    position: 'absolute',
    top: '50%',
    marginTop: -20,
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  navButtonLeft: {
    left: 16,
  },
  navButtonRight: {
    right: 16,
  },
});
