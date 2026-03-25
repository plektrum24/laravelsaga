<?php

return [
    [
        'title' => 'Menu',
        'items' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />',
                'roles' => ['all'] // Visible to everyone
            ],
            [
                'label' => 'POS System',
                'id' => 'pos', // Converted to Submenu
                'icon' => '<path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />',
                'roles' => ['Owner', 'Kasir'],
                'submenu' => [
                    ['label' => 'Kasir (APP)', 'route' => 'pos.index'],
                    ['label' => 'Riwayat Transaksi', 'route' => 'pos.history'],
                ]
            ],
            [
                'label' => 'Sales Force',
                'id' => 'salesforce',
                'icon' => '<path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />',
                'roles' => ['Owner', 'Manager'],
                'submenu' => [
                    ['label' => 'Salesman Data', 'route' => 'salesman.index'],
                    ['label' => 'Sales Orders', 'route' => 'sales.create'],
                    ['label' => 'Visit Plans', 'route' => 'visit-plans.index'],
                    ['label' => 'Sales Order History', 'route' => 'sales.history'],
                ]
            ],
            [
                'label' => 'Item Receiving',
                'id' => 'item_receiving',
                'icon' => '<path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4m0 0c0 1.657 1.343 3 3 3h10c1.657 0 3-1.343 3-3m0 0V6" />',
                'roles' => ['Owner', 'Manager', 'Gudang'],
                'submenu' => [
                    ['label' => 'Goods In', 'route' => 'inventory.receiving.index'],
                    ['label' => 'Supplier Returns', 'route' => 'inventory.receiving.supplier-returns'],
                    ['label' => 'Customer Returns', 'route' => 'inventory.receiving.customer-returns'],
                    ['label' => 'Receiving History', 'route' => 'inventory.receiving.history'],
                ]
            ],
            [
                'label' => 'Inventory',
                'id' => 'inventory',
                'icon' => '<path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />',
                'roles' => ['Owner', 'Manager', 'Gudang'],
                'submenu' => [
                    ['label' => 'Current Stock', 'route' => 'inventory.index'],
                    ['label' => 'Stock Management', 'route' => 'inventory.stock-management'],
                    ['label' => 'Stock Transfer', 'route' => 'inventory.stock-transfer'],
                    ['label' => 'Transfer Analytics', 'route' => 'inventory.stock-transfer-analytics'],
                    ['label' => 'Stock Movements', 'route' => 'inventory.movements'],
                ]
            ],
            [
                'label' => 'Inventory Intelligence',
                'id' => 'inventory_intelligence',
                'icon' => '<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
                'roles' => ['Owner', 'Manager'],
                'submenu' => [
                    ['label' => 'Stock Analytics', 'route' => 'inventory.stock-analytics'],
                    ['label' => 'Product Forecasting', 'route' => 'inventory.forecasting'],
                    ['label' => 'Deadstock', 'route' => 'inventory.deadstock'],
                    ['label' => 'Categories', 'route' => 'inventory.categories'],
                    ['label' => 'Label Designer', 'route' => 'inventory.label-designer'],
                ]
            ],
            [
                'label' => 'Partners',
                'id' => 'partners',
                'icon' => '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />',
                'roles' => ['Owner', 'Manager', 'Gudang', 'Kasir'],
                'submenu' => [
                    ['label' => 'Suppliers', 'route' => 'inventory.suppliers'],
                    ['label' => 'Customers', 'route' => 'customers.index'],
                ]
            ],
            [
                'label' => 'Finance',
                'id' => 'finance',
                'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
                'roles' => ['Owner', 'Manager'],
                'submenu' => [
                    ['label' => 'Supplier Debts', 'route' => 'finance.debts'],
                    ['label' => 'Customer Receivables', 'route' => 'finance.receivables'],
                ]
            ]
        ]
    ],
    [
        'title' => 'Others',
        'items' => [
            [
                'label' => 'Sales Analytics',
                'route' => 'analytics.dashboard',
                'icon' => '<path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                'roles' => ['Owner', 'Manager', 'Kasir']
            ],
            [
                'label' => 'User Management',
                'route' => 'users.index',
                'icon' => '<path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />',
                'roles' => ['Owner']
            ],
            [
                'label' => 'Branches',
                'route' => 'branches.index',
                'icon' => '<path d="M17 1H7C5.9 1 5 1.9 5 3v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-2-2-2zm0 18H7V5h10v14zm-6-8h2v-2h-2v2zm0 4h2v-2h-2v2z" />',
                'roles' => ['Owner']
            ],
            [
                'label' => 'Settings',
                'id' => 'settings',
                'icon' => '<path d="M19.14 12.94c.04-.31.06-.63.06-.94 0-.31-.02-.63-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0 .59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" />',
                'roles' => ['Owner', 'Manager', 'Kasir', 'Gudang'],
                'submenu' => [
                    ['label' => 'Store Settings', 'route' => 'settings.index'],
                    ['label' => 'Loyalty Program', 'route' => 'settings.loyalty'],
                ]
            ]
        ]
    ]
];
