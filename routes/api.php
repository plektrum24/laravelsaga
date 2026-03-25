<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeController;

// Public Routes
Route::post('/login', [AuthController::class , 'login']);
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthorized'], 401);
})->name('login');

// Auth-only routes (without tenant middleware)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('/products/actions/delete-all', [ProductController::class , 'destroyAll']);
    
    // Tenant Portal (Self-Service)
    Route::prefix('tenant/portal')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\TenantPortalController::class , 'dashboard']);
        Route::get('/plans', [\App\Http\Controllers\Api\TenantPortalController::class , 'availablePlans']);
        Route::post('/plan/change', [\App\Http\Controllers\Api\TenantPortalController::class , 'changePlan']);
        Route::get('/billing/history', [\App\Http\Controllers\Api\TenantPortalController::class , 'billingHistory']);
        Route::get('/billing/invoice/{id}', [\App\Http\Controllers\Api\TenantPortalController::class , 'invoiceDetail']);
        Route::post('/billing/invoice/{id}/pay', [\App\Http\Controllers\Api\TenantPortalController::class , 'payInvoice']);
        Route::get('/billing/payment-methods', [\App\Http\Controllers\Api\TenantPortalController::class , 'paymentMethods']);
        Route::get('/usage', [\App\Http\Controllers\Api\TenantPortalController::class , 'usage']);
        Route::get('/support/tickets', [\App\Http\Controllers\Api\TenantPortalController::class , 'supportTickets']);
        Route::post('/support/tickets', [\App\Http\Controllers\Api\TenantPortalController::class , 'createTicket']);
        Route::get('/support/tickets/{id}', [\App\Http\Controllers\Api\TenantPortalController::class , 'ticketDetail']);
        Route::post('/support/tickets/{id}/reply', [\App\Http\Controllers\Api\TenantPortalController::class , 'replyToTicket']);
    });
});

