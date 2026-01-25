<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', function () {
})->name('login');

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Cash Register Routes
    Route::prefix('cash-register')->group(function () {
        Route::get('/current', [\App\Modules\Retail\Controllers\CashRegisterController::class, 'current']);
        Route::post('/open', [\App\Modules\Retail\Controllers\CashRegisterController::class, 'open']);
        Route::post('/close', [\App\Modules\Retail\Controllers\CashRegisterController::class, 'close']);
        Route::post('/expense', [\App\Modules\Retail\Controllers\CashRegisterController::class, 'addExpense']);
    });

    Route::get('/user/menus', [\App\Http\Controllers\Api\MenuController::class, 'getMenus']);

    // Master Data 
    Route::apiResource('products/categories', CategoryController::class);
});
