import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  FlatList,
  TouchableOpacity,
  Modal,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import * as SearchService from '../../services/search.service';

interface AutocompleteSuggestionsProps {
  onProductSelect?: (productId: string) => void;
  onCategorySelect?: (categoryId: string) => void;
  onSearch?: (query: string) => void;
  placeholder?: string;
}

export default function AutocompleteSuggestions({
  onProductSelect,
  onCategorySelect,
  onSearch,
  placeholder = 'Search products...',
}: AutocompleteSuggestionsProps) {
  const [query, setQuery] = useState('');
  const [suggestions, setSuggestions] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [showSuggestions, setShowSuggestions] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => {
      if (query.trim().length >= 2) {
        loadSuggestions();
      } else {
        setSuggestions([]);
      }
    }, 300);

    return () => clearTimeout(timer);
  }, [query]);

  const loadSuggestions = async () => {
    setIsLoading(true);
    try {
      const data = await SearchService.getSearchSuggestions(query);
      setSuggestions(data);
    } catch (error) {
      console.error('Error loading suggestions:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleSuggestionPress = (suggestion: any) => {
    if (suggestion.type === 'product' && suggestion.product_id) {
      onProductSelect?.(suggestion.product_id);
      router.push(`/product/${suggestion.product_id}` as any);
    } else if (suggestion.type === 'category') {
      onCategorySelect?.(suggestion.id);
      router.push({
        pathname: '/shop' as any,
        params: { category: suggestion.id },
      });
    } else {
      setQuery(suggestion.text);
      onSearch?.(suggestion.text);
    }
    setShowSuggestions(false);
  };

  const handleSearch = () => {
    if (query.trim()) {
      onSearch?.(query);
      setShowSuggestions(false);
    }
  };

  const clearSearch = () => {
    setQuery('');
    setSuggestions([]);
  };

  const renderSuggestion = ({ item }: { item: any }) => (
    <TouchableOpacity
      style={styles.suggestionItem}
      onPress={() => handleSuggestionPress(item)}
      activeOpacity={0.7}
    >
      {item.image_url && (
        <View style={styles.suggestionImage}>
          <Ionicons name="image-outline" size={32} color="#9CA3AF" />
        </View>
      )}
      <View style={styles.suggestionContent}>
        <Text style={styles.suggestionText}>{item.text}</Text>
        {item.type && (
          <Text style={styles.suggestionType}>{item.type}</Text>
        )}
      </View>
      <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
    </TouchableOpacity>
  );

  return (
    <>
      <View style={styles.container}>
        <View style={styles.inputContainer}>
          <Ionicons name="search" size={20} color="#9CA3AF" />
          <TextInput
            style={styles.input}
            placeholder={placeholder}
            placeholderTextColor="#9CA3AF"
            value={query}
            onChangeText={setQuery}
            onFocus={() => setShowSuggestions(true)}
            onSubmitEditing={handleSearch}
            returnKeyType="search"
            autoCapitalize="none"
            autoCorrect={false}
          />
          {query.length > 0 && (
            <TouchableOpacity onPress={clearSearch} style={styles.clearButton}>
              <Ionicons name="close-circle" size={20} color="#9CA3AF" />
            </TouchableOpacity>
          )}
        </View>
        {isLoading && (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="small" color="#4F46E5" />
          </View>
        )}
      </View>

      <Modal
        visible={showSuggestions && suggestions.length > 0}
        transparent={true}
        animationType="slide"
        onRequestClose={() => setShowSuggestions(false)}
      >
        <TouchableOpacity
          style={styles.modalOverlay}
          activeOpacity={1}
          onPress={() => setShowSuggestions(false)}
        >
          <View style={styles.suggestionsContainer}>
            <FlatList
              data={suggestions}
              renderItem={renderSuggestion}
              keyExtractor={(item) => item.id || item.text}
              keyboardShouldPersistTaps="handled"
            />
          </View>
        </TouchableOpacity>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    paddingHorizontal: 12,
    height: 48,
    gap: 8,
  },
  input: {
    flex: 1,
    fontSize: 16,
    color: '#111827',
  },
  clearButton: {
    padding: 4,
  },
  loadingContainer: {
    padding: 8,
    alignItems: 'center',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-start',
  },
  suggestionsContainer: {
    backgroundColor: '#FFFFFF',
    borderBottomLeftRadius: 16,
    borderBottomRightRadius: 16,
    maxHeight: '60%',
  },
  suggestionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  suggestionImage: {
    width: 40,
    height: 40,
    borderRadius: 8,
    backgroundColor: '#F3F4F6',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  suggestionContent: {
    flex: 1,
  },
  suggestionText: {
    fontSize: 15,
    color: '#111827',
    fontWeight: '500',
  },
  suggestionType: {
    fontSize: 12,
    color: '#6B7280',
    textTransform: 'capitalize',
  },
});