// Protected Routes
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class , 'logout']);
    Route::get('/auth/me', [AuthController::class , 'me']);
    Route::get('/user', [AuthController::class , 'me']);

    // Menus
    Route::get('/user/menus', [\App\Http\Controllers\Api\MenuController::class , 'getMenus']);
    
    // Tenant Info
    Route::get('/tenant/info', [\App\Http\Controllers\Api\AuthController::class , 'tenantInfo']);
    
    // Notifications (Mock)
    Route::get('/notifications', function () {
            return response()->json(['success' => true, 'data' => []]);
        }
        );

        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class , 'stats']);

        // Branch Management
        Route::prefix('branches')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\BranchController::class , 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Api\BranchController::class , 'show']);
            Route::post('/', [\App\Http\Controllers\Api\BranchController::class , 'store']);
            Route::put('/{id}', [\App\Http\Controllers\Api\BranchController::class , 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\BranchController::class , 'destroy']);
            Route::get('/statistics', [\App\Http\Controllers\Api\BranchController::class , 'statistics']);
        });

        // Products & Inventory
        // Note: 'products/categories' resource must come BEFORE 'products' resource if using same prefix, 
        // or just ensure no conflict. Here it is fine.
        // We replace the simple 'categories' helper with full resource.
        Route::apiResource('products/categories', \App\Http\Controllers\Api\CategoryController::class);

        // Units Helper
        Route::get('/products/units', [ProductController::class , 'units']);
        Route::post('/products/units', [ProductController::class , 'storeUnit']);
        Route::get('/products/generate-sku/{categoryId}', [ProductController::class , 'generateSku']);

        // Inventory Management
        Route::get('/products/expiry', [\App\Http\Controllers\Api\InventoryController::class , 'expiry']);
        Route::post('/products/adjust-stock/{id}', [\App\Http\Controllers\Api\InventoryController::class , 'adjustStock']);
        Route::post('/products/reset-all-stock', [\App\Http\Controllers\Api\InventoryController::class , 'resetAllStock']);
        // Inventory Exports
        Route::get('/inventory/export/excel', [\App\Http\Controllers\Api\InventoryController::class , 'exportExcel']);
        Route::get('/inventory/export/pdf', [\App\Http\Controllers\Api\InventoryController::class , 'exportPdf']);
        Route::get('/inventory/adjustments/export', [\App\Http\Controllers\Api\InventoryController::class , 'exportAdjustments']);

        // Purchases (Goods In) - For Receiving History
        Route::get('/purchases', [\App\Http\Controllers\Api\PurchaseController::class , 'index']);
        Route::get('/purchases/{id}', [\App\Http\Controllers\Api\PurchaseController::class , 'show']);
        Route::post('/purchases', [\App\Http\Controllers\Api\PurchaseController::class , 'store']);
        Route::post('/purchases/{id}', [\App\Http\Controllers\Api\PurchaseController::class , 'update']);
        Route::delete('/purchases/{id}', [\App\Http\Controllers\Api\PurchaseController::class , 'destroy']);
        Route::get('/purchases/{id}/receipt', [\App\Http\Controllers\Api\PurchaseController::class , 'printReceipt']);
        // Purchases Export
        Route::get('/purchases/export/excel', [\App\Http\Controllers\Api\PurchaseController::class , 'exportExcel']);
        Route::get('/purchases/export/pdf', [\App\Http\Controllers\Api\PurchaseController::class , 'exportPdf']);

        // Core Resources
        Route::get('product-exports/excel', [ProductController::class , 'exportExcel']);
        Route::get('product-exports/pdf', [ProductController::class , 'exportPdf']);
        Route::get('product-exports/template', [ProductController::class , 'downloadTemplate']);
        Route::post('products/import', [ProductController::class , 'import']);
        // delete-all moved to auth-only group above
        Route::apiResource('products', ProductController::class);
        
        // Deadstock Analytics
        Route::get('/products/deadstock', [ProductController::class , 'deadstock']);
        Route::get('/products/deadstock/export', [ProductController::class , 'exportDeadstock']);
        
        // Pricing Tiers
        Route::get('/products/{product}/pricing-tiers', [ProductController::class , 'getPricingTiers']);
        Route::post('/products/calculate-price', [ProductController::class , 'calculatePrice']);
        
        Route::apiResource('customers', CustomerController::class);

        // Transactions / POS
        Route::get('transactions/{transaction}/receipt', [\App\Http\Controllers\Api\TransactionExportController::class , 'downloadReceipt']);
        Route::apiResource('transactions', TransactionController::class);

        // Users (for tenant - fetch cashiers, employees, etc.)
        Route::get('users', [\App\Http\Controllers\Api\UserController::class , 'index']);
        Route::get('users/cashiers', [\App\Http\Controllers\Api\UserController::class , 'cashiers']);

        // Employees & Payroll
        Route::get('employees/{employee}/payroll-preview', [\App\Http\Controllers\Api\PayrollController::class , 'preview']);
        Route::get('payrolls/bulk-preview', [\App\Http\Controllers\Api\PayrollController::class , 'bulkPreview']);
        Route::post('payrolls/bulk', [\App\Http\Controllers\Api\PayrollController::class , 'bulkStore']);

        // Exports
        Route::get('payrolls/{payroll}/pdf', [\App\Http\Controllers\Api\PayrollExportController::class , 'downloadPdf']);
        Route::get('payrolls/export/excel', [\App\Http\Controllers\Api\PayrollExportController::class , 'exportExcel']);

        Route::apiResource('employees', EmployeeController::class);
        Route::apiResource('payrolls', \App\Http\Controllers\Api\PayrollController::class)->only(['index', 'store']);

        // Reports
        Route::get('/reports/assets', [\App\Http\Controllers\Api\ReportController::class , 'assets']);
        Route::get('/reports/sales-overview', [\App\Http\Controllers\Api\ReportController::class , 'salesOverview']);
        Route::get('/reports/top-products', [\App\Http\Controllers\Api\ReportController::class , 'topProducts']);
        Route::get('/reports/category-performance', [\App\Http\Controllers\Api\ReportController::class , 'categoryPerformance']);
        Route::get('/reports/inventory-movements', [\App\Http\Controllers\Api\ReportController::class , 'inventoryMovements']);
        Route::get('/reports/dashboard', [\App\Http\Controllers\Api\ReportController::class , 'dashboard']);
        Route::get('/products/reports/price-logs', [\App\Http\Controllers\Api\ReportController::class , 'priceLogs']);

        // Real-time Analytics (Phase 30)
        Route::prefix('analytics')->group(function () {
            Route::get('/realtime', [\App\Http\Controllers\Api\Analytics\RealtimeController::class , 'index']);
            Route::get('/sales/live', [\App\Http\Controllers\Api\Analytics\RealtimeController::class , 'liveSales']);
            Route::get('/users/active', [\App\Http\Controllers\Api\Analytics\RealtimeController::class , 'activeUsers']);
            Route::get('/revenue/today', [\App\Http\Controllers\Api\Analytics\RealtimeController::class , 'revenueToday']);
            Route::get('/stats/hourly', [\App\Http\Controllers\Api\Analytics\RealtimeController::class , 'hourlyStats']);
            Route::get('/products/top', [\App\Http\Controllers\Api\Analytics\RealtimeController::class , 'topProducts']);
        });

        // Report Builder (Phase 30)
        Route::prefix('reports')->group(function () {
            Route::get('/sales', [\App\Http\Controllers\Api\Analytics\ReportBuilderController::class , 'salesReport']);
            Route::get('/inventory', [\App\Http\Controllers\Api\Analytics\ReportBuilderController::class , 'inventoryReport']);
            Route::get('/customers', [\App\Http\Controllers\Api\Analytics\ReportBuilderController::class , 'customerReport']);
            Route::post('/export/excel', [\App\Http\Controllers\Api\Analytics\ReportBuilderController::class , 'exportExcel']);
        });

        // Forecasting (Phase 30)
        Route::prefix('forecasting')->group(function () {
            Route::get('/sales', [\App\Http\Controllers\Api\Analytics\ForecastingController::class , 'salesForecast']);
            Route::get('/trend', [\App\Http\Controllers\Api\Analytics\ForecastingController::class , 'salesTrend']);
            Route::get('/inventory', [\App\Http\Controllers\Api\Analytics\ForecastingController::class , 'inventoryForecast']);
            Route::get('/categories', [\App\Http\Controllers\Api\Analytics\ForecastingController::class , 'categoryForecast']);
        });

        // Customer Analytics (Phase 30)
        Route::prefix('customers')->group(function () {
            Route::get('/segmentation', [\App\Http\Controllers\Api\Analytics\CustomerAnalyticsController::class , 'segmentation']);
            Route::get('/lifetime-value', [\App\Http\Controllers\Api\Analytics\CustomerAnalyticsController::class , 'lifetimeValue']);
            Route::get('/churn-risk', [\App\Http\Controllers\Api\Analytics\CustomerAnalyticsController::class , 'churnRisk']);
            Route::get('/journey', [\App\Http\Controllers\Api\Analytics\CustomerAnalyticsController::class , 'journey']);
        });

        // Performance Monitoring (Phase 30 - Wave 3)
        Route::prefix('performance')->group(function () {
            Route::get('/summary', [\App\Http\Controllers\Api\Performance\PerformanceController::class , 'summary']);
            Route::get('/database/stats', [\App\Http\Controllers\Api\Performance\PerformanceController::class , 'databaseStats']);
            Route::get('/database/slow-queries', [\App\Http\Controllers\Api\Performance\PerformanceController::class , 'slowQueries']);
            Route::get('/database/indexes', [\App\Http\Controllers\Api\Performance\PerformanceController::class , 'missingIndexes']);
            Route::post('/database/optimize', [\App\Http\Controllers\Api\Performance\PerformanceController::class , 'optimizeTables']);
            Route::get('/cache/stats', [\App\Http\Controllers\Api\Performance\PerformanceController::class , 'cacheStats']);
            Route::post('/cache/warmup', [\App\Http\Controllers\Api\Performance\PerformanceController::class , 'warmupCache']);
            Route::post('/cache/clear', [\App\Http\Controllers\Api\Performance\PerformanceController::class , 'clearCache']);
        });

        // Sales Trends & Analytics
        Route::prefix('sales-trends')->group(function () {
            Route::get('/trend', [\App\Http\Controllers\Api\SalesTrendController::class , 'trend']);
            Route::get('/by-category', [\App\Http\Controllers\Api\SalesTrendController::class , 'byCategory']);
            Route::get('/top-products', [\App\Http\Controllers\Api\SalesTrendController::class , 'topProducts']);
            Route::get('/hourly-pattern', [\App\Http\Controllers\Api\SalesTrendController::class , 'hourlyPattern']);
        });

        // Sales Force Reports
        Route::prefix('reports/sales-force')->group(function () {
            Route::get('/performance', [\App\Http\Controllers\Api\SalesForceReportController::class , 'performance']);
            Route::get('/performance/{salesmanId}', [\App\Http\Controllers\Api\SalesForceReportController::class , 'salesmanPerformance']);
            Route::get('/export', [\App\Http\Controllers\Api\SalesForceReportController::class , 'export']);
        });

        // Sales Orders
        Route::prefix('sales-orders')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\SalesController::class , 'index']);
            Route::post('/', [\App\Http\Controllers\Api\SalesController::class , 'store']);
            Route::get('/statistics', [\App\Http\Controllers\Api\SalesController::class , 'statistics']);
            Route::get('/{id}', [\App\Http\Controllers\Api\SalesController::class , 'show']);
            Route::put('/{id}', [\App\Http\Controllers\Api\SalesController::class , 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\SalesController::class , 'destroy']);
        });

        // Loyalty Program
        Route::prefix('loyalty')->group(function () {
            Route::get('/settings', [\App\Http\Controllers\Api\LoyaltyController::class , 'settings']);
            Route::post('/settings', [\App\Http\Controllers\Api\LoyaltyController::class , 'updateSettings']);
            Route::post('/calculate', [\App\Http\Controllers\Api\LoyaltyController::class , 'calculate']);
            Route::post('/redeem', [\App\Http\Controllers\Api\LoyaltyController::class , 'redeem']);
        });
        Route::get('/customers/{customer}/points', [\App\Http\Controllers\Api\LoyaltyController::class , 'customerPoints']);
        Route::get('/customers/{customer}/points/history', [\App\Http\Controllers\Api\LoyaltyController::class , 'pointsHistory']);

        // Membership Tiers
        Route::prefix('tiers')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\TierController::class , 'index']);
            Route::post('/', [\App\Http\Controllers\Api\TierController::class , 'store']);
            Route::post('/calculate-progress', [\App\Http\Controllers\Api\TierController::class , 'calculateProgress']);
        });
        Route::get('/customers/{customer}/tier', [\App\Http\Controllers\Api\TierController::class , 'customerTier']);
        Route::post('/customers/{customer}/assess-tier', [\App\Http\Controllers\Api\TierController::class , 'assessCustomer']);

        // Rewards
        Route::prefix('rewards')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\RewardController::class , 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Api\RewardController::class , 'show']);
            Route::post('/', [\App\Http\Controllers\Api\RewardController::class , 'store']);
            Route::put('/{id}', [\App\Http\Controllers\Api\RewardController::class , 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\RewardController::class , 'destroy']);
            Route::post('/redeem', [\App\Http\Controllers\Api\RewardController::class , 'redeem']);
            Route::post('/customer-rewards/{id}/fulfill', [\App\Http\Controllers\Api\RewardController::class , 'fulfillReward']);
        });
        Route::get('/customers/{customer}/rewards', [\App\Http\Controllers\Api\RewardController::class , 'customerRewards']);

        // Stock Transfers
        Route::prefix('stock-transfers')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\StockTransferController::class , 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Api\StockTransferController::class , 'show']);
            Route::post('/', [\App\Http\Controllers\Api\StockTransferController::class , 'store']);
            Route::put('/{id}', [\App\Http\Controllers\Api\StockTransferController::class , 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\StockTransferController::class , 'destroy']);
            Route::post('/{id}/submit', [\App\Http\Controllers\Api\StockTransferController::class , 'submit']);
            Route::post('/{id}/approve', [\App\Http\Controllers\Api\StockTransferController::class , 'approve']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\StockTransferController::class , 'reject']);
            Route::post('/{id}/ship', [\App\Http\Controllers\Api\StockTransferController::class , 'ship']);
            Route::post('/{id}/receive', [\App\Http\Controllers\Api\StockTransferController::class , 'receive']);

            // Export/Print
            Route::get('/{id}/print', [\App\Http\Controllers\Api\StockTransferExportController::class , 'printTransferOrder']);
            Route::get('/{id}/print-receipt', [\App\Http\Controllers\Api\StockTransferExportController::class , 'printReceipt']);

            // Analytics & Reports
            Route::get('/analytics/dashboard', [\App\Http\Controllers\Api\StockTransferController::class , 'dashboard']);
            Route::get('/reports/in-transit', [\App\Http\Controllers\Api\StockTransferController::class , 'inTransitReport']);
            Route::get('/reports/history', [\App\Http\Controllers\Api\StockTransferController::class , 'historyReport']);
            Route::get('/reports/branch-comparison', [\App\Http\Controllers\Api\StockTransferController::class , 'branchComparison']);
        });

        // Debt Management (Supplier Debts / Accounts Payable)
        Route::prefix('debts')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\DebtPaymentController::class , 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Api\DebtPaymentController::class , 'show']);
            Route::post('/{id}/pay', [\App\Http\Controllers\Api\DebtPaymentController::class , 'pay']);
            Route::get('/payments/history', [\App\Http\Controllers\Api\DebtPaymentController::class , 'paymentHistory']);
            Route::get('/statistics', [\App\Http\Controllers\Api\DebtPaymentController::class , 'statistics']);
        });

        // Barcodes
        Route::prefix('barcodes')->group(function () {
            Route::get('/products/{productId}', [\App\Http\Controllers\Api\BarcodeController::class , 'getProductBarcodes']);
            Route::post('/products/{productId}/generate', [\App\Http\Controllers\Api\BarcodeController::class , 'generateForProduct']);
            Route::post('/generate-bulk', [\App\Http\Controllers\Api\BarcodeController::class , 'generateBulk']);
            Route::get('/{barcode}/image', [\App\Http\Controllers\Api\BarcodeController::class , 'getImage']);
            Route::get('/{barcode}/html', [\App\Http\Controllers\Api\BarcodeController::class , 'getHTML']);
            Route::get('/lookup', [\App\Http\Controllers\Api\BarcodeController::class , 'lookup']);
            Route::post('/{id}/set-primary', [\App\Http\Controllers\Api\BarcodeController::class , 'setPrimary']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\BarcodeController::class , 'destroy']);
        });

        // Label Templates
        Route::prefix('label-templates')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\LabelTemplateController::class , 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Api\LabelTemplateController::class , 'show']);
            Route::post('/', [\App\Http\Controllers\Api\LabelTemplateController::class , 'store']);
            Route::put('/{id}', [\App\Http\Controllers\Api\LabelTemplateController::class , 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\LabelTemplateController::class , 'destroy']);
            Route::post('/create-defaults', [\App\Http\Controllers\Api\LabelTemplateController::class , 'createDefaults']);
            Route::post('/preview', [\App\Http\Controllers\Api\LabelTemplateController::class , 'preview']);
            Route::post('/print', [\App\Http\Controllers\Api\LabelTemplateController::class , 'print']);
            Route::post('/quick-print-barcode', [\App\Http\Controllers\Api\LabelTemplateController::class , 'quickPrintBarcode']);
            Route::post('/quick-print-price-tag', [\App\Http\Controllers\Api\LabelTemplateController::class , 'quickPrintPriceTag']);
            Route::get('/printers', [\App\Http\Controllers\Api\LabelTemplateController::class , 'getPrinters']);
            Route::post('/test-printer', [\App\Http\Controllers\Api\LabelTemplateController::class , 'testPrinter']);
            Route::get('/{id}/render', [\App\Http\Controllers\Api\LabelTemplateController::class , 'render']);
            Route::get('/print-history', [\App\Http\Controllers\Api\LabelTemplateController::class , 'printHistory']);
        });

        // E-Commerce (Public)
        Route::prefix('web')->group(function () {
            Route::get('/products', [\App\Http\Controllers\Api\ECommerceController::class , 'products']);
            Route::get('/products/{id}', [\App\Http\Controllers\Api\ECommerceController::class , 'productDetail']);
            Route::get('/categories', [\App\Http\Controllers\Api\ECommerceController::class , 'categories']);
            Route::get('/cart', [\App\Http\Controllers\Api\ECommerceController::class , 'getCart']);
            Route::post('/cart/add', [\App\Http\Controllers\Api\ECommerceController::class , 'addToCart']);
            Route::put('/cart/items/{id}', [\App\Http\Controllers\Api\ECommerceController::class , 'updateCartItem']);
            Route::delete('/cart/items/{id}', [\App\Http\Controllers\Api\ECommerceController::class , 'removeFromCart']);
            Route::delete('/cart/clear', [\App\Http\Controllers\Api\ECommerceController::class , 'clearCart']);
            Route::post('/checkout', [\App\Http\Controllers\Api\ECommerceController::class , 'checkout']);
            Route::get('/orders/{orderNumber}', [\App\Http\Controllers\Api\ECommerceController::class , 'getOrder']);
        });

        // Payment Routes
        Route::prefix('payments')->group(function () {
            Route::post('/initiate', [\App\Http\Controllers\Api\PaymentController::class , 'initiate']);
            Route::post('/callback', [\App\Http\Controllers\Api\PaymentController::class , 'callback']);
            Route::get('/verify/{orderNumber}', [\App\Http\Controllers\Api\PaymentController::class , 'verify']);
            Route::get('/methods', [\App\Http\Controllers\Api\PaymentController::class , 'getMethods']);
            Route::post('/cancel', [\App\Http\Controllers\Api\PaymentController::class , 'cancel']);
            Route::get('/status/{orderNumber}', [\App\Http\Controllers\Api\PaymentController::class , 'status']);
        });

        // Admin Order Management
        Route::prefix('admin/orders')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\OrderManagementController::class , 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Api\OrderManagementController::class , 'show']);
            Route::post('/{id}/confirm', [\App\Http\Controllers\Api\OrderManagementController::class , 'confirm']);
            Route::post('/{id}/process', [\App\Http\Controllers\Api\OrderManagementController::class , 'process']);
            Route::post('/{id}/ship', [\App\Http\Controllers\Api\OrderManagementController::class , 'ship']);
            Route::post('/{id}/deliver', [\App\Http\Controllers\Api\OrderManagementController::class , 'deliver']);
            Route::post('/{id}/cancel', [\App\Http\Controllers\Api\OrderManagementController::class , 'cancel']);
            Route::get('/statistics', [\App\Http\Controllers\Api\OrderManagementController::class , 'statistics']);
            Route::get('/export', [\App\Http\Controllers\Api\OrderManagementController::class , 'export']);
            Route::get('/customers/{customerId}', [\App\Http\Controllers\Api\OrderManagementController::class , 'customerOrders']);
        });

        // Mobile App API
        Route::prefix('mobile')->group(function () {
            // Auth
            Route::post('/login', [\App\Http\Controllers\Api\MobileAppController::class , 'login']);
            Route::post('/register', [\App\Http\Controllers\Api\MobileAppController::class , 'register']);
            Route::post('/logout', [\App\Http\Controllers\Api\MobileAppController::class , 'logout'])->middleware('auth:sanctum');
            
            // Home & Catalog
            Route::get('/home', [\App\Http\Controllers\Api\MobileAppController::class , 'home'])->middleware('auth:sanctum');
            Route::get('/products', [\App\Http\Controllers\Api\MobileAppController::class , 'products'])->middleware('auth:sanctum');
            Route::get('/products/{id}', [\App\Http\Controllers\Api\MobileAppController::class , 'productDetail'])->middleware('auth:sanctum');
            
            // Cart
            Route::get('/cart', [\App\Http\Controllers\Api\MobileAppController::class , 'cart'])->middleware('auth:sanctum');
            Route::get('/cart/summary', [\App\Http\Controllers\Api\MobileAppController::class , 'cartSummary'])->middleware('auth:sanctum');
            Route::post('/cart/add', [\App\Http\Controllers\Api\MobileAppController::class , 'addToCart'])->middleware('auth:sanctum');
            Route::put('/cart/items/{id}', [\App\Http\Controllers\Api\MobileAppController::class , 'updateCartItem'])->middleware('auth:sanctum');
            Route::delete('/cart/items/{id}', [\App\Http\Controllers\Api\MobileAppController::class , 'removeFromCart'])->middleware('auth:sanctum');
            Route::delete('/cart/clear', [\App\Http\Controllers\Api\MobileAppController::class , 'clearCart'])->middleware('auth:sanctum');
            
            // Checkout
            Route::post('/checkout', [\App\Http\Controllers\Api\MobileAppController::class , 'checkout'])->middleware('auth:sanctum');
            
            // Loyalty
            Route::get('/loyalty/summary', [\App\Http\Controllers\Api\MobileAppController::class , 'loyaltySummary'])->middleware('auth:sanctum');
            Route::get('/loyalty/qr-code', [\App\Http\Controllers\Api\MobileAppController::class , 'qrCode'])->middleware('auth:sanctum');
            Route::post('/loyalty/redeem', [\App\Http\Controllers\Api\MobileAppController::class , 'redeemPoints'])->middleware('auth:sanctum');
            Route::get('/loyalty/rewards', [\App\Http\Controllers\Api\MobileAppController::class , 'rewards'])->middleware('auth:sanctum');
            
            // Orders
            Route::get('/orders', [\App\Http\Controllers\Api\MobileAppController::class , 'orders'])->middleware('auth:sanctum');
            Route::get('/orders/{orderNumber}', [\App\Http\Controllers\Api\MobileAppController::class , 'orderDetail'])->middleware('auth:sanctum');
            
            // Notifications
            Route::post('/notifications/register-device', [\App\Http\Controllers\Api\NotificationController::class , 'registerDevice'])->middleware('auth:sanctum');
            Route::delete('/notifications/unregister-device', [\App\Http\Controllers\Api\NotificationController::class , 'unregisterDevice'])->middleware('auth:sanctum');
            Route::get('/notifications/preferences', [\App\Http\Controllers\Api\NotificationController::class , 'getPreferences'])->middleware('auth:sanctum');
            Route::put('/notifications/preferences', [\App\Http\Controllers\Api\NotificationController::class , 'updatePreferences'])->middleware('auth:sanctum');
            Route::get('/notifications/history', [\App\Http\Controllers\Api\NotificationController::class , 'history'])->middleware('auth:sanctum');
            Route::put('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class , 'markAsRead'])->middleware('auth:sanctum');
            
            // Stores
            Route::get('/stores', [\App\Http\Controllers\Api\MobileAppController::class , 'stores'])->middleware('auth:sanctum');
            Route::get('/stores/nearby', [\App\Http\Controllers\Api\MobileAppController::class , 'nearbyStores'])->middleware('auth:sanctum');
            
            // Wishlist
            Route::get('/wishlist', [\App\Http\Controllers\Api\MobileAppController::class , 'getWishlist'])->middleware('auth:sanctum');
            Route::post('/wishlist/add', [\App\Http\Controllers\Api\MobileAppController::class , 'addToWishlist'])->middleware('auth:sanctum');
            Route::delete('/wishlist/remove/{productId}', [\App\Http\Controllers\Api\MobileAppController::class , 'removeFromWishlist'])->middleware('auth:sanctum');
            
            // Reviews
            Route::post('/products/{productId}/review', [\App\Http\Controllers\Api\MobileAppController::class , 'submitReview'])->middleware('auth:sanctum');
            Route::get('/products/{productId}/reviews', [\App\Http\Controllers\Api\MobileAppController::class , 'getProductReviews'])->middleware('auth:sanctum');
            
            // Social Sharing
            Route::post('/products/{productId}/share', [\App\Http\Controllers\Api\MobileAppController::class , 'shareProduct'])->middleware('auth:sanctum');
            
            // Scan & Go
            Route::get('/scan-and-go/session', [\App\Http\Controllers\Api\MobileAppController::class , 'getScanAndGoSession'])->middleware('auth:sanctum');
            Route::post('/scan-and-go/scan', [\App\Http\Controllers\Api\MobileAppController::class , 'scanAndGoScan'])->middleware('auth:sanctum');
            Route::post('/scan-and-go/checkout', [\App\Http\Controllers\Api\MobileAppController::class , 'scanAndGoCheckout'])->middleware('auth:sanctum');
            
            // Utilities
            Route::get('/settings', [\App\Http\Controllers\Api\MobileAppController::class , 'settings'])->middleware('auth:sanctum');
            Route::post('/scan', [\App\Http\Controllers\Api\MobileAppController::class , 'scan'])->middleware('auth:sanctum');
            Route::get('/orders/{orderNumber}/receipt', [\App\Http\Controllers\Api\MobileAppController::class , 'receipt'])->middleware('auth:sanctum');
        });

        // Uploads
        Route::post('/upload/product-image', [\App\Http\Controllers\Api\UploadController::class , 'productImage']);

        // Analytics & BI
        Route::prefix('analytics')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Api\AnalyticsController::class , 'dashboard']);
            Route::get('/kpis', [\App\Http\Controllers\Api\AnalyticsController::class , 'kpis']);
            Route::get('/sales-trend', [\App\Http\Controllers\Api\AnalyticsController::class , 'salesTrend']);
            Route::get('/top-products', [\App\Http\Controllers\Api\AnalyticsController::class , 'topProducts']);
            Route::get('/category-performance', [\App\Http\Controllers\Api\AnalyticsController::class , 'categoryPerformance']);
            Route::get('/customer-segments', [\App\Http\Controllers\Api\AnalyticsController::class , 'customerSegments']);
            Route::get('/forecast', [\App\Http\Controllers\Api\AnalyticsController::class , 'forecast']);
            Route::get('/reports', [\App\Http\Controllers\Api\AnalyticsController::class , 'reports']);
            
            // Predictive Analytics
            Route::post('/forecast/generate', [\App\Http\Controllers\Api\AnalyticsController::class , 'generateForecast']);
            Route::get('/demand-prediction', [\App\Http\Controllers\Api\AnalyticsController::class , 'demandPrediction']);
            Route::get('/stock-optimization', [\App\Http\Controllers\Api\AnalyticsController::class , 'stockOptimization']);
            Route::get('/churn-prediction', [\App\Http\Controllers\Api\AnalyticsController::class , 'churnPrediction']);
            Route::post('/run-forecast', [\App\Http\Controllers\Api\AnalyticsController::class , 'runForecast']);

            // Customer Analytics
            Route::post('/rfm-calculate', [\App\Http\Controllers\Api\AnalyticsController::class , 'calculateRFM']);
            Route::get('/rfm-segments', [\App\Http\Controllers\Api\AnalyticsController::class , 'getRFMSegments']);
            Route::post('/clv-calculate', [\App\Http\Controllers\Api\AnalyticsController::class , 'calculateCLV']);
            Route::get('/cohort-analysis', [\App\Http\Controllers\Api\AnalyticsController::class , 'getCohortAnalysis']);
            Route::post('/generate-report', [\App\Http\Controllers\Api\AnalyticsController::class , 'generateReport']);
            Route::get('/export/{reportType}', [\App\Http\Controllers\Api\AnalyticsController::class , 'exportReport']);
        });

        // Forecast Target Routes
        Route::prefix('forecast')->group(function () {
            Route::post('/calculate-target', [\App\Http\Controllers\Api\ForecastTargetController::class , 'calculateTarget']);
            Route::post('/save-target', [\App\Http\Controllers\Api\ForecastTargetController::class , 'saveTarget']);
            Route::get('/active-target', [\App\Http\Controllers\Api\ForecastTargetController::class , 'getActiveTarget']);
            Route::post('/update-progress', [\App\Http\Controllers\Api\ForecastTargetController::class , 'updateProgress']);
        });

        // Suppliers
        Route::apiResource('suppliers', \App\Http\Controllers\Api\SupplierController::class);

        // Purchases (Goods-In) with batch creation
        Route::apiResource('purchases', \App\Http\Controllers\Api\PurchaseController::class);

        // Purchase Returns with batch deduction
        Route::get('purchase-returns/batches/{productId}', [\App\Http\Controllers\Api\PurchaseReturnController::class , 'getBatches']);
        Route::patch('purchase-returns/{id}/complete', [\App\Http\Controllers\Api\PurchaseReturnController::class , 'complete']);
        Route::patch('purchase-returns/{id}/cancel', [\App\Http\Controllers\Api\PurchaseReturnController::class , 'cancel']);
        Route::apiResource('purchase-returns', \App\Http\Controllers\Api\PurchaseReturnController::class);
    });

