<?php

use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- */

// Dashboard
Route::get('/', function () {
    return view('pages.dashboard');
})->name('dashboard');

Route::get('/dashboard', function () {
    return redirect()->route('dashboard');
});

// Auth
Route::get('/signup', function () {
    return view('pages.auth.signup');
})->name('signup');
Route::get('/signin', function () {
    return view('pages.auth.signin');
})->name('login'); // Named 'login' for Laravel auth redirect

// Super Admin Routes (Legacy)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        }
        );

        Route::get('/dashboard', function () {
            return view('pages.admin.dashboard');
        }
        )->name('dashboard');

        Route::get('/tenants', [\App\Http\Controllers\Admin\TenantController::class , 'index'])->name('tenants.index');
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class , 'index'])->name('users.index');
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportsController::class , 'index'])->name('reports.index');
        Route::get('/license', [\App\Http\Controllers\Admin\LicenseController::class , 'index'])->name('license.index');
        Route::post('/license/generate', [\App\Http\Controllers\Admin\LicenseController::class , 'generate'])->name('license.generate');
    });

// Super Admin Routes - Phase 22 SaaS Management
Route::prefix('super-admin')->name('super-admin.')->middleware(['auth:sanctum', 'super_admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.super-admin.dashboard');
    })->name('dashboard');

    Route::get('/tenants', function () {
        return view('pages.super-admin.tenants.index');
    })->name('tenants.index');

    Route::get('/tenants/{tenant}', function ($id) {
        return view('pages.super-admin.tenants.show', ['tenantId' => $id]);
    })->name('tenants.show');

    Route::get('/plans', function () {
        return view('pages.super-admin.plans.index');
    })->name('plans.index');

    Route::get('/invoices', function () {
        return view('pages.super-admin.invoices.index');
    })->name('invoices.index');

    Route::get('/tickets', function () {
        return view('pages.super-admin.tickets.index');
    })->name('tickets.index');
});

// Tenant Portal Routes - Phase 22
Route::prefix('tenant-portal')->name('tenant-portal.')->middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::get('/', function () {
        return view('pages.tenant-portal.dashboard');
    })->name('dashboard');

    Route::get('/dashboard', function () {
        return view('pages.tenant-portal.dashboard');
    })->name('home');

    Route::get('/subscription', function () {
        return view('pages.tenant-portal.subscription');
    })->name('subscription');

    Route::get('/invoices', function () {
        return view('pages.tenant-portal.invoices');
    })->name('invoices');

    Route::get('/tickets', function () {
        return view('pages.tenant-portal.tickets');
    })->name('tickets');

    Route::get('/usage', function () {
        return view('pages.tenant-portal.usage');
    })->name('usage');
});

// Inventory Routes
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/index', function () {
            return view('pages.inventory.index');
        }
        )->name('index');

        // Analytics Routes (Phase 30)
        Route::get('/analytics/realtime', function () {
            return view('pages.analytics.realtime');
        }
        )->name('analytics.realtime');

        Route::get('/analytics/forecasting', function () {
            return view('pages.analytics.forecasting');
        }
        )->name('analytics.forecasting');

        Route::get('/analytics/customers', function () {
            return view('pages.analytics.customers');
        }
        )->name('analytics.customers');

        Route::get('/performance/monitor', function () {
            return view('pages.performance.monitor');
        }
        )->name('performance.monitor');

        Route::get('/categories', function () {
            return view('pages.inventory.categories');
        }
        )->name('categories');

        Route::get('/stock', function () {
            return view('pages.inventory.stock-management');
        }
        )->name('stock');

        Route::get('/stock-management', function () {
            return view('pages.inventory.stock-management');
        }
        )->name('stock-management');

        Route::get('/movements', function () {
            return view('pages.inventory.movements');
        }
        )->name('movements');

        Route::get('/receiving', function () {
            return view('pages.inventory.receiving.goods-in-standalone');
        }
        )->name('receiving.index');

        Route::get('/receiving/create', function () {
            return view('pages.inventory.receiving.create');
        }
        )->name('receiving.create');

        Route::get('/receiving/supplier-returns', function () {
            return view('pages.inventory.receiving.supplier-returns');
        }
        )->name('receiving.supplier-returns');

        Route::get('/receiving/customer-returns', function () {
            return view('pages.inventory.receiving.customer-returns');
        }
        )->name('receiving.customer-returns');

        Route::get('/receiving/history', function () {
            return view('pages.inventory.receiving.history');
        }
        )->name('receiving.history');

        Route::get('/suppliers', function () {
            return view('pages.inventory.suppliers');
        }
        )->name('suppliers');

        Route::get('/transfer', function () {
            return view('pages.inventory.transfer');
        }
        )->name('transfer');

        Route::get('/stock-transfer', function () {
            return view('pages.inventory.stock-transfer');
        }
        )->name('stock-transfer');

        Route::get('/stock-transfer-analytics', function () {
            return view('pages.inventory.stock-transfer-analytics');
        }
        )->name('stock-transfer-analytics');

        Route::get('/label-designer', function () {
            return view('pages.inventory.label-designer');
        }
        )->name('label-designer');

        Route::get('/deadstock', function () {
            return view('pages.inventory.deadstock');
        }
        )->name('deadstock');

        // Analytics
        Route::get('/analytics', function () {
            return view('pages.analytics.dashboard');
        })->name('analytics.dashboard');

        // Stock Analytics & Forecasting
        Route::get('/stock-analytics', function () {
            return view('pages.inventory.stock-analytics');
        })->name('stock-analytics');

        Route::get('/forecasting', function () {
            return view('pages.inventory.forecasting');
        })->name('forecasting');
    });

