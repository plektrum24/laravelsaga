# 🎨 SAGATOKOV3 - UI/UX COMPONENTS & STYLING GUIDE

**Purpose**: Copy semua UI styles, components, dan design patterns dari sagatokov3 ke Laravel  
**Status**: Frontend UI/UX First (Backend & Logic later)

---

## 📐 DESIGN TOKENS

### Color Palette (dari style.css)

#### Primary Brand
```css
--color-brand-25: #f2f7ff    (lightest)
--color-brand-50: #ecf3ff
--color-brand-100: #dde9ff
--color-brand-200: #c2d6ff
--color-brand-300: #9cb9ff
--color-brand-400: #7592ff
--color-brand-500: #465fff   (PRIMARY)
--color-brand-600: #3641f5
--color-brand-700: #2a31d8
--color-brand-800: #252dae
--color-brand-900: #262e89
--color-brand-950: #161950   (darkest)
```

#### Blue Light (Secondary)
```css
--color-blue-light-25: #f5fbff
--color-blue-light-50: #f0f9ff
--color-blue-light-100: #e0f2fe
--color-blue-light-200: #b9e6fe
--color-blue-light-300: #7cd4fd
--color-blue-light-400: #36bffa
--color-blue-light-500: #0ba5ec
--color-blue-light-600: #0086c9
--color-blue-light-700: #026aa2
--color-blue-light-800: #065986
--color-blue-light-900: #0b4a6f
--color-blue-light-950: #062c41
```

#### Neutral/Gray Scale
```css
--color-gray-25: #fcfcfd     (almost white)
--color-gray-50: #f9fafb
--color-gray-100: #f2f4f7
--color-gray-200: #e4e7ec
--color-gray-300: #d0d5dd
--color-gray-400: #98a2b3
--color-gray-500: #667085
--color-gray-600: #475467
--color-gray-700: #344054
--color-gray-800: #1d2939
--color-gray-900: #101828
--color-gray-950: #0c111d    (nearly black)
```

#### Status Colors (not shown but common)
- Success: Green variants
- Error/Danger: Red variants
- Warning: Yellow/Orange variants
- Info: Light blue variants

### Typography

**Font**: Outfit (Google Fonts)
- Weights: 100-900
- Fallback: sans-serif

**Text Sizes**:
```css
Title 2XL: 72px (line-height: 90px)
Title XL:  60px (line-height: 72px)
Title LG:  48px (line-height: 60px)
Title MD:  36px (line-height: 44px)
Title SM:  30px (line-height: 38px)
Theme XL:  20px (line-height: 30px)
Theme SM:  14px (line-height: 20px)
Theme XS:  12px (line-height: 18px)
```

### Spacing Scale
- Based on 4px unit
- Common values: 4px, 8px, 12px, 16px, 20px, 24px, 32px, 48px, 64px

### Border Radius
- Small: 4px
- Medium: 8px
- Large: 12px
- Extra Large: 16px (cards)
- Full: 9999px (pills, avatars)

### Shadows
Multiple shadow levels for depth (z-index layering)

### Breakpoints (Custom)
```css
2xsm: 375px   (small phones)
xsm:  425px   (phones)
sm:   640px   (tablets portrait)
md:   768px   (tablets)
lg:   1024px  (small desktop)
xl:   1280px  (desktop)
2xl:  1536px  (large desktop)
3xl:  2000px  (extra large)
```

---

## 🧩 COMPONENT LIBRARY

### 1. BUTTONS

**Styles**: Primary, Secondary, Tertiary, Danger, Disabled  
**Sizes**: Small, Medium, Large  
**States**: Default, Hover, Active, Disabled  
**Icons**: With left/right icons

**Location**: `src/partials/buttons/`

### 2. FORMS

**Components**:
- Text Input (with placeholder, validation state)
- Email Input
- Password Input
- Textarea
- Select Dropdown
- Multi-select
- Checkbox
- Radio Button
- Toggle Switch
- Date Picker (Flatpickr)
- File Upload
- Search Input

**Validation States**: Default, Focus, Error, Success

**Location**: `src/partials/form-elements/` (referenced in pos.html, dashboard.html)