// Diagnostic Route
Route::get('/test-sort', function () {
    return \App\Models\Product::orderBy('id', 'desc')->take(5)->get();
});

// Super Admin API Routes (Phase 22 - SaaS Management)
Route::middleware(['auth:sanctum', 'super_admin'])->prefix('admin')->group(function () {
    // Dashboard & Stats
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\SuperAdmin\DashboardController::class , 'stats']);
    Route::get('/dashboard/revenue', [\App\Http\Controllers\Api\SuperAdmin\DashboardController::class , 'revenue']);
    Route::get('/dashboard/usage', [\App\Http\Controllers\Api\SuperAdmin\DashboardController::class , 'usage']);

    // Tenant Management
    Route::get('/tenants/stats', [\App\Http\Controllers\Api\SuperAdmin\TenantController::class , 'stats']);
    Route::get('/tenants', [\App\Http\Controllers\Api\SuperAdmin\TenantController::class , 'index']);
    Route::get('/tenants/{tenant}', [\App\Http\Controllers\Api\SuperAdmin\TenantController::class , 'show']);
    Route::patch('/tenants/{tenant}/status', [\App\Http\Controllers\Api\SuperAdmin\TenantController::class , 'updateStatus']);
    Route::post('/tenants/{tenant}/extend', [\App\Http\Controllers\Api\SuperAdmin\TenantController::class , 'extend']);
    Route::delete('/tenants/{tenant}', [\App\Http\Controllers\Api\SuperAdmin\TenantController::class , 'destroy']);

    // Subscription Plans
    Route::get('/plans/available', [\App\Http\Controllers\Api\SuperAdmin\PlanController::class , 'available']);
    Route::apiResource('plans', \App\Http\Controllers\Api\SuperAdmin\PlanController::class)->except(['available']);

    // Invoice Management
    Route::get('/invoices/stats', [\App\Http\Controllers\Api\SuperAdmin\InvoiceController::class , 'stats']);
    Route::get('/invoices', [\App\Http\Controllers\Api\SuperAdmin\InvoiceController::class , 'index']);
    Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\SuperAdmin\InvoiceController::class , 'show']);
    Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Api\SuperAdmin\InvoiceController::class , 'downloadPdf']);
    Route::post('/invoices/{invoice}/mark-paid', [\App\Http\Controllers\Api\SuperAdmin\InvoiceController::class , 'markAsPaid']);
    Route::post('/invoices/{invoice}/cancel', [\App\Http\Controllers\Api\SuperAdmin\InvoiceController::class , 'cancel']);
    Route::post('/invoices/generate-recurring', [\App\Http\Controllers\Api\SuperAdmin\InvoiceController::class , 'generateRecurring']);

    // Support Tickets
    Route::get('/tickets/stats', [\App\Http\Controllers\Api\SuperAdmin\SupportTicketController::class , 'stats']);
    Route::get('/tickets', [\App\Http\Controllers\Api\SuperAdmin\SupportTicketController::class , 'index']);
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\Api\SuperAdmin\SupportTicketController::class , 'show']);
    Route::post('/tickets/{ticket}/assign', [\App\Http\Controllers\Api\SuperAdmin\SupportTicketController::class , 'assign']);
    Route::patch('/tickets/{ticket}/status', [\App\Http\Controllers\Api\SuperAdmin\SupportTicketController::class , 'updateStatus']);
    Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\Api\SuperAdmin\SupportTicketController::class , 'reply']);
    Route::post('/tickets/{ticket}/resolve', [\App\Http\Controllers\Api\SuperAdmin\SupportTicketController::class , 'resolve']);
});

