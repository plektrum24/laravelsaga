import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

interface Category {
  id: number;
  name: string;
  icon?: string;
  color?: string;
  product_count?: number;
}

interface CategoriesGridProps {
  categories?: Category[];
  title?: string;
  onCategoryPress?: (category: Category) => void;
}

// Default category icons mapping
const CATEGORY_ICONS: Record<string, string> = {
  'default': 'folder',
  'food': 'restaurant',
  'beverage': 'wine',
  'electronics': 'phone-portrait',
  'fashion': 'shirt',
  'health': 'heart',
  'beauty': 'flower',
  'home': 'home',
  'sports': 'basketball',
  'books': 'book',
  'toys': 'game-controller',
  'automotive': 'car',
  'garden': 'leaf',
  'office': 'briefcase',
  'pets': 'paw',
};

// Default colors for categories
const CATEGORY_COLORS = [
  '#4F46E5', // Indigo
  '#10B981', // Emerald
  '#F59E0B', // Amber
  '#EF4444', // Red
  '#3B82F6', // Blue
  '#8B5CF6', // Violet
  '#EC4899', // Pink
  '#14B8A6', // Teal
];

export default function CategoriesGrid({
  categories = [],
  title = 'Categories',
  onCategoryPress,
}: CategoriesGridProps) {
  const handleCategoryPress = (category: Category) => {
    if (onCategoryPress) {
      onCategoryPress(category);
    } else {
      // Default navigation to shop with category filter
      router.push({
        pathname: '/shop',
        params: { 
          category: category.id.toString(),
          category_name: category.name
        },
      });
    }
  };

  const getCategoryIcon = (category: Category): string => {
    // Try to match category name to icon
    const categoryName = category.name.toLowerCase();
    for (const [key, icon] of Object.entries(CATEGORY_ICONS)) {
      if (categoryName.includes(key)) {
        return icon;
      }
    }
    return category.icon || CATEGORY_ICONS.default;
  };

  const getCategoryColor = (index: number): string => {
    return category.color || CATEGORY_COLORS[index % CATEGORY_COLORS.length];
  };

  if (categories.length === 0) {
    return null;
  }

  return (
    <View style={styles.container}>
      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>{title}</Text>
        <TouchableOpacity onPress={() => router.push('/shop')}>
          <Text style={styles.seeAll}>See All</Text>
        </TouchableOpacity>
      </View>

      <ScrollView 
        horizontal 
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {categories.map((category, index) => {
          const color = getCategoryColor(index);
          const icon = getCategoryIcon(category);

          return (
            <TouchableOpacity
              key={category.id}
              style={styles.categoryCard}
              onPress={() => handleCategoryPress(category)}
              activeOpacity={0.7}
            >
              <View 
                style={[
                  styles.categoryIconContainer,
                  { backgroundColor: `${color}15` },
                ]}
              >
                <Ionicons 
                  name={icon as any} 
                  size={32} 
                  color={color} 
                />
              </View>
              <Text style={styles.categoryName} numberOfLines={1}>
                {category.name}
              </Text>
              {category.product_count !== undefined && (
                <Text style={styles.productCount}>
                  {category.product_count} items
                </Text>
              )}
            </TouchableOpacity>
          );
        })}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    paddingVertical: 16,
    marginBottom: 8,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
  },
  seeAll: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
  },
  scrollContent: {
    paddingHorizontal: 12,
  },
  categoryCard: {
    alignItems: 'center',
    marginHorizontal: 6,
    width: 84,
  },
  categoryIconContainer: {
    width: 68,
    height: 68,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  categoryName: {
    fontSize: 12,
    color: '#111827',
    textAlign: 'center',
    fontWeight: '500',
    marginBottom: 2,
  },
  productCount: {
    fontSize: 10,
    color: '#9CA3AF',
    textAlign: 'center',
  },
});
