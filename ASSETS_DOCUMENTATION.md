# SAGA POS - Assets & Images Documentation

## Overview
All static assets (images, icons, and media) from the original sagatokov3 design have been copied to the Laravel `public/images/` directory with proper organization.

## Directory Structure

```
public/images/
├── ai/                      # AI-related illustrations
├── brand/                   # Brand logos (15 SVG files)
├── cards/                   # Card/payment method icons
├── carousel/                # Carousel/slider images
├── chat/                    # Chat and messaging icons
├── country/                 # Country flag icons (8 SVGs)
├── error/                   # Error page illustrations (404, 500, 503, maintenance)
├── grid-image/              # Grid layout sample images (6 JPGs)
├── icons/                   # Icon sets
├── logistics/               # Logistics/shipping related images
├── logo/                    # Application logos (4 files)
│   ├── auth-logo.svg        # Authentication page logo
│   ├── logo-dark.svg        # Logo for dark mode
│   ├── logo-icon.svg        # Icon-only logo
│   └── logo.svg             # Main logo
├── product/                 # Product sample images (5 JPGs)
├── shape/                   # Shape/design elements
├── support/                 # Support/help related images
├── task/                    # Task/todo related icons
├── user/                    # User avatars (38 images, mix of PNG, WEBP, JPG)
├── video-thumb/             # Video thumbnail images
├── favicon.ico              # Browser favicon
├── saga-logo-new.ico        # New SAGA logo
└── saga-logo.ico            # SAGA logo

```

## Image Assets by Category

### Logos & Branding (`brand/`)
- 15 brand/company logo variations in SVG format
- Used for partner displays, integrations, and partner listings
- **Usage**: Partner logos in dashboard, affiliate programs

### Country Flags (`country/`)
- 8 country flag SVG icons
- Represents different countries for localization/multi-region support
- **Usage**: Language/region selection, shipping destinations

### Error Pages (`error/`)
- Dark and light versions of error illustrations:
  - 404 (Not Found) - 404-dark.svg, 404.svg
  - 500 (Server Error) - 500-dark.svg, 500.svg
  - 503 (Service Unavailable) - 503-dark.svg, 503.svg
  - Maintenance - maintenance-dark.svg, maintenance.svg
  - Success - success-dark.svg, success.svg
- **Usage**: Error page templates in `resources/views/pages/errors/`

### Grid Images (`grid-image/`)
- 6 product/item sample images for grid layouts
- Image sizes: ~300KB-1MB, JPEG format
- **Usage**: Product catalog mockups, dashboard showcase

### Icons (`icons/`)
- File-related icons (file-dark.svg, file-icon.svg, etc.)
- Used throughout application for file operations
- **Usage**: File uploads, document management, file type indicators

### Logos (`logo/`)
- **auth-logo.svg**: Logo displayed on login/signup pages
- **logo-dark.svg**: Logo variant for dark mode
- **logo-icon.svg**: Icon-only version for favicon/shortcuts
- **logo.svg**: Primary application logo
- **Usage**: Navigation headers, page headers, authentication pages

### Product Images (`product/`)
- 5 sample product images in JPEG format
- Used for product catalog demonstrations
- **Usage**: Product listing templates, shopping cart, product details

### Shapes (`shape/`)
- Decorative geometric shapes and design elements
- SVG format for scalability
- **Usage**: Background patterns, decorative elements

### User Avatars (`user/`)
- 38 user avatar images in mixed formats (PNG, WEBP, JPG)
- Various avatar designs and colors
- **Usage**: User profiles, comment authors, team members, chat participants

### Video Thumbnails (`video-thumb/`)
- Sample video thumbnail images
- YouTube icon and other video-related images
- **Usage**: Video content sections, tutorials, demos

