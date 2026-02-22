import React, { forwardRef } from 'react';
import {
  TouchableOpacity,
  Text,
  StyleSheet,
  ActivityIndicator,
  ViewStyle,
  TextStyle,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

type ButtonVariant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
type ButtonSize = 'sm' | 'md' | 'lg';

interface ButtonProps {
  title: string;
  variant?: ButtonVariant;
  size?: ButtonSize;
  icon?: string;
  iconPosition?: 'left' | 'right';
  loading?: boolean;
  disabled?: boolean;
  fullWidth?: boolean;
  onPress?: () => void;
  style?: ViewStyle;
  textStyle?: TextStyle;
}

const Button = forwardRef<TouchableOpacity, ButtonProps>(
  (
    {
      title,
      variant = 'primary',
      size = 'md',
      icon,
      iconPosition = 'left',
      loading = false,
      disabled = false,
      fullWidth = false,
      onPress,
      style,
      textStyle,
    },
    ref
  ) => {
    const buttonStyles = [
      styles.button,
      styles[variant],
      styles[size],
      fullWidth && styles.fullWidth,
      (disabled || loading) && styles.disabled,
      style,
    ];

    const textStyles = [
      styles.text,
      styles[`${variant}Text`],
      styles[`${size}Text`],
      (disabled || loading) && styles.disabledText,
      textStyle,
    ];

    const iconSize = size === 'sm' ? 16 : size === 'md' ? 20 : 24;

    return (
      <TouchableOpacity
        ref={ref}
        style={buttonStyles}
        onPress={onPress}
        disabled={disabled || loading}
        activeOpacity={0.7}
      >
        {loading ? (
          <ActivityIndicator
            color={variant === 'primary' || variant === 'danger' ? '#FFFFFF' : '#4F46E5'}
            size="small"
          />
        ) : (
          <>
            {icon && iconPosition === 'left' && (
              <Ionicons
                name={icon as any}
                size={iconSize}
                color={variant === 'primary' || variant === 'danger' ? '#FFFFFF' : '#4F46E5'}
                style={styles.icon}
              />
            )}
            <Text style={textStyles}>{title}</Text>
            {icon && iconPosition === 'right' && (
              <Ionicons
                name={icon as any}
                size={iconSize}
                color={variant === 'primary' || variant === 'danger' ? '#FFFFFF' : '#4F46E5'}
                style={styles.icon}
              />
            )}
          </>
        )}
      </TouchableOpacity>
    );
  }
);

const styles = StyleSheet.create({
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 12,
  },
  // Variants
  primary: {
    backgroundColor: '#4F46E5',
  },
  secondary: {
    backgroundColor: '#EEF2FF',
  },
  outline: {
    backgroundColor: 'transparent',
    borderWidth: 2,
    borderColor: '#4F46E5',
  },
  ghost: {
    backgroundColor: 'transparent',
  },
  danger: {
    backgroundColor: '#EF4444',
  },
  // Sizes
  sm: {
    height: 32,
    paddingHorizontal: 12,
  },
  md: {
    height: 40,
    paddingHorizontal: 16,
  },
  lg: {
    height: 48,
    paddingHorizontal: 20,
  },
  fullWidth: {
    width: '100%',
  },
  disabled: {
    opacity: 0.5,
  },
  // Text Styles
  text: {
    fontWeight: '600',
  },
  primaryText: {
    color: '#FFFFFF',
  },
  secondaryText: {
    color: '#4F46E5',
  },
  outlineText: {
    color: '#4F46E5',
  },
  ghostText: {
    color: '#4F46E5',
  },
  dangerText: {
    color: '#FFFFFF',
  },
  smText: {
    fontSize: 12,
  },
  mdText: {
    fontSize: 14,
  },
  lgText: {
    fontSize: 16,
  },
  disabledText: {
    color: '#9CA3AF',
  },
  icon: {
    marginHorizontal: 4,
  },
});

export default Button;
