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

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'collation' => env('DB_COLLATION', 'utf8mb4_0900_ai_ci'),
        ],

        'mysql_cache' => [
            'driver' => 'mysql',
            'host' => env('SUPPLIER_CONTENT_DB_HOST', '127.0.0.1'),
            'port' => env('SUPPLIER_CONTENT_DB_PORT', '3306'),
            'database' => env('SUPPLIER_CONTENT_DB_DATABASE', 'forge'),
            'username' => env('SUPPLIER_CONTENT_DB_USERNAME', 'forge'),
            'password' => env('SUPPLIER_CONTENT_DB_PASSWORD', ''),
        ],

        'donor' => [
            'driver' => 'mysql',
            'host' => env('DONOR_DB_HOST', '127.0.0.1'),
            'port' => env('DONOR_DB_PORT', '3306'),
            'database' => env('DONOR_DB_DATABASE', 'forge'),
            'username' => env('DONOR_DB_USERNAME', 'forge'),
            'password' => env('DONOR_DB_PASSWORD', ''),
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

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],
];
