<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Retail\Controllers\ProductController;
use App\Modules\Retail\Controllers\CategoryController;

Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::apiResource('products/categories', CategoryController::class); // Moved here
    Route::get('products/units', [ProductController::class, 'getUnits']);
    Route::get('products/generate-sku/{category}', [ProductController::class, 'generateSku']);
    Route::apiResource('products', ProductController::class);
});
