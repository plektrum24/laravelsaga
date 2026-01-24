# SAGA POS - Quick Start Guide for Developers

## 🚀 Getting Started

### Prerequisites
- PHP 8.3+
- Node.js 20+
- npm or yarn
- MySQL/PostgreSQL database
- Laravel 11+

### Installation

```bash
# 1. Clone the repository
git clone <repo-url> laravelsaga
cd laravelsaga

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Generate app key
php artisan key:generate

# 6. Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=saga_pos
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Run migrations
php artisan migrate

# 8. Build frontend assets
npm run build
# or for development with watch
npm run dev
```

### Running the Application

```bash
# Start Laravel development server
php artisan serve
# Access at http://localhost:8000

# In another terminal, start Vite development server (for CSS/JS auto-recompile)
npm run dev
```

---

## 📁 Project Structure

```
laravelsaga/
├── app/                          # Laravel application code
│   ├── Http/Controllers/         # Controller classes
│   ├── Models/                   # Eloquent models
│   ├── Services/                 # Business logic services
│   └── Helpers/                  # Helper functions
├── resources/
│   ├── views/                    # Blade templates
│   │   ├── layouts/app.blade.php # Master layout
│   │   ├── components/           # Reusable components (20+)
│   │   └── pages/                # Page templates
│   ├── js/                       # JavaScript files
│   │   ├── app.js               # Main entry point
│   │   ├── bootstrap.js         # Configuration
│   │   └── services/            # API, Auth, Barcode, Store services
│   └── css/
│       └── app.css              # Tailwind CSS entry
├── public/
│   ├── images/                  # 15 image directories (90+ files)
│   └── index.php                # Entry point
├── routes/
│   ├── web.php                  # Web routes
│   └── api.php                  # API routes
├── database/
│   ├── migrations/              # Database migrations
│   ├── factories/               # Model factories
│   └── seeders/                 # Database seeders
├── config/                       # Configuration files
├── tailwind.config.js           # Tailwind CSS configuration
├── vite.config.js               # Vite build configuration
└── composer.json / package.json # Dependencies
```

---

## 🎨 Frontend Components

### Using Components in Blade Templates

```blade
<!-- Button component -->
<x-button.primary href="{{ route('products.create') }}">
    Create Product
</x-button.primary>

<!-- Form input component -->
<x-form.input 
    name="product_name" 
    label="Product Name" 
    placeholder="Enter product name"
    required
/>

<!-- Card component -->
<x-card.card title="Sales Dashboard">
    <!-- Content here -->
</x-card.card>

<!-- Badge component -->
<x-badge.badge variant="success">In Stock</x-badge.badge>

<!-- Modal component -->
<x-modal.confirmation
    title="Delete Product?"
    message="This action cannot be undone."
    confirmText="Delete"
    cancelText="Cancel"
/>
```

### Component Categories
- **Button** (6 variants): primary, menu-link, tab, nav-item, dropdown
- **Form** (5): input, textarea, select, checkbox, radio
- **UI** (4): card, badge, avatar, alert
- **Modal** (3): modal, loading, confirmation
- **Table** (2): table, data-table

For detailed component documentation, see: [BLADE_COMPONENT_LIBRARY.md](BLADE_COMPONENT_LIBRARY.md)

---

## 🔌 JavaScript Services

### API Service
```javascript
import { api } from './services/index.js';

// GET request
const products = await api.get('/api/products', { page: 1 });

// POST request
const response = await api.post('/api/sales', {
    customer_id: 1,
    items: [...],
    total: 50000
});

// Error handling
try {
    const data = await api.get('/api/products');
} catch (error) {
    console.error('Failed:', error.message);
}
```

### Auth Service
```javascript
import { auth } from './services/index.js';

// Login
const result = await auth.login('user@example.com', 'password');

// Check authentication
if (auth.isAuthenticated()) {
    const user = auth.getCurrentUser();
}

// Role checking
if (auth.hasRole('cashier')) {
    // Show cashier features
}

// Logout
auth.logout();
```

