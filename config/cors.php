<?php

logger()->info('CORS configuration loaded', ['CORS_ALLOWED_ORIGINS' => explode(',', env('CORS_ALLOWED_ORIGINS', ''))]);

return [

    'paths' => ['api/*', 'admin/*', 'docs/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