// Tenant Self-Service Portal API Routes (Phase 22)
Route::middleware(['auth:sanctum', 'tenant'])->prefix('tenant')->group(function () {
    // Subscription Management
    Route::get('/subscription', [\App\Http\Controllers\Api\Tenant\SubscriptionController::class , 'current']);
    Route::post('/subscription/change', [\App\Http\Controllers\Api\Tenant\SubscriptionController::class , 'change']);
    Route::post('/subscription/cancel', [\App\Http\Controllers\Api\Tenant\SubscriptionController::class , 'cancel']);

    // Usage Tracking
    Route::get('/usage/current', [\App\Http\Controllers\Api\Tenant\UsageController::class , 'current']);
    Route::get('/usage/history', [\App\Http\Controllers\Api\Tenant\UsageController::class , 'history']);
    Route::get('/usage/metric/{metric}', [\App\Http\Controllers\Api\Tenant\UsageController::class , 'metric']);
    Route::get('/usage/check-limits', [\App\Http\Controllers\Api\Tenant\UsageController::class , 'checkLimits']);

    // Invoices
    Route::get('/invoices/summary', [\App\Http\Controllers\Api\Tenant\InvoiceController::class , 'summary']);
    Route::get('/invoices', [\App\Http\Controllers\Api\Tenant\InvoiceController::class , 'index']);
    Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\Tenant\InvoiceController::class , 'show']);
    Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Api\Tenant\InvoiceController::class , 'downloadPdf']);
    Route::post('/invoices/{invoice}/pay', [\App\Http\Controllers\Api\Tenant\InvoiceController::class , 'payInvoice']);

    // Support Tickets
    Route::get('/tickets', [\App\Http\Controllers\Api\Tenant\SupportTicketController::class , 'index']);
    Route::post('/tickets', [\App\Http\Controllers\Api\Tenant\SupportTicketController::class , 'store']);
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\Api\Tenant\SupportTicketController::class , 'show']);
    Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\Api\Tenant\SupportTicketController::class , 'reply']);
    Route::post('/tickets/{ticket}/close', [\App\Http\Controllers\Api\Tenant\SupportTicketController::class , 'close']);
    Route::post('/tickets/{ticket}/reopen', [\App\Http\Controllers\Api\Tenant\SupportTicketController::class , 'reopen']);
});

