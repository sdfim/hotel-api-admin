<?php

return [
    'HBSI' => [
        'credentials' => [
            'username' => env('BOOKING_SUPPLIER_HBSI_USERNAME', ''),
            'password' => env('BOOKING_SUPPLIER_HBSI_PASSWORD', ''),
            'channel_identifier_id' => env('BOOKING_SUPPLIER_HBSI_CHANNEL_IDENTIFIER_ID', ''),
            'search_book_url' => env('BOOKING_SUPPLIER_HBSI_SEARCH_BOOK_URL', ''),
            'target' => env('BOOKING_SUPPLIER_HBSI_TARGET', 'Test'),
            'component_info_id' => env('BOOKING_SUPPLIER_HBSI_COMPONENT_INFO_ID', ''),
        ],
        'use_debug_tax_fee' => env('USE_DEBUG_TAX_FEE', false),
    ],

    'Expedia' => [
        'credentials' => [
            'api_key' => env('SUPPLIER_EXPEDIA_API_KEY', ''),
            'shared_secret' => env('SUPPLIER_EXPEDIA_SHARED_SECRET', ''),
            'rapid_base_url' => env('SUPPLIER_EXPEDIA_BASE_URL', ''),
            'test_token' => env('TEST_TOKEN', ''),
        ],
        'supplier_expedia_rate_type' => env('SUPPLIER_EXPEDIA_RATE_TYPE', 'standalone'),
    ],

    'IcePortal' => [
        'credentials' => [
            'client_id' => env('SUPPLIER_ICE_PORTAL_CLIENT_ID', ''),
            'client_secret' => env('SUPPLIER_ICE_PORTAL_CLIENT_SECRET', ''),
            'base_url' => env('SUPPLIER_ICE_PORTAL_BASE_URL', ''),
            'token_url' => env('SUPPLIER_ICE_PORTAL_TOKEN_URL', ''),
        ],
    ],
];
