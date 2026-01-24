require('dotenv').config();
const express = require('express');
const cors = require('cors');

// Import routes
const authRoutes = require('./routes/auth');
const licenseRoutes = require('./routes/license');
const LicenseService = require('./services/LicenseService');
const { initBackupScheduler } = require('./services/BackupScheduler');
const adminTenantRoutes = require('./routes/admin/tenants');
const adminUserRoutes = require('./routes/admin/users');
const adminAnalyticsRoutes = require('./routes/admin/analytics');
const tenantProductRoutes = require('./routes/tenant/products');
const tenantTransactionRoutes = require('./routes/tenant/transactions');
const tenantReportRoutes = require('./routes/tenant/reports');
const tenantExportRoutes = require('./routes/tenant/export');
const tenantImportRoutes = require('./routes/tenant/import');
const tenantBackupRoutes = require('./routes/tenant/backup');
const tenantBranchRoutes = require('./routes/tenant/branches');
const tenantTransferRoutes = require('./routes/tenant/transfers');
const tenantUserRoutes = require('./routes/tenant/users');
const tenantSupplierRoutes = require('./routes/tenant/suppliers');
const tenantReturnRoutes = require('./routes/tenant/returns');
const tenantPurchaseRoutes = require('./routes/tenant/purchases');
const tenantPurchaseReturnRoutes = require('./routes/tenant/purchase-returns');
const tenantCustomerRoutes = require('./routes/tenant/customers');
const tenantDebtRoutes = require('./routes/tenant/debts');
const tenantNotificationRoutes = require('./routes/tenant/notifications');
const tenantSalesmanRoutes = require('./routes/tenant/salesmen');
const tenantUploadRoutes = require('./routes/tenant/upload');

const tenantProfileRoutes = require('./routes/tenant/profile');
const tenantSettingsRoutes = require('./routes/tenant/settings');
const updatesRoutes = require('./routes/updates');
const setupRoutes = require('./routes/setup');

// Import middleware
const { authenticateToken } = require('./middleware/auth');
const { resolveTenant } = require('./middleware/tenantResolver');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve uploaded files statically
const path = require('path');
// Serve Static Frontend (Production)
// Pointing to 'build' folder in root
app.use(express.static(path.join(__dirname, '../build')));

// Serve Uploaded Files
app.use('/uploads', express.static(path.join(__dirname, 'public/uploads')));

// Public routes
app.use('/api/license', licenseRoutes);
app.use('/api/auth', authRoutes);
app.use('/api/updates', updatesRoutes);
app.use('/api/setup', setupRoutes);

// GLOBAL LICENSE CHECK MIDDLEWARE
const licenseCheckMiddleware = async (req, res, next) => {
  // Skip check for license/auth/setup routes or static files
  if (req.path.startsWith('/api/license') ||
    req.path.startsWith('/api/auth') ||
    req.path.startsWith('/api/setup') ||
    req.path.startsWith('/api/updates') ||
    !req.path.startsWith('/api')) {
    return next();
  }

  // Verify license
  const check = await LicenseService.verifyLicense();
  if (!check.valid) {
    return res.status(403).json({
      success: false,
      message: 'LICENSE_REQUIRED',
      reason: check.reason
    });
  }
  next();
};

app.use(licenseCheckMiddleware);

const adminLicenseRoutes = require('./routes/admin/license');

// Protected routes - Super Admin
app.use('/api/admin/tenants', authenticateToken, adminTenantRoutes);
app.use('/api/admin/users', authenticateToken, adminUserRoutes);
app.use('/api/admin/analytics', authenticateToken, adminAnalyticsRoutes);
app.use('/api/admin/license', authenticateToken, adminLicenseRoutes);

