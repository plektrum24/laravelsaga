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

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Products Helpers
    Route::get('/products/categories', [ProductController::class, 'categories']);
    Route::get('/products/units', [ProductController::class, 'units']);
    Route::get('/products/generate-sku/{categoryId}', [ProductController::class, 'generateSku']);
    Route::post('/products/units', [ProductController::class, 'storeUnit']); // Quick add unit

    // Resources
    Route::apiResource('products', ProductController::class);
    Route::apiResource('customers', CustomerController::class);

    // Transactions / POS
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']); // Checkout
});