### Supporting Images
- **ai/**: AI-related illustrations (if available)
- **cards/**: Payment card icons and styles
- **carousel/**: Slider/carousel images
- **chat/**: Chat bubbles and messaging icons
- **logistics/**: Shipping, delivery, and logistics icons
- **support/**: Help, FAQ, and support-related images
- **task/**: Task list, checklist, and todo icons

## Using Images in Blade Templates

### Referencing Images
```blade
<!-- Logo in header -->
<img src="{{ asset('images/logo/logo.svg') }}" alt="SAGA POS" class="h-8">

<!-- Dark mode logo variant -->
<img src="{{ asset('images/logo/logo-dark.svg') }}" alt="SAGA POS" class="h-8 dark:block hidden">

<!-- User avatar -->
<img src="{{ asset('images/user/user-01.png') }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full">

<!-- Brand logos in carousel -->
<img src="{{ asset('images/brand/brand-01.svg') }}" alt="Partner 1" class="h-12">

<!-- Error page illustration -->
<img src="{{ asset('images/error/404.svg') }}" alt="Not Found" class="w-96">
<img src="{{ asset('images/error/404-dark.svg') }}" alt="Not Found" class="w-96 dark:block hidden">
```

### With Alpine.js
```blade
<div x-data="{ isDark: true }">
    <img :src="`/images/logo/${isDark ? 'logo-dark' : 'logo'}.svg`" alt="Logo">
</div>
```

### Using SVGs as Inline Components
```blade
@include('components.icons.file', ['class' => 'w-5 h-5'])
```

## Image Specifications

### SVG Assets
- **Format**: SVG (Scalable Vector Graphics)
- **Advantages**: Scalable, responsive, lightweight
- **Usage**: Logos, icons, illustrations that need to scale
- **Best for**: All resolutions, dark mode variants

### JPEG Assets
- **Format**: JPG
- **Size Range**: 3KB - 1.1MB
- **Usage**: Product images, photos, detailed illustrations
- **Optimization**: Consider lazy loading for bulk images

### PNG/WEBP Assets
- **Format**: PNG (lossless), WEBP (modern format)
- **Usage**: User avatars, images with transparency
- **Optimization**: WEBP variants for modern browsers

## Dark Mode Image Variants

Many images have dark mode versions:
```html
<!-- Light mode -->
<img src="images/error/404.svg" alt="" class="dark:hidden">

<!-- Dark mode -->
<img src="images/error/404-dark.svg" alt="" class="hidden dark:block">
```

## Favicon Setup
```blade
<!-- In <head> of your layout -->
<link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('images/saga-logo.ico') }}">
```

## Performance Optimization

### Lazy Loading Images
```blade
<img src="{{ asset('images/product/product-01.jpg') }}" 
     alt="Product" 
     loading="lazy" 
     class="w-full h-auto">
```

### Responsive Images with srcset
```blade
<img src="{{ asset('images/product/product-01.jpg') }}" 
     srcset="{{ asset('images/product/product-01.jpg') }} 1x, 
             {{ asset('images/product/product-01.jpg') }} 2x" 
     alt="Product" 
     class="w-full h-auto">
```

### Image Optimization
- SVG images are already optimized for web
- Consider using image optimization services for JPEG/PNG
- Use WebP format for modern browsers with PNG fallback

## Image License & Attribution
All images in the `public/images/` directory are:
- Part of the SAGA POS design system
- Licensed for use within the SAGA application
- Should not be republished or reused outside of SAGA POS without permission

## Adding New Images

When adding new images to the application:

1. **Determine Category**: Place images in appropriate subdirectory
2. **Format Consideration**:
   - Use SVG for logos, icons, illustrations
   - Use JPG for photographs
   - Use PNG for graphics with transparency
   - Consider WEBP for modern browsers
3. **Naming Convention**: Use descriptive, lowercase names with hyphens
   - Example: `product-blue-shirt.jpg`
4. **Size Optimization**: 
   - SVG: Keep under 100KB
   - JPG: Keep under 500KB
   - PNG: Keep under 1MB
   - Use compression tools for large files
5. **Dark Mode**: Create `-dark` variants if needed
   - Example: `logo-dark.svg`, `logo.svg`

## Image Paths in CSS

### Background Images
```css
.hero-section {
    background-image: url('{{ asset("images/shape/grid-bg.svg") }}');
}
```

### In Vite Asset Bundling
```javascript
import logo from '/images/logo/logo.svg';

const logoPath = logo; // Contains hashed filename for cache busting
```

## Testing Image Assets

To verify all images are properly accessible:

```bash
# Check image directories exist
ls -la public/images/

# Verify specific images
ls -la public/images/logo/
ls -la public/images/user/
```

## Image Gallery / Showcase

### Product Showcase
Location: `public/images/product/`
- product-01.jpg - Sample product 1
- product-02.jpg - Sample product 2
- product-03.jpg - Sample product 3
- product-04.jpg - Sample product 4
- product-05.jpg - Sample product 5

### User Avatars Gallery
Location: `public/images/user/`
- 38 different avatar images
- Used for diverse user representations
- Supports multi-cultural representation

### Grid Images Gallery
Location: `public/images/grid-image/`
- 6 high-quality sample images
- Suitable for grid/mosaic layouts
- Good for image showcase features

## Configuration & CDN

### Serving from CDN
If you want to serve images from a CDN:

```php
// config/app.php or env
ASSET_URL=https://cdn.example.com

// Usage in Blade
<img src="{{ asset('images/logo/logo.svg') }}" alt="Logo">
// Outputs: https://cdn.example.com/images/logo/logo.svg
```

### Static Asset Versioning
Laravel automatically handles cache busting for Vite assets. For static images:

```blade
<!-- Add version parameter for cache busting -->
<img src="{{ asset('images/logo/logo.svg?v=1') }}" alt="Logo">
```

## Troubleshooting

### Images not displaying
- Check file paths are correct: `{{ asset('images/...') }}`
- Verify files exist in `public/images/`
- Check file permissions (should be readable)
- Clear browser cache and Laravel cache

### Performance issues
- Use lazy loading for below-fold images
- Optimize large JPEG/PNG files
- Consider using SVG for icons/logos
- Use WebP format with PNG fallback

### Dark mode images not switching
- Ensure dark mode variant exists (e.g., `-dark.svg`)
- Use conditional classes: `dark:block hidden` / `dark:hidden block`
- Verify dark mode is properly enabled in app layout

## Related Documentation
- [Tailwind CSS Configuration](tailwind.config.js) - Theme colors for image overlays
- [Blade Components](BLADE_COMPONENT_LIBRARY.md) - Components that use images
- [Frontend Architecture](README.md) - Overall frontend structure
