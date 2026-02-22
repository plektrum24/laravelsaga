<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    */
    'default_gateway' => env('PAYMENT_GATEWAY', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configurations
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        /*
         * Midtrans Configuration
         */
        'midtrans' => [
            'server_key' => env('MIDTRANS_SERVER_KEY', ''),
            'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
            'production' => env('MIDTRANS_PRODUCTION', false),
            'enabled_payments' => ['credit_card', 'gopay', 'bank_transfer'],
        ],

        /*
         * Xendit Configuration
         */
        'xendit' => [
            'secret_key' => env('XENDIT_SECRET_KEY', ''),
            'public_key' => env('XENDIT_PUBLIC_KEY', ''),
        ],

        /*
         * Manual Payment (COD/Bank Transfer)
         */
        'manual' => [
            'enabled' => true,
            'methods' => ['cod', 'bank_transfer'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Callback URL
    |--------------------------------------------------------------------------
    */
    'callback_url' => env('PAYMENT_CALLBACK_URL', '/api/payments/callback'),
];
