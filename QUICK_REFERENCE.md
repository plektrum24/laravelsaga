# 🚀 SAGA POS Frontend - Quick Reference Card

## ⚡ Quick Start

```bash
# 1. Start the development server
php artisan serve

# 2. Open browser
http://127.0.0.1:8000

# 3. Access dashboard
You're done! 🎉
```

---

## 📍 Key URLs

| Page | URL | Route |
|------|-----|-------|
| Dashboard | http://localhost:8000/ | `dashboard` |
| Inventory | http://localhost:8000/inventory | `inventory` |
| Inventory Add | http://localhost:8000/inventory/create | `inventory.create` |
| Sales | http://localhost:8000/sales | `sales.index` |
| Sales Create | http://localhost:8000/sales/create | `sales.create` |
| Customers | http://localhost:8000/customers | `customers.index` |
| Customers Add | http://localhost:8000/customers/create | `customers.create` |
| Reports | http://localhost:8000/reports | `reports.index` |
| Settings | http://localhost:8000/settings | `settings.index` |
| Profile | http://localhost:8000/profile | `profile.show` |

---

## 🎨 Using Components

### Button
```blade
<x-button.primary href="#">Click me</x-button.primary>
<x-button.menu-link>Menu Item</x-button.menu-link>
```

### Form Input
```blade
<x-form.input name="email" label="Email" type="email" />
<x-form.textarea name="description" label="Description" />
<x-form.select name="category" label="Category" :options="$categories" />
```

### Card
```blade
<x-card.card title="Card Title">
    Your content here
</x-card.card>
```

### Badge
```blade
<x-badge.badge variant="success">Active</x-badge.badge>
<x-badge.badge variant="error">Error</x-badge.badge>
```

---

## 📝 Using Services

### API Service
```javascript
// GET request
const products = await api.get('/api/products');

// POST request
const result = await api.post('/api/sales', { /* data */ });

// Automatic CSRF token & Bearer auth included
```

### Auth Service
```javascript
// Login
await auth.login('user@example.com', 'password');

// Check authentication
if (auth.isAuthenticated()) {
    const user = auth.getCurrentUser();
}

// Check role
if (auth.hasRole('admin')) {
    // Admin features
}
```

### Store Service
```javascript
// Dark mode
store.toggleDarkMode();
if (store.isDarkMode()) {
    // Dark mode enabled
}

// User info
const userName = store.getUserName();
const userEmail = store.getUserEmail();
```

### Barcode Service
```javascript
// Initialization
barcode.init();

// Listen for scans
document.addEventListener('scan', (event) => {
    const code = event.detail.code;
    // Handle barcode
});
```

---

## 🎨 Dark Mode

### In Blade
```blade
<!-- Element with dark mode -->
<div class="bg-white dark:bg-gray-900">
    Content
</div>

<!-- Image with dark variant -->
<img src="logo.svg" class="dark:hidden">
<img src="logo-dark.svg" class="hidden dark:block">
```

### In JavaScript
```javascript
// Check dark mode
if (store.isDarkMode()) {
    console.log('Dark mode enabled');
}

// Toggle dark mode
store.toggleDarkMode();

// Set explicitly
store.setDarkMode(true);
```

---

## 📂 File Locations

| What | Where |
|------|-------|
| **Pages** | `resources/views/pages/` |
| **Components** | `resources/views/components/` |
| **Services** | `resources/js/services/` |
| **Styles** | `resources/css/app.css` |
| **Images** | `public/images/` |
| **Routes** | `routes/web.php` |

---

## 🔧 Common Commands

```bash
# Clear view cache
php artisan view:clear

# Build frontend assets
npm run build

# Watch for changes
npm run dev

# Clear all caches
php artisan cache:clear

# Database migrations (Phase 2)
php artisan migrate
php artisan migrate:rollback
```

---

## 📖 Documentation

| Document | Purpose |
|----------|---------|
| `QUICKSTART.md` | Getting started guide |
| `BLADE_COMPONENT_LIBRARY.md` | All components reference |
| `JAVASCRIPT_SERVICES.md` | Service API reference |
| `PHASE_2_PLANNING.md` | Backend architecture |
| `PROJECT_STATUS.md` | Current project status |
| `BUG_FIX_REPORT.md` | Latest bug fixes |

---

## ⚙️ Configuration

### Tailwind Colors
```
Primary: brand-500
Success: success-500
Error: error-500
Warning: warning-500
Orange: orange-500
```

### Responsive Breakpoints
```
sm:  640px
md:  768px
lg:  1024px
xl:  1280px
```

---

## 🐛 Troubleshooting

### Styles not loading
```bash
npm run build
php artisan view:clear
```

### Component not found
Check: `resources/views/components/[component-name].blade.php`

### Route not found
Check: `routes/web.php` for correct route name

### Dark mode not working
1. Clear localStorage: `localStorage.clear()`
2. Hard refresh: `Ctrl+Shift+R`

---

## 🚀 Next: Phase 2 Backend

When ready, create API endpoints for:
- `GET /api/dashboard/stats` - Dashboard statistics
- `GET /api/products` - Product listing
- `POST /api/auth/login` - User login
- `GET /api/sales` - Sales orders
- And 145+ more endpoints...

See [PHASE_2_PLANNING.md](PHASE_2_PLANNING.md) for complete plan.

---

## 📞 Quick Links

- **Dashboard**: http://127.0.0.1:8000
- **Laravel Docs**: https://laravel.com/docs
- **Tailwind Docs**: https://tailwindcss.com
- **Alpine Docs**: https://alpinejs.dev

---

**Status**: ✅ All systems operational  
**Last Updated**: January 24, 2026  
**Phase**: 1 Complete → Phase 2 Ready

