# SAGA POS JavaScript Services

## Overview
Four core services for managing API calls, authentication, barcode scanning, and global application state.

## Services

### 1. API Service (`resources/js/services/api.js`)
HTTP client for all API requests with automatic CSRF token and Bearer token handling.

#### Usage
```javascript
import { api } from './services/index.js';

// GET request
const products = await api.get('/api/products', { page: 1, limit: 10 });

// POST request
const response = await api.post('/api/sales', { 
  customer_id: 1, 
  items: [...], 
  total: 50000 
});

// PUT request
await api.put('/api/products/1', { name: 'Updated Name' });

// PATCH request
await api.patch('/api/products/1', { price: 99999 });

// DELETE request
await api.delete('/api/products/1');

// File upload
const formData = new FormData();
formData.append('file', fileInput.files[0]);
await api.upload('/api/products/import', formData);
```

#### Key Features
- ✅ Automatic CSRF token injection
- ✅ Bearer token authentication
- ✅ Request timeout (30 seconds default)
- ✅ Error handling with status codes
- ✅ Token refresh on 401 (unauthorized)
- ✅ File upload support
- ✅ Query parameter serialization

#### Error Handling
```javascript
try {
  const data = await api.get('/api/products');
} catch (error) {
  if (error.message.includes('401')) {
    // Token expired, redirect to login
  } else if (error.message.includes('403')) {
    // Permission denied
  } else if (error.message.includes('422')) {
    // Validation errors - parse JSON
    const errors = JSON.parse(error.message);
  }
}
```

---

### 2. Authentication Service (`resources/js/services/auth.js`)
Manages user login, registration, logout, and session state.

#### Usage
```javascript
import { auth } from './services/index.js';

// Login
const result = await auth.login('user@example.com', 'password');
if (result.success) {
  console.log('Logged in as:', result.user.name);
}

// Register
const registerResult = await auth.register({
  name: 'John Doe',
  email: 'john@example.com',
  password: 'password123',
  password_confirmation: 'password123'
});

// Check authentication
if (auth.isAuthenticated()) {
  const user = auth.getCurrentUser();
  console.log('User:', user.name);
}

// Check roles and permissions
if (auth.hasRole('cashier')) {
  // Show cashier features
}

if (auth.hasPermission('sell')) {
  // Show sell button
}

if (auth.isSuperAdmin()) {
  // Show admin panel
}

// Change password
await auth.changePassword('oldPassword', 'newPassword', 'newPassword');

// Logout
auth.logout(); // Redirects to /login
```

#### Key Features
- ✅ Login/Register/Logout
- ✅ Password change & reset
- ✅ Role checking (super_admin, tenant_owner, manager, cashier, sales_staff)
- ✅ Permission checking
- ✅ User profile management
- ✅ Automatic token storage

#### User Roles
- `super_admin` - System administrator
- `tenant_owner` - Store owner
- `manager` - Store manager
- `cashier` - Point of sale cashier
- `sales_staff` - Sales representative

---

### 3. Barcode Service (`resources/js/services/barcode.js`)
Detects barcode scanner input and optionally QR code scanning via camera.

#### Keyboard Scanner (USB barcode scanner)
```javascript
import { barcode } from './services/index.js';

// Initialize scanner
barcode.init();

// Listen for scans
document.addEventListener('scan', (event) => {
  console.log('Barcode scanned:', event.detail.code);
  addProductToCart(event.detail.code);
});

// Stop scanner
barcode.destroy();
```

#### QR Code Scanner (Camera)
```javascript
// Start camera-based QR scanning
await barcode.startQrScanner('qr-scanner'); // Element ID where video will be displayed

// Listen for QR scans
document.addEventListener('qr:scan', (event) => {
  console.log('QR code scanned:', event.detail.code);
});

// Stop QR scanner
await barcode.stopQrScanner();

// Check if QR scanner is available
if (barcode.isQrScannerAvailable()) {
  console.log('QR scanning supported');
}
```

#### Barcode Filtering
```javascript
// Only accept specific barcode prefixes
barcode.setAcceptedPrefixes(['8934', '4927']); // Only EAN-13 codes from certain manufacturers

// Listen for invalid scans
document.addEventListener('scan:invalid', (event) => {
  console.log('Invalid barcode:', event.detail.code);
});
```

#### Key Features
- ✅ USB barcode scanner detection
- ✅ QR code scanning via camera
- ✅ Barcode prefix filtering
- ✅ Timing-based scanner vs. manual typing detection
- ✅ Custom events for scan handling
- ✅ Backward compatible events

---

### 4. Store Service (`resources/js/services/store.js`)
Global application state management with localStorage persistence.

#### Usage
```javascript
import { store } from './services/index.js';

// User management
store.setUser(userData);
const user = store.getUser();
console.log('User:', user.name);

// Tenant management
store.setTenant(tenantData);
const tenant = store.getTenant();

// Token management
store.setToken(jwtToken);
const hasToken = !!store.getToken();

// Authentication checks
if (store.isAuthenticated()) {
  console.log('User is logged in');
}

// Role checks
if (store.hasRole('super_admin')) {
  // Show super admin features
}

if (store.isSuperAdmin()) {
  // Alternative syntax
}

// Permission checks
if (store.hasPermission('delete_user')) {
  showDeleteButton();
}

// Branch/Location management
store.setBranches([...]);
const branches = store.getBranches();
store.setSelectedBranch(branch);

// Dark mode
store.toggleDarkMode();
store.setDarkMode(true);
if (store.isDarkMode()) {
  // Apply dark styles
}

// Sidebar state
store.toggleSidebar();
store.setSidebarCollapsed(true);

// Get all state
const state = store.getState();
console.log('Full app state:', state);

// Clear state (logout)
store.clear();
```

