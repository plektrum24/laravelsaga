import React, { forwardRef, useState } from 'react';
import {
  View,
  TextInput,
  Text,
  StyleSheet,
  TouchableOpacity,
  ViewStyle,
  TextStyle,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface InputProps {
  label?: string;
  placeholder?: string;
  value: string;
  onChangeText?: (text: string) => void;
  error?: string;
  helperText?: string;
  leftIcon?: string;
  rightIcon?: string;
  onRightIconPress?: () => void;
  secureTextEntry?: boolean;
  multiline?: boolean;
  numberOfLines?: number;
  editable?: boolean;
  style?: ViewStyle;
  inputStyle?: TextStyle;
}

const Input = forwardRef<TextInput, InputProps>(
  (
    {
      label,
      placeholder,
      value,
      onChangeText,
      error,
      helperText,
      leftIcon,
      rightIcon,
      onRightIconPress,
      secureTextEntry = false,
      multiline = false,
      numberOfLines = 1,
      editable = true,
      style,
      inputStyle,
    },
    ref
  ) => {
    const [isFocused, setIsFocused] = useState(false);

    const containerStyles = [
      styles.container,
      error && styles.errorContainer,
      !editable && styles.disabledContainer,
      style,
    ];

    const inputStyles = [
      styles.input,
      multiline && styles.multiline,
      !editable && styles.disabledInput,
      inputStyle,
    ];

    return (
      <View style={styles.wrapper}>
        {label && <Text style={styles.label}>{label}</Text>}
        
        <View style={containerStyles}>
          {leftIcon && (
            <Ionicons name={leftIcon as any} size={20} color="#9CA3AF" style={styles.leftIcon} />
          )}
          
          <TextInput
            ref={ref}
            style={inputStyles}
            placeholder={placeholder}
            placeholderTextColor="#9CA3AF"
            value={value}
            onChangeText={onChangeText}
            secureTextEntry={secureTextEntry}
            multiline={multiline}
            numberOfLines={numberOfLines}
            editable={editable}
            onFocus={() => setIsFocused(true)}
            onBlur={() => setIsFocused(false)}
          />
          
          {rightIcon && (
            <TouchableOpacity onPress={onRightIconPress} disabled={!onRightIconPress}>
              <Ionicons
                name={rightIcon as any}
                size={20}
                color={error ? '#EF4444' : '#9CA3AF'}
                style={styles.rightIcon}
              />
            </TouchableOpacity>
          )}
        </View>
        
        {(error || helperText) && (
          <Text style={[styles.helperText, error && styles.errorText]}>
            {error || helperText}
          </Text>
        )}
      </View>
    );
  }
);

const styles = StyleSheet.create({
  wrapper: {
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 8,
  },
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    paddingHorizontal: 12,
    minHeight: 48,
  },
  errorContainer: {
    borderColor: '#EF4444',
  },
  disabledContainer: {
    backgroundColor: '#F3F4F6',
    opacity: 0.7,
  },
  input: {
    flex: 1,
    fontSize: 16,
    color: '#111827',
    paddingVertical: 12,
    paddingHorizontal: 8,
  },
  multiline: {
    textAlignVertical: 'top',
    minHeight: 100,
  },
  disabledInput: {
    color: '#9CA3AF',
  },
  leftIcon: {
    marginRight: 8,
  },
  rightIcon: {
    marginLeft: 8,
  },
  helperText: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 4,
    marginLeft: 4,
  },
  errorText: {
    color: '#EF4444',
  },
});

export default Input;
