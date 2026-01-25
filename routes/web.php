<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', function () {
    return view('pages.dashboard');
})->name('dashboard');

// Auth
Route::get('/signup', function () {
    return view('pages.auth.signup');
})->name('signup');
Route::get('/signin', function () {
    return view('pages.auth.signin');
})->name('signin');

// Inventory Routes loaded from Retail Module

// Finance
Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/debts', function () {
        return view('pages.finance.debts');
    })->name('debts');
    Route::get('/receivables', function () {
        return view('pages.finance.receivables');
    })->name('receivables');
});

// Sales
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', function () {
        return view('pages.sales.index');
    })->name('index');
    Route::get('/create', function () {
        return view('pages.sales.create');
    })->name('create');
});

// POS
Route::get('/pos', function () {
    return view('pages.pos.index');
})->name('pos.index');

Route::get('/pos/history', function () {
    return view('pages.pos.history');
})->name('pos.history');

// Customers
Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', function () {
        return view('pages.customers.index');
    })->name('index');
});

// Reports
Route::get('/reports', function () {
    return view('pages.reports.index');
})->name('reports.index');

// Settings
Route::get('/settings', function () {
    return view('pages.settings.index');
})->name('settings.index');

// User Management
Route::get('/users', function () {
    return view('pages.users.index');
})->name('users.index');

// Payroll
Route::get('/payroll', function () {
    return view('pages.payroll.index');
})->name('payroll.index');

// Branch Management
Route::get('/branches', function () {
    return view('pages.branches.index');
})->name('branches.index');

// Profile
Route::get('/profile', function () {
    return view('pages.profile');
})->name('profile');

// Fallback
Route::get('/blank', function () {
    return view('pages.blank');
})->name('blank');
