import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

interface SearchCorrectionProps {
  originalQuery: string;
  correctedQuery: string;
  onCorrectedSearch?: () => void;
}

export default function SearchCorrection({
  originalQuery,
  correctedQuery,
  onCorrectedSearch,
}: SearchCorrectionProps) {
  if (originalQuery === correctedQuery) {
    return null;
  }

  return (
    <View style={styles.container}>
      <Text style={styles.text}>
        Did you mean:{' '}
        <Text style={styles.correctedText} onPress={onCorrectedSearch}>
          {correctedQuery}
        </Text>
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: 12,
    backgroundColor: '#FEF3C7',
    borderRadius: 8,
    marginHorizontal: 16,
    marginBottom: 8,
  },
  text: {
    fontSize: 14,
    color: '#92400E',
  },
  correctedText: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '600',
    textDecorationLine: 'underline',
  },
});
