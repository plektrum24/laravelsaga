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
                    ['label' => 'Salesmen Data', 'route' => 'sales.index'], // Assuming route exists or will be created
                    ['label' => 'Sales Orders', 'route' => 'sales.create'],
                    ['label' => 'Visit Plans', 'route' => 'sales.index'],
                ]
            ],
            [
                'label' => 'Inventory',
                'id' => 'inventory', // for toggle
                'icon' => '<path d="M20 3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H4V5h16v14zM6 7h12v2H6zm0 4h12v2H6zm0 4h8v2H6z" />',
                'roles' => ['Owner', 'Gudang'],
                'submenu' => [
                    ['label' => 'Items', 'route' => 'inventory.index'],
                    ['label' => 'Categories', 'route' => 'inventory.categories'],
                    ['label' => 'Stock Management', 'route' => 'inventory.stock-management'],
                    ['label' => 'Deadstock', 'route' => 'inventory.deadstock'],
                    ['label' => 'Transfer Item', 'route' => 'inventory.transfer'],
                ]
            ],
            [
                'label' => 'Item Receiving',
                'id' => 'receiving',
                'icon' => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z" />',
                'roles' => ['Owner', 'Gudang'],
                'submenu' => [
                    ['label' => 'Goods In', 'route' => 'inventory.receiving.goods-in'],
                    ['label' => 'Return Supplier', 'route' => 'inventory.receiving.supplier-returns'],
                    ['label' => 'Returns (Customer)', 'route' => 'inventory.receiving.customer-returns'],
                ]
            ],
            [
                'label' => 'Suppliers & Customers',
                'id' => 'partners',
                'icon' => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />',
                'roles' => ['Owner', 'Gudang', 'Kasir'],
                'submenu' => [
                    ['label' => 'Suppliers', 'route' => 'inventory.suppliers'],
                    ['label' => 'Customers', 'route' => 'customers.index'],
                ]
            ],
            [
                'label' => 'Debt & Receivables',
                'id' => 'debt',
                'icon' => '<path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z" />',
                'roles' => ['Owner', 'Manager'],
                'submenu' => [
                    ['label' => 'Supplier Debts', 'route' => 'finance.debts'],
                    ['label' => 'Receivables', 'route' => 'finance.receivables'],
                ]
            ]
        ]
    ],
    [
        'title' => 'Others',
        'items' => [
            [
                'label' => 'Payroll (Gaji)',
                'route' => 'payroll.index',
                'icon' => '<path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z" />',
                'roles' => ['Owner']
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
                'roles' => ['Owner', 'Manager'],
                'submenu' => [
                    ['label' => 'Store Settings', 'route' => 'settings.index'],
                    ['label' => 'Backup & Export', 'route' => 'settings.index', 'params' => ['tab' => 'backup']],
                ]
            ]
        ]
    ]
];
