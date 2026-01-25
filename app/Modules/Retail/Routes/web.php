<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    // Inventory
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', function () {
            return view('pages.inventory.index');
        })->name('index');
        Route::get('/create', function () {
            return view('pages.inventory.create');
        })->name('create');
        Route::get('/categories', function () {
            return view('pages.inventory.categories');
        })->name('categories');
        Route::get('/stock-management', function () {
            return view('pages.inventory.stock-management');
        })->name('stock-management');
        Route::get('/transfer', function () {
            return view('pages.inventory.transfer');
        })->name('transfer');
        Route::get('/deadstock', function () {
            return view('pages.inventory.deadstock');
        })->name('deadstock');

        // Receiving
        Route::prefix('receiving')->name('receiving.')->group(function () {
            Route::get('/goods-in', function () {
                return view('pages.inventory.receiving.goods-in');
            })->name('goods-in');
            Route::get('/supplier-returns', function () {
                return view('pages.inventory.receiving.supplier-returns');
            })->name('supplier-returns');
            Route::get('/customer-returns', function () {
            })->name('customer-returns');
        });

        Route::get('/suppliers', function () {
            return view('pages.inventory.suppliers');
        })->name('suppliers');
    });
});
