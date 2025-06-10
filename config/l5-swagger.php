<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Main API',
            ],
            'routes' => [
                'api' => 'admin/api/documentation',
                'docs' => 'docs',
                'oauth2_callback' => 'oauth2-callback',
            ],
            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
                'annotations' => [
                    base_path('Modules/API'),
                ],
            ],
        ],
    ],
];
