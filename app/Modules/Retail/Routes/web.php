<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    // Note: Inventory routes are now managed in routes/web.php
    // This file is kept for module structure but routes are disabled to prevent conflicts
    // 
    // Main routes location: routes/web.php
    // - /inventory → inventory.index
    // - /inventory/receiving → inventory.receiving.index (Goods In)
    // - /inventory/receiving/history → inventory.receiving.history
    // - /inventory/stock-management → inventory.stock-management
    // - /inventory/stock-transfer → inventory.stock-transfer
    // - /inventory/suppliers → inventory.suppliers
});
