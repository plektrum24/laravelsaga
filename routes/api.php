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

    Route::get('/user/menus', [\App\Http\Controllers\Api\MenuController::class, 'getMenus']);

    // Master Data 
    Route::apiResource('products/categories', CategoryController::class);
});
