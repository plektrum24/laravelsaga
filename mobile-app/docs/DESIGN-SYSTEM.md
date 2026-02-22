# Design System - SAGA POS Mobile App

**Version:** 1.0.0  
**Date:** 2026-02-22  
**Status:** Production Ready

---

## 🎨 Color System

### Brand Colors

```typescript
export const brand = {
  50: '#EEF2FF',
  100: '#E0E7FF',
  200: '#C7D2FE',
  300: '#A5B4FC',
  400: '#818CF8',
  500: '#6366F1', // Primary Brand
  600: '#4F46E5',
  700: '#4338CA',
  800: '#3730A3',
  900: '#312E81',
};
```

### Semantic Colors

```typescript
export const semantic = {
  success: '#10B981',
  successLight: '#ECFDF5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
  errorLight: '#FEF2F2',
  info: '#3B82F6',
  infoLight: '#EFF6FF',
};
```

### Neutral Colors

```typescript
export const gray = {
  50: '#F9FAFB',
  100: '#F3F4F6',
  200: '#E5E7EB',
  300: '#D1D5DB',
  400: '#9CA3AF',
  500: '#6B7280',
  600: '#4B5563',
  700: '#374151',
  800: '#1F2937',
  900: '#111827',
};
```

### Color Usage Guidelines

| Use Case | Color |
|----------|-------|
| Primary Actions | `brand.500` |
| Secondary Actions | `brand.100` |
| Text Primary | `gray.900` |
| Text Secondary | `gray.500` |
| Background | `gray.50` |
| Cards | `#FFFFFF` |
| Borders | `gray.200` |
| Success States | `success` |
| Error States | `error` |
| Warning States | `warning` |

---

## 📝 Typography

### Font Families

```typescript
export const fontFamily = {
  regular: 'Inter-Regular',
  medium: 'Inter-Medium',
  semiBold: 'Inter-SemiBold',
  bold: 'Inter-Bold',
};
```

### Font Sizes

```typescript
export const fontSize = {
  xs: 10,    // Captions, badges
  sm: 12,    // Labels, helper text
  base: 14,  // Body text
  lg: 16,    // Subtitles, buttons
  xl: 18,    // Titles
  '2xl': 20, // Section titles
  '3xl': 24, // Page titles
  '4xl': 32, // Display
};
```

### Font Weights

```typescript
export const fontWeight = {
  regular: '400',
  medium: '500',
  semiBold: '600',
  bold: '700',
};
```

### Line Heights

```typescript
export const lineHeight = {
  tight: 1.2,    // Headlines
  normal: 1.5,   // Body text
  relaxed: 1.75, // Long form content
};
```

### Typography Usage

| Element | Size | Weight | Line Height |
|---------|------|--------|-------------|
| Page Title | 24px (3xl) | Bold | Tight |
| Section Title | 20px (2xl) | SemiBold | Tight |
| Subtitle | 18px (xl) | SemiBold | Tight |
| Body Text | 14px (base) | Regular | Normal |
| Caption | 10px (xs) | Medium | Normal |
| Button | 16px (lg) | SemiBold | Normal |

---

## 📏 Spacing System

### Spacing Scale (8pt Grid)

```typescript
export const spacing = {
  0: 0,
  1: 4,    // 4px - Tight spacing
  2: 8,    // 8px - Icon padding
  3: 12,   // 12px - Component padding
  4: 16,   // 16px - Screen padding
  5: 20,   // 20px - Section spacing
  6: 24,   // 24px - Card spacing
  8: 32,   // 32px - Large gaps
  10: 40,  // 40px - Section margins
  12: 48,  // 48px - Page margins
  16: 64,  // 64px - Large sections
  20: 80,  // 80px - Page sections
  24: 96,  // 96px - Major sections
};
```

### Spacing Usage

| Element | Spacing |
|---------|---------|
| Screen Padding | `spacing[4]` (16px) |
| Card Padding | `spacing[4]` (16px) |
| Component Padding | `spacing[3-4]` (12-16px) |
| Section Spacing | `spacing[6]` (24px) |
| Element Gap | `spacing[2-3]` (8-12px) |

---

## 🎯 Icon System

### Icon Sizes

```typescript
export const iconSize = {
  sm: 16,   // Inline icons
  md: 20,   // Button icons, navigation
  lg: 24,   // Standalone icons
  xl: 32,   // Feature icons
  '2xl': 48, // Empty state icons
};
```

### Icon Usage

| Context | Size | Color |
|---------|------|-------|
| Navigation | 20px | `gray.500` |
| Action Buttons | 20px | `brand.500` |
| Status Icons | 24px | Semantic |
| Empty States | 48px | `gray.300` |
| Features | 32px | `brand.500` |

---

## 🔲 Border Radius

```typescript
export const radii = {
  none: 0,
  sm: 4,
  md: 8,
  lg: 12,
  xl: 16,
  '2xl': 20,
  full: 9999,
};
```

### Usage

| Element | Radius |
|---------|--------|
| Buttons | `lg` (12px) |
| Cards | `lg` (12px) |
| Inputs | `md` (8px) |
| Badges | `full` (pill) |
| Modals | `xl` (16px) |

---

## 🌑 Shadows

```typescript
export const shadows = {
  sm: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 1,
  },
  md: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  lg: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 4,
  },
  xl: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.2,
    shadowRadius: 12,
    elevation: 6,
  },
};
```

---

## ⚡ Transitions

```typescript
export const transitions = {
  fast: 150,
  normal: 300,
  slow: 500,
};

export const easing = {
  linear: 'linear',
  easeIn: 'ease-in',
  easeOut: 'ease-out',
  easeInOut: 'ease-in-out',
};
```

---

## 📱 Breakpoints

```typescript
export const breakpoints = {
  phone: 0,
  tablet: 768,
  desktop: 1024,
};
```

---

*Design System v1.0.0 - SAGA POS Mobile App*