// Returns (Combined Supplier & Customer Returns)
Route::prefix('inventory/returns')->name('inventory.returns.')->group(function () {
    Route::get('/', function () {
        return view('pages.inventory.returns.index');
    })->name('index');
    Route::get('/supplier', function () {
        return view('pages.inventory.returns.supplier-returns');
    })->name('supplier');
    Route::get('/customer', function () {
        return view('pages.inventory.returns.customer-returns');
    })->name('customer');
});

// Analytics Dashboard (Standalone - Outside Inventory)
Route::get('/analytics', function () {
    return view('pages.analytics.dashboard');
})->name('analytics.dashboard');

// Finance
Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/debts', function () {
            return view('pages.finance.debts');
        }
        )->name('debts');
        Route::get('/receivables', function () {
            return view('pages.finance.receivables');
        }
        )->name('receivables');
    });

// Sales
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', function () {
            return view('pages.sales.index');
        }
        )->name('index');
        Route::get('/create', function () {
            return view('pages.sales.create');
        }
        )->name('create');
        Route::get('/history', function () {
            return view('pages.sales.history');
        }
        )->name('history');
    });

// Salesman Routes
Route::prefix('salesman')->name('salesman.')->group(function () {
    Route::get('/', function () {
        return view('pages.salesman.index');
    })->name('index');
});

// Visit Plans Routes
Route::prefix('visit-plans')->name('visit-plans.')->group(function () {
    Route::get('/', function () {
        return view('pages.visit-plans.index');
    })->name('index');
});

// POS Routes - Cashier System
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('/', function () {
        return view('pages.pos.index');
    })->name('index');
    
    Route::get('/history', function () {
        return view('pages.pos.history');
    })->name('history');
    
    Route::get('/cashier', function () {
        return redirect()->route('pos.index');
    })->name('cashier');
    
    Route::get('/transactions', function () {
        return redirect()->route('pos.history');
    })->name('transactions');
});

// Customers
Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', function () {
            return view('pages.customers.index');
        }
        )->name('index');
    });

// Reports
Route::get('/reports', function () {
    return view('pages.reports.index');
})->name('reports.index');

Route::get('/reports/cash-register', function () {
    return view('pages.reports.cash-register');
})->name('reports.cash-register');

// Settings
Route::get('/settings', function () {
    return view('pages.settings.index');
})->name('settings.index');

Route::get('/settings/loyalty', function () {
    return view('pages.settings.loyalty');
})->name('settings.loyalty');

// User Management
Route::get('/users', function () {
    return view('pages.employees.index');
})->name('users.index');

Route::get('/team/manage', function () {
    return view('pages.team.manage');
})->name('team.manage');

Route::get('/via-management', function () {
    return view('pages.via.index');
})->name('via.management');

// Payroll
Route::get('/payroll', function () {
    return view('pages.payroll.index');
})->name('payroll.index');

// HR & Payroll Routes (New)
Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('/', function () {
        return view('pages.employees.index');
    })->name('index');
});

Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', function () {
        return view('pages.attendance.index');
    })->name('index');
});

Route::prefix('hr')->name('hr.')->group(function () {
    Route::get('/reports', function () {
        return view('pages.hr.reports');
    })->name('reports');
});

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
