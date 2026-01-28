<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthorized'], 401);
})->name('login');

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);

    // Menus
    Route::get('/user/menus', [\App\Http\Controllers\Api\MenuController::class, 'getMenus']);
    // Notifications (Mock)
    Route::get('/notifications', function () {
        return response()->json(['success' => true, 'data' => []]);
    });

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Products & Inventory
    // Note: 'products/categories' resource must come BEFORE 'products' resource if using same prefix, 
    // or just ensure no conflict. Here it is fine.
    // We replace the simple 'categories' helper with full resource.
    Route::apiResource('products/categories', \App\Http\Controllers\Api\CategoryController::class);

    // Units Helper
    Route::get('/products/units', [ProductController::class, 'units']);
    Route::post('/products/units', [ProductController::class, 'storeUnit']);
    Route::get('/products/generate-sku/{categoryId}', [ProductController::class, 'generateSku']);

    // Inventory Management
    Route::get('/products/expiry', [\App\Http\Controllers\Api\InventoryController::class, 'expiry']);
    Route::post('/products/adjust-stock/{id}', [\App\Http\Controllers\Api\InventoryController::class, 'adjustStock']);
    Route::post('/products/reset-all-stock', [\App\Http\Controllers\Api\InventoryController::class, 'resetAllStock']);

    // Core Resources
    Route::post('products/import', [ProductController::class, 'import']);
    Route::delete('products/delete-all', [ProductController::class, 'destroyAll']);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('customers', CustomerController::class);

    // Transactions / POS
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);

    // Reports
    Route::get('/reports/assets', [\App\Http\Controllers\Api\ReportController::class, 'assets']);
});

// Diagnostic Route
Route::get('/test-sort', function () {
    return \App\Models\Product::orderBy('id', 'desc')->take(5)->get();
});
