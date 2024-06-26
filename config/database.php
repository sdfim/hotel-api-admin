<?php

use Illuminate\Support\Str;

return [

    'connections' => [
        'sqlite2' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('SUPPLIER_CONTENT_DB_DATABASE', database_path('database2.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql_cache' => [
            'driver' => 'mysql',
            'host' => env('SUPPLIER_CONTENT_DB_HOST', '127.0.0.1'),
            'port' => env('SUPPLIER_CONTENT_DB_PORT', '3306'),
            'database' => env('SUPPLIER_CONTENT_DB_DATABASE', 'forge'),
            'username' => env('SUPPLIER_CONTENT_DB_USERNAME', 'forge'),
            'password' => env('SUPPLIER_CONTENT_DB_PASSWORD', ''),
        ],
    ],

    'active_connections' => [
        'mysql' => env('DB_CONNECTION', 'mysql'),
        'mysql_cache' => env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'),
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => false, // disable to preserve original behavior for existing applications
    ],

];