### 3. TABLES

**Features**:
- Sortable columns
- Pagination
- Row selection (checkbox)
- Bulk actions
- Responsive (horizontal scroll on mobile)
- Custom column templates
- Row actions (edit, delete, view)

**Location**: `src/partials/table/`

### 4. NAVIGATION

#### Sidebar
- Logo/brand
- Main menu items
- Submenu collapse/expand
- Collapse toggle button
- Icon + label per item
- Active state highlighting

**File**: `src/partials/sidebar.html`, `src/partials/sidebar-saga.html`

#### Top Header
- Search bar
- User profile dropdown
- Notifications icon with count
- Dark mode toggle
- Settings icon
- Logout button

**File**: `src/partials/header.html`

#### Breadcrumb
- Current page path
- Clickable links

**File**: `src/partials/breadcrumb.html`

### 5. CARDS

**Types**:
- Data card (with metric value, label, change %)
- Product card (with image, name, price, actions)
- Transaction card (with items, total)
- Alert card (with icon, title, message)

**Location**: `src/partials/common-grid-shape.html`

### 6. ALERTS & NOTIFICATIONS

**Types**:
- Success alert (green)
- Error alert (red)
- Warning alert (yellow)
- Info alert (blue)

**Components**:
- Toast notifications (top-right)
- Modal alerts (centered)
- Inline alerts (within forms)

**Library**: SweetAlert2

### 7. MODALS/DIALOGS

**Types**:
- Confirmation dialog
- Form modal
- Alert modal
- Large modal (fullscreen-like)

**Features**:
- Overlay backdrop (clickable to close)
- Close button (X)
- Action buttons (Cancel, Confirm)
- Scrollable body
- Header/footer sections

**Example**: `Transaction Success Modal` in pos.html

### 8. AVATARS

**Styles**:
- Image avatars (with fallback)
- Initial avatars (colored background)
- Status badge (online, offline)
- Size variants (small, medium, large)

**Location**: `src/partials/avatar/`

### 9. BADGES

**Types**:
- Status badges (pending, active, inactive)
- Category badges
- Color variants
- Size variants

**Location**: `src/partials/badge/`

### 10. CHARTS

**Types Supported**:
- Line chart
- Bar chart
- Pie chart
- Area chart

**Library**: Chart.js or ApexCharts

**Files**: 
- `src/bar-chart.html`
- `src/line-chart.html`
- `src/js/components/charts/`

### 11. CALENDAR

**Features**:
- Month/year view
- Event display
- Event creation modal
- Click to select date

**Library**: Flatpickr (custom)

**File**: `src/calendar.html`

### 12. DROPDOWNS

**Features**:
- Click to open/close
- Keyboard navigation
- Search within dropdown (for large lists)
- Icon support
- Custom styling

### 13. ACCORDION/COLLAPSE

**Features**:
- Click header to expand/collapse
- Icon rotation on toggle
- Smooth animation
- Multiple/single mode

### 14. TABS

**Features**:
- Tab list (horizontal)
- Tab content area
- Active tab styling
- Icon support per tab

### 15. SEARCH

**Types**:
- Text search
- Advanced search with filters
- Autocomplete/suggestions
- QR code scan input

**File**: Dashboard search, Product search in POS

### 16. PAGINATION

**Features**:
- Previous/Next buttons
- Page numbers
- Current page highlight
- "X of Y" display

### 17. PROGRESS BARS

**Types**:
- Horizontal progress bar
- Circular progress (for metrics)
- Color variants (success, warning, danger)

### 18. LOADERS

**Types**:
- Spinner/loading animation
- Skeleton loading
- Progress animation

**File**: `src/partials/preloader.html`

### 19. EMPTY STATES

**Design**:
- Icon
- Heading
- Description
- Call-to-action button

### 20. MEDIA COMPONENTS

**Types**:
- Image grid
- Video embed
- Carousel (Swiper)
- Lightbox/modal image viewer

**Files**: `src/images.html`, `src/videos.html`

---

## 📄 PAGE TEMPLATES

### Dashboard (`dashboard.html` - 1015 lines)