#### Alpine.js Integration
```html
<div x-data="{ user: $store.app.user }">
  <p x-text="user?.name"></p>
</div>
```

#### Key Features
- ✅ User & tenant state
- ✅ JWT token management
- ✅ Dark mode toggle
- ✅ Sidebar state
- ✅ Branch selection
- ✅ localStorage persistence
- ✅ Alpine.js store integration

---

## Global Access
All services are available globally on the window object:

```javascript
// In inline scripts or console
window.ApiService.get('/api/products');
window.AuthService.login('email@example.com', 'password');
window.BarcodeService.init();
window.SagaStore.getUser();
```

## Event System

### API Events
- `scanner:init` - Barcode scanner initialized

### Barcode Events
- `scan` - Barcode scanned (via keyboard)
- `scan:invalid` - Invalid barcode detected
- `qr:scan` - QR code scanned
- `qr:start` - QR scanner started
- `qr:stop` - QR scanner stopped
- `qr:error` - QR scanner error

### Listening to Events
```javascript
document.addEventListener('scan', (event) => {
  console.log('Scanned code:', event.detail.code);
});

document.addEventListener('qr:scan', (event) => {
  console.log('QR code:', event.detail.code);
});
```

## Integration Examples

### Complete Login Flow
```javascript
// 1. User submits login form
const loginResult = await auth.login(email, password);

if (loginResult.success) {
  // 2. Store updates automatically via auth service
  // 3. Redirect to dashboard
  window.location.href = '/dashboard';
} else {
  // Show error message
  showError(loginResult.message);
}
```

### POS Sale with Barcode Scanner
```javascript
// 1. Initialize barcode scanner
barcode.init();

// 2. Listen for scans
document.addEventListener('scan', async (event) => {
  const code = event.detail.code;
  
  // 3. Fetch product from API
  const response = await api.get('/api/products', { barcode: code });
  const product = response.data;
  
  // 4. Add to cart
  addToCart(product);
});

// 5. Complete sale
async function completeSale(cart) {
  const response = await api.post('/api/sales', {
    items: cart.items,
    total: cart.total,
    payment_method: 'cash'
  });
  
  if (response.success) {
    showSuccess('Sale completed');
    clearCart();
  }
}
```

### Multi-Tenant Branch Switching
```javascript
// 1. Get available branches for user
const branches = await api.get('/api/branches');
store.setBranches(branches.data);

// 2. User selects branch
async function switchBranch(branch) {
  store.setSelectedBranch(branch);
  
  // 3. Reload dashboard data for new branch
  const dashboard = await api.get('/api/reports/dashboard', {
    branch_id: branch.id
  });
  
  // 4. Update UI
  updateDashboard(dashboard.data);
}
```

## Error Handling Best Practices

```javascript
try {
  const response = await api.post('/api/sales', saleData);
  
  if (response.success) {
    // Handle success
  } else {
    // Handle business logic error
    showError(response.message);
  }
} catch (error) {
  // Handle network/parsing error
  if (error.message.includes('401')) {
    // Unauthorized - redirect to login
    auth.logout();
  } else if (error.message.includes('422')) {
    // Validation error
    const errors = JSON.parse(error.message);
    showValidationErrors(errors);
  } else {
    showError('Something went wrong. Please try again.');
  }
}
```

## CSRF Protection
CSRF tokens are automatically injected into all requests from the `<meta name="csrf-token">` tag in your layout.

## API Response Format
All responses should follow this format:

```javascript
{
  success: true,
  message: "Operation successful",
  data: { ... }
}
```

Error responses:

```javascript
{
  success: false,
  message: "Error description",
  errors: { field: ["Error message"] } // For 422 validation errors
}
```

## Configuration

### Change API Base URL
```javascript
// In your application initialization
window.ApiService.baseUrl = 'https://api.example.com';
```

### Change Request Timeout
```javascript
window.ApiService.timeout = 60000; // 60 seconds
```

### Change Barcode Settings
```javascript
window.BarcodeService.minLength = 8; // Minimum barcode length
window.BarcodeService.threshold = 100; // Time threshold for scanner detection
window.BarcodeService.setAcceptedPrefixes(['8934', '4927']);
```

## Testing Services

```javascript
// Test API
await window.ApiService.get('/api/test');

// Test Auth
const loginResult = await window.AuthService.login('test@example.com', 'password');

// Test Barcode (simulate scan)
window.BarcodeService.emitScan('123456789');

// Check Store State
window.SagaStore.logState();
```

## Migration from Old Services
Old API format:

```javascript
// Before
await fetch('/api/products', {
  headers: { 'Authorization': 'Bearer ' + token }
});

// After
import { api } from './services/index.js';
await api.get('/api/products');
```

All token and CSRF handling is now automatic!

## Debugging
Enable detailed logging:

```javascript
// Check service state
console.log('Auth:', window.AuthService.getCurrentUser());
console.log('Store:', window.SagaStore.getState());
console.log('Token:', window.ApiService.getToken());

// Monitor API calls in browser DevTools Network tab
// All requests will show proper headers and response format
```
