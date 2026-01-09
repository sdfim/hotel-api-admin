<?php

return [
    'merchant_id'    => env('CYBS_MERCHANT_ID'),
    'api_key_id'     => env('CYBS_API_KEY_ID'),
    'api_secret_key' => env('CYBS_API_SECRET_KEY'),
    // Example: apitest.cybersource.com or api.cybersource.com
    'environment'    => env('CYBS_ENV', 'apitest.cybersource.com'),

    // Default frontend origin for Microform (must match page where Microform is rendered)
    'default_origin' => env('CYBS_DEFAULT_ORIGIN', 'https://localhost'),

    // Optional: restrict allowed card networks and payment types
    'allowed_card_networks' => [
        'VISA',
        'MASTERCARD',
        'AMEX',
    ],

    'allowed_payment_types' => [
        'CARD',
    ],

    // Microform client version; according to docs typical value is "v2"
    'client_version' => 'v2',
];
