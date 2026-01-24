/**
 * SAGA POS - Services Index
 * Central export point for all application services
 */

export { default as api, ApiService } from './api.js';
export { default as auth, AuthService } from './auth.js';
export { default as barcode, BarcodeService } from './barcode.js';
export { default as store, StoreService } from './store.js';

// For backward compatibility with old imports
export { default } from './api.js';