**Layout**:
```
┌─────────────────────────────────────┐
│  Header (search, profile, notifications) │
├──────────┬──────────────────────────┤
│          │                          │
│ Sidebar  │  Main Content Area      │
│          │  - Metric Cards         │
│          │  - Charts               │
│          │  - Recent Transactions  │
│          │  - Quick Actions        │
└──────────┴──────────────────────────┘
```

**Key Sections**:
1. **Top Metrics** - 4 cards showing:
   - Total Revenue
   - Today's Sales
   - Total Orders
   - Active Customers

2. **Charts Section**:
   - Sales trend line chart
   - Category pie chart
   - Revenue by day (bar chart)

3. **Recent Transactions Table**:
   - Date, Invoice #, Customer, Amount, Status
   - Action buttons (view, print)

4. **Quick Actions**:
   - New Sale button
   - New Order button
   - Print Receipt button

5. **Notifications Widget**:
   - Low stock alerts
   - Pending orders
   - New returns

### POS Page (`pos.html` - 1395 lines)

**Layout**:
```
┌────────────────────────────────────────┐
│  Header (Price Checker, Settings)      │
├─────────────┬────────────────┬─────────┤
│             │                │         │
│  Products   │   Cart/Items   │ Payment │
│  - Grid/    │   - Item List  │ - Total │
│    List     │   - Quantity   │ - Disc  │
│  - Search   │   - Remove btn │ - Tax   │
│  - Barcode  │                │ - Method│
│    Scanner  │                │         │
│             │                │         │
└─────────────┴────────────────┴─────────┘
```

**Components**:
1. **Product Section**:
   - Product cards with image, name, price
   - Category filter buttons
   - Search/barcode input
   - Quantity adjuster (+ button, number, - button)

2. **Cart Section**:
   - Item list (product, qty, price, subtotal)
   - Remove button per item
   - Clear cart button
   - Swipe to remove (mobile)

3. **Payment Section**:
   - Subtotal display
   - Discount input + %/Rp toggle
   - Tax calculation
   - Total amount
   - Customer selector
   - Payment method selector (Cash, Card, etc.)
   - Amount tendered input
   - Change calculation display
   - "Complete Sale" button

4. **Success Modal** (after transaction):
   - Success icon
   - Invoice number
   - Items summary
   - Receipt actions (print, email, send)

### Inventory Page (`inventory.html`)

**Layout**:
```
┌──────────────────────────────────────┐
│  Header (Branch Selector, Filter)    │
├──────────────────────────────────────┤
│  Product List Table                  │
│  - SKU, Name, Category               │
│  - Stock Level (with color indicator)│
│  - Reorder Level                     │
│  - Actions (edit, add stock)         │
└──────────────────────────────────────┘
```

**Features**:
- Stock level color coding (red if low, yellow if near min, green if ok)
- Quick add stock button
- Low stock badges
- Search functionality

### Customer/Supplier Pages (`customers.html`, `suppliers.html`)

**Layout**:
```
┌──────────────────────────────────────┐
│  Filter & Search                     │
├──────────────────────────────────────┤
│  List Table                          │
│  - Name, Contact, City               │
│  - Actions (view, edit, delete)      │
│                                      │
│  OR                                  │
│                                      │
│  Card Grid View                      │
│  - Contact info                      │
│  - Quick stats                       │
└──────────────────────────────────────┘
```

### Reports Page (`reports.html`)

**Layout**:
```
Date Range Filter → Report Type Selector
↓
┌────────────────────────────────────────┐
│  Summary Metrics                       │
├────────────────────────────────────────┤
│  Detailed Table/Chart View             │
├────────────────────────────────────────┤
│  Export Options (PDF, Excel, Print)    │
└────────────────────────────────────────┘
```

---

## 🎯 INTERACTIVE FEATURES

### Real-time Search
- As-you-type filtering
- Autocomplete suggestions
- QR code scanner integration
- Result count

### Data Import
- File upload (Excel, CSV)
- Progress indicator
- Success/error messages
- Validation feedback

### Data Export
- Format selection (PDF, Excel, CSV)
- Date range selection
- Field selection (which columns to export)
- Download trigger

### Bulk Operations
- Select all / deselect all
- Row checkboxes
- Bulk action dropdown (delete, export, print, etc.)
- Batch processing indicator

