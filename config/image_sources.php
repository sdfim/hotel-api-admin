<?php

return [
    'sources' => [
        's3' => env('S3_BASE_URL', 'https://obe-terra_mare.s3.amazonaws.com/'),
        'local' => env('APP_URL', 'http://localhost/'),
        'crm' => env('CRM_PATH_IMAGES', 'https://your-crm-domain.com/storage/'),
    ],
];