// Protected routes - Tenant (with tenant DB resolution)
app.use('/api/products', authenticateToken, resolveTenant, tenantProductRoutes);
app.use('/api/transactions', authenticateToken, resolveTenant, tenantTransactionRoutes);
app.use('/api/reports', authenticateToken, resolveTenant, tenantReportRoutes);
app.use('/api/export', authenticateToken, resolveTenant, tenantExportRoutes);
app.use('/api/import', authenticateToken, resolveTenant, tenantImportRoutes);
app.use('/api/backup', authenticateToken, resolveTenant, tenantBackupRoutes);
app.use('/api/branches', authenticateToken, resolveTenant, tenantBranchRoutes);
app.use('/api/transfers', authenticateToken, resolveTenant, tenantTransferRoutes);
app.use('/api/users', authenticateToken, resolveTenant, tenantUserRoutes);
app.use('/api/suppliers', authenticateToken, resolveTenant, tenantSupplierRoutes);
app.use('/api/returns', authenticateToken, resolveTenant, tenantReturnRoutes);
app.use('/api/purchases', authenticateToken, resolveTenant, tenantPurchaseRoutes);
app.use('/api/purchase-returns', authenticateToken, resolveTenant, tenantPurchaseReturnRoutes);
app.use('/api/customers', authenticateToken, resolveTenant, tenantCustomerRoutes);
app.use('/api/debts', authenticateToken, resolveTenant, tenantDebtRoutes);
app.use('/api/notifications', authenticateToken, resolveTenant, tenantNotificationRoutes);
app.use('/api/salesmen', authenticateToken, resolveTenant, tenantSalesmanRoutes);
app.use('/api/tenant-profile', authenticateToken, tenantProfileRoutes);
app.use('/api/settings', authenticateToken, resolveTenant, tenantSettingsRoutes);
app.use('/api/upload', authenticateToken, resolveTenant, tenantUploadRoutes);

// Health check endpoint
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Serve index.html for non-API routes (SPA support if needed, or just static mapping)
app.get('*', (req, res, next) => {
  // If request is for API, skip to error handler
  if (req.path.startsWith('/api')) {
    return next();
  }
  // Otherwise serve index.html (or signin.html as entry)
  res.sendFile(path.join(__dirname, '../build', 'index.html'));
});

// Error handling middleware
// Error handling middleware
app.use((err, req, res, next) => {
  console.error(err.stack);

  // DEBUG: Write error to file
  const fs = require('fs');
  const path = require('path');
  const logPath = path.join(__dirname, 'server_error.log');
  const logContent = `[${new Date().toISOString()}] ${req.method} ${req.url}\n${err.message}\n${err.stack}\n\n`;
  try { fs.appendFileSync(logPath, logContent); } catch (e) { console.error('Failed to write log', e); }

  res.status(500).json({
    success: false,
    message: err.message, // SHOW REAL ERROR TO USER FOR DEBUGGING
    stack: process.env.NODE_ENV === 'development' ? err.stack : undefined
  });
});

// Serve Frontend (bundled build folder)
const buildPath = path.join(__dirname, '../build');
if (require('fs').existsSync(buildPath)) {
  console.log('Serving frontend from:', buildPath);
  app.use(express.static(buildPath));

  // SPA Fallback
  app.get('*', (req, res) => {
    if (req.url.startsWith('/api')) {
      return res.status(404).json({ success: false, message: 'API Endpoint not found' });
    }
    res.sendFile(path.join(buildPath, 'index.html'));
  });
} else {
  console.error('Frontend build folder not found at:', buildPath);
}

// Global Error Handler
app.use((err, req, res, next) => {
  console.error('SERVER ERROR:', err.stack);
  const logPath = path.join(__dirname, 'server_error.log');
  const logContent = `[${new Date().toISOString()}] ${req.method} ${req.url}\n${err.message}\n${err.stack}\n\n`;
  try { fs.appendFileSync(logPath, logContent); } catch (e) { }

  res.status(500).json({
    success: false,
    message: err.message
  });
});

app.listen(PORT, () => {
  console.log(`🚀 SAGA TOKO API Server running on port ${PORT}`);
  console.log(`📊 Environment: ${process.env.NODE_ENV}`);

  // DEBUG: Write startup timestamp to prove new code is running
  try {
    const fs = require('fs');
    const path = require('path');
    fs.writeFileSync(path.join(__dirname, 'server_startup.log'), `Server started at: ${new Date().toLocaleString()}\n`);
    console.log('📝 Startup timestamp written to server_startup.log');
  } catch (e) { console.error('Failed to write startup log:', e); }

  // Initialize backup scheduler
  initBackupScheduler();
});

module.exports = app;
