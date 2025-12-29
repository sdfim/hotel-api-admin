<?php

return [
    'connected_suppliers' => env('CONNECTED_SUPPLIERS', 'HBSI,IcePortal,HotelTrader'),

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

    'Hilton' => [
        'credentials' => [
            'client_id' => env('HILTON_CLIENT_ID', ''),
            'client_secret' => env('HILTON_CLIENT_SECRET', ''),
            'base_url' => env('HILTON_BASE_URL', 'https://kapip-s.hilton.io/hospitality-partner/v2'),
            'token_url' => env('HILTON_TOKEN_URL', 'https://kapip-s.hilton.io/hospitality-partner/v2/realms/applications/token'),
        ],
    ],

    'HotelTrader' => [
        'credentials' => [
            'graphql_search_url' => env('SUPPLIER_HOTEL_TRADER_GRAPHQL_SEARCH_URL', 'https://search-sandbox.hoteltrader.com/graphql'),
            'graphql_book_url' => env('SUPPLIER_HOTEL_TRADER_GRAPHQL_BOOK_URL', 'https://book-sandbox.hoteltrader.com/graphql'),
            'username' => env('SUPPLIER_HOTEL_TRADER_API_KEY', ''),
            'password' => env('SUPPLIER_HOTEL_TRADER_TOKEN_URL', ''),
        ],
        'push_credentials' => [
            'username' => env('HOTEL_TRADER_PUSH_USERNAME', ''),
            'password' => env('HOTEL_TRADER_PUSH_PASSWORD', ''),
        ],
        'use_debug_tax_fee' => env('USE_DEBUG_TAX_FEE', false),
    ],

    'Oracle' => [
        'credentials' => [
            'username' => env('ORACLE_CLIENT_USERNAME', ''),
            'password' => env('ORACLE_CLIENT_PASSWORD', ''),
            'basic_username' => env('ORACLE_BASIC_USERNAME', ''),
            'basic_password' => env('ORACLE_BASIC_PASSWORD', ''),
            'app_key' => env('ORACLE_CLIENT_APP_KEY', ''),
            'base_url' => env('ORACLE_CLIENT_BASE_URL', ''),
        ],
    ],
];