// Payment Callback Routes (Public - no auth middleware)
Route::prefix('payments')->group(function () {
    Route::post('/callback/midtrans', [\App\Http\Controllers\Api\PaymentCallbackController::class , 'midtransCallback']);
    Route::get('/callback/finish', [\App\Http\Controllers\Api\PaymentCallbackController::class , 'finish'])->name('payment.callback');
    Route::get('/status/{invoiceNumber}', [\App\Http\Controllers\Api\PaymentCallbackController::class , 'status']);
    
    // Payment initiation (requires auth)
    Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
        Route::post('/initiate', [\App\Http\Controllers\Api\PaymentCallbackController::class , 'initiate']);
        Route::post('/cancel', [\App\Http\Controllers\Api\PaymentCallbackController::class , 'cancel']);
    });
});

// Legacy Super Admin Routes (keep for backward compatibility)
Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('legacy-admin')->group(function () {
    // Analytics
    Route::get('/analytics/overview', [\App\Http\Controllers\Api\Admin\AnalyticsController::class , 'overview']);
    Route::get('/analytics/revenue', [\App\Http\Controllers\Api\Admin\AnalyticsController::class , 'revenue']);
    Route::get('/analytics/tenants-map', [\App\Http\Controllers\Api\Admin\AnalyticsController::class , 'tenantsMap']);

    // Tenants CRUD
    Route::get('/tenants', [\App\Http\Controllers\Admin\TenantController::class , 'index']);
    Route::post('/tenants', [\App\Http\Controllers\Admin\TenantController::class , 'store']);
    Route::put('/tenants/{id}', [\App\Http\Controllers\Admin\TenantController::class , 'update']);
    Route::patch('/tenants/{id}/status', [\App\Http\Controllers\Admin\TenantController::class , 'updateStatus']);
    Route::post('/tenants/{id}/subscription', [\App\Http\Controllers\Admin\TenantController::class , 'extendSubscription']);
    Route::delete('/tenants/{id}', [\App\Http\Controllers\Admin\TenantController::class , 'destroy']);

    // Users CRUD
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class , 'index']);
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class , 'store']);
    Route::put('/users/{id}', [\App\Http\Controllers\Admin\UserController::class , 'update']);
    Route::delete('/users/{id}', [\App\Http\Controllers\Admin\UserController::class , 'destroy']);

    // License Generator
    Route::post('/license/generate', [\App\Http\Controllers\Admin\LicenseController::class , 'generate']);

    // SaaS Management (Legacy)
    Route::get('/saas/dashboard', [\App\Http\Controllers\Admin\SaasManagementController::class , 'dashboard']);
    Route::get('/saas/tenants', [\App\Http\Controllers\Admin\SaasManagementController::class , 'tenants']);
    Route::get('/saas/tenants/{id}', [\App\Http\Controllers\Admin\SaasManagementController::class , 'tenantDetail']);
    Route::post('/saas/tenants/{id}/subscription', [\App\Http\Controllers\Admin\SaasManagementController::class , 'updateSubscription']);
    Route::post('/saas/tenants/{id}/create-subscription', [\App\Http\Controllers\Admin\SaasManagementController::class , 'createSubscription']);
    Route::post('/saas/tenants/{id}/suspend', [\App\Http\Controllers\Admin\SaasManagementController::class , 'suspendTenant']);
    Route::post('/saas/tenants/{id}/reactivate', [\App\Http\Controllers\Admin\SaasManagementController::class , 'reactivateTenant']);
    Route::get('/saas/plans', [\App\Http\Controllers\Admin\SaasManagementController::class , 'plans']);
    Route::post('/saas/plans', [\App\Http\Controllers\Admin\SaasManagementController::class , 'savePlan']);
    Route::get('/saas/invoices', [\App\Http\Controllers\Admin\SaasManagementController::class , 'invoices']);
    Route::get('/saas/invoices/{id}/pdf', [\App\Http\Controllers\Admin\SaasManagementController::class , 'downloadInvoice']);
    Route::post('/saas/invoices/{id}/pay', [\App\Http\Controllers\Admin\SaasManagementController::class , 'payInvoice']);
    Route::get('/saas/tickets', [\App\Http\Controllers\Admin\SaasManagementController::class , 'tickets']);
    Route::get('/saas/billing/stats', [\App\Http\Controllers\Admin\SaasManagementController::class , 'billingStats']);
    Route::post('/saas/billing/process-recurring', [\App\Http\Controllers\Admin\SaasManagementController::class , 'processRecurring']);
    Route::post('/saas/billing/check-overdue', [\App\Http\Controllers\Admin\SaasManagementController::class , 'checkOverdue']);
});