### Barcode Service
```javascript
import { barcode } from './services/index.js';

// Initialize scanner
barcode.init();

// Listen for barcode scans
document.addEventListener('scan', (event) => {
    const code = event.detail.code;
    // Handle barcode
});

// QR code scanning
await barcode.startQrScanner('qr-container');

document.addEventListener('qr:scan', (event) => {
    const qrCode = event.detail.code;
    // Handle QR code
});
```

### Store Service
```javascript
import { store } from './services/index.js';

// User management
store.setUser(userData);
const user = store.getUser();

// Dark mode
store.toggleDarkMode();
if (store.isDarkMode()) {
    // Apply dark mode
}

// Check state
if (store.isAuthenticated()) {
    console.log('User:', store.getUserName());
}
```

For complete documentation, see: [JAVASCRIPT_SERVICES.md](JAVASCRIPT_SERVICES.md)

---

## 🗄️ Database Models & Migrations

### Key Models (to be created in Phase 2)
- User
- Tenant
- Product
- Inventory
- Sale
- SaleItem
- Customer
- Payment
- Report
- And more...

### Multi-Tenant Support
The application is designed for multi-tenant architecture:
```php
// Each user belongs to a tenant
$user->tenant_id;

// Filter queries by tenant
Product::where('tenant_id', auth()->user()->tenant_id)->get();
```

---

## 🛣️ Routes & Endpoints

### Web Routes
```php
GET    /                       → Dashboard
GET    /inventory             → Inventory list
GET    /inventory/create      → Add inventory form
GET    /sales                → Sales list
GET    /sales/create         → New sale form
GET    /customers            → Customer list
GET    /customers/create     → Add customer form
GET    /reports              → Reports view
GET    /settings             → Settings view
GET    /profile              → User profile
```

### API Endpoints (to be created in Phase 2)
```
Authentication
  POST   /api/auth/login                → Login
  POST   /api/auth/register             → Register
  POST   /api/auth/logout               → Logout
  POST   /api/auth/change-password      → Change password
  GET    /api/auth/profile              → Get profile

Products
  GET    /api/products                  → List all
  POST   /api/products                  → Create
  GET    /api/products/:id              → Get single
  PUT    /api/products/:id              → Update
  DELETE /api/products/:id              → Delete

Sales
  GET    /api/sales                     → List sales
  POST   /api/sales                     → Create sale
  GET    /api/sales/:id                 → Get sale details

And more...
```

---

## 🎨 Tailwind CSS & Dark Mode

### Color Palette
The custom Tailwind theme includes:
- **Primary**: brand (blue)
- **Status Colors**: success, error, warning, orange
- **Neutral**: gray (25 shades)
- **Additional**: pink, purple, indigo, cyan, violet

### Dark Mode Usage
```blade
<!-- Dark mode classes -->
<div class="bg-white dark:bg-gray-900">
    <h1 class="text-gray-900 dark:text-white">Title</h1>
</div>

<!-- Conditional visibility -->
<img src="logo.svg" class="dark:hidden">
<img src="logo-dark.svg" class="hidden dark:block">
```

### Responsive Design
```blade
<!-- Mobile-first responsive -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    <!-- Single column on mobile, 2 on tablet, 3 on desktop -->
</div>
```

---

## 🖼️ Asset Management

### Image Locations
All images are in `public/images/`:
- `brand/` - Brand logos
- `logo/` - Application logos
- `user/` - User avatars
- `product/` - Product images
- `icons/` - Icon sets
- `error/` - Error page illustrations
- And 9 more directories...

### Using Images in Blade
```blade
<!-- Logo -->
<img src="{{ asset('images/logo/logo.svg') }}" alt="SAGA POS">

<!-- User avatar -->
<img src="{{ asset('images/user/user-01.png') }}" alt="{{ $user->name }}">

<!-- With dark mode -->
<img src="{{ asset('images/logo/logo.svg') }}" class="dark:hidden">
<img src="{{ asset('images/logo/logo-dark.svg') }}" class="hidden dark:block">
```