### Responsive Design
- Mobile-first approach
- Sidebar collapse on small screens
- Table horizontal scroll on mobile
- Touch-friendly button sizes
- Simplified modals for mobile

### Dark Mode
- Toggle switch in header
- Color inversion
- Reduced brightness
- Persisted in localStorage

### Print Layout (`print.css`)
- Receipt template (2.5" wide, infinite height)
- Invoice template (A4)
- Header: Logo, store info
- Content: Items, totals
- Footer: Cashier name, timestamp
- Barcode/QR code support

---

## 🔧 IMPLEMENTATION IN LARAVEL

### Blade Component Structure

```blade
<!-- Base layout -->
@extends('layouts.app')

<!-- Page section -->
@section('content')
  @include('partials.breadcrumb')
  
  <div class="container-fluid">
    @yield('page-content')
  </div>
@endsection
```

### Reusable Components

```blade
<!-- Button component -->
<x-button 
  type="primary"
  size="md"
  :icon="'plus'"
  wire:click="create"
>
  New Product
</x-button>

<!-- Table component -->
<x-data-table 
  :columns="$columns"
  :data="$products"
  sortable
  searchable
/>

<!-- Card component -->
<x-metric-card 
  label="Total Sales"
  :value="$totalSales"
  icon="shopping-cart"
  trend="up"
/>
```

### Alpine.js Integration

Keep Alpine.js for:
- Interactive forms
- Real-time validation
- Modal management
- Dynamic UI updates
- QR code scanning

---

## 📊 CSS VARIABLES FOR DARK MODE

```css
@media (prefers-color-scheme: dark) {
  :root {
    --bg-primary: var(--color-gray-900);
    --bg-secondary: var(--color-gray-800);
    --text-primary: var(--color-gray-25);
    --text-secondary: var(--color-gray-400);
    --border-color: var(--color-gray-700);
  }
}
```

---

## ✅ COPY CHECKLIST

### CSS Files to Copy
- [ ] `src/css/style.css` → `resources/css/app.css`
- [ ] `src/css/print.css` → `resources/css/print.css`
- [ ] Tailwind config → `tailwind.config.js`

### Partials/Components to Convert
- [ ] Sidebar → `components/sidebar.blade.php`
- [ ] Header → `components/header.blade.php`
- [ ] Buttons → `components/button.blade.php`
- [ ] Forms → `components/form-input.blade.php`
- [ ] Tables → `components/data-table.blade.php`
- [ ] Cards → `components/metric-card.blade.php`
- [ ] Modals → `components/modal.blade.php`
- [ ] Alerts → `components/alert.blade.php`

### Pages to Convert (HTML → Blade)
- [ ] Dashboard → `pages/dashboard.blade.php`
- [ ] POS → `pages/pos.blade.php`
- [ ] Inventory → `pages/inventory.blade.php`
- [ ] Products → `pages/products.blade.php`
- [ ] Customers → `pages/customers.blade.php`
- [ ] Suppliers → `pages/suppliers.blade.php`
- [ ] Orders → `pages/orders.blade.php`
- [ ] Transactions → `pages/transactions.blade.php`
- [ ] Reports → `pages/reports.blade.php`
- [ ] Users → `pages/users.blade.php`
- [ ] Settings → `pages/settings.blade.php`
- [ ] ... (30+ more pages)

### JavaScript to Port
- [ ] `api.js` → `resources/js/services/api.js`
- [ ] `auth.js` → `resources/js/services/auth.js`
- [ ] `barcode-service.js` → `resources/js/services/barcode.js`
- [ ] `store.js` → `resources/js/store.js`
- [ ] `print-utils.js` → `resources/js/utils/print.js`
- [ ] Chart initialization → `resources/js/components/charts.js`

### Images & Assets
- [ ] All brand logos → `public/images/`
- [ ] Icons → `public/images/icons/`
- [ ] Product images (samples) → `public/images/products/`

---

**Status**: Frontend Foundation Ready ✅  
**Next**: Convert HTML → Blade templates  
**Then**: Port JavaScript services  
**Finally**: Implement backend logic & database