---

## 🔐 Authentication & Authorization

### Login Flow
```php
// routes/web.php
Route::middleware(['guest'])->group(function () {
    Route::get('/login', LoginController@show)->name('login');
    Route::post('/login', LoginController@store);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController@show)->name('dashboard');
    Route::post('/logout', LogoutController@store)->name('logout');
});
```

### Role-Based Access Control
```php
// Check role in blade
@if(auth()->user()->hasRole('admin'))
    <admin-panel />
@endif

// Check in JavaScript
if (auth.hasRole('admin')) {
    // Show admin features
}

// Middleware (to be created in Phase 2)
Route::middleware(['role:admin'])->group(function () {
    // Admin-only routes
});
```

---

## 🧪 Development Workflow

### Frontend Development
```bash
# Watch CSS and JS changes
npm run dev

# Build for production
npm run build

# Check for TypeScript errors (if using)
npm run check

# Lint JavaScript
npm run lint
```

### Blade Component Development
```blade
<!-- Create a new component -->
<!-- resources/views/components/example/sample.blade.php -->

@props(['title', 'items' => []])

<div {{ $attributes->merge(['class' => 'my-class']) }}>
    @if($title)
        <h2>{{ $title }}</h2>
    @endif
    
    @foreach($items as $item)
        {{ $item }}
    @endforeach
</div>
```

### Creating New Pages
```blade
<!-- resources/views/pages/new-page.blade.php -->

@extends('layouts.app')

@section('title', 'Page Title | SAGA POS')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Page Title</h1>
    </div>
    
    <x-card.card>
        <!-- Your content -->
    </x-card.card>
@endsection
```

---

## 🐛 Common Issues & Solutions

### Issue: Styles not loading
```bash
# Clear cache and rebuild
npm run build
php artisan view:clear
php artisan cache:clear
```

### Issue: Images not displaying
- Ensure files exist in `public/images/`
- Use `asset('images/...')` helper
- Check file permissions
- Run `php artisan storage:link` if needed

### Issue: Components not recognized
- Verify component file naming (lowercase with hyphens)
- Check component path in `resources/views/components/`
- Component name must match file structure

### Issue: Dark mode not working
- Ensure `dark:` classes are in tailwind.config.js
- Check if `dark` class is set on `<html>` element
- Verify localStorage: `localStorage.setItem('darkMode', 'true')`

---

## 📚 Additional Resources

### Documentation Files
- [BLADE_COMPONENT_LIBRARY.md](BLADE_COMPONENT_LIBRARY.md) - Component reference
- [JAVASCRIPT_SERVICES.md](JAVASCRIPT_SERVICES.md) - Service documentation
- [ASSETS_DOCUMENTATION.md](ASSETS_DOCUMENTATION.md) - Image & asset usage
- [PAGES_CONVERSION_LOG.md](PAGES_CONVERSION_LOG.md) - Page conversion details
- [PHASE_1_COMPLETION_SUMMARY.md](PHASE_1_COMPLETION_SUMMARY.md) - Phase 1 overview

### External References
- [Laravel Documentation](https://laravel.com/docs)
- [Blade Templating](https://laravel.com/docs/blade)
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)
- [Vite](https://vitejs.dev)

---

## 🚀 Ready for Phase 2: Backend Development

The frontend is complete and ready for:
1. Database migrations and models
2. API controller development
3. Business logic implementation
4. Integration testing

All frontend services are properly structured to connect with Laravel backend endpoints.

---

## 📞 Support

For questions or issues:
1. Check the comprehensive documentation files
2. Review component examples in pages
3. Refer to service documentation for API integration
4. Check Laravel and Tailwind documentation

---

**Happy Coding! 🎉**

