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
        'content_repository' => [
            'api' => [
                'title' => 'Content Repository API',
            ],
            'routes' => [
                'api' => 'admin/api/doc-content-repository',
                'docs' => 'docs-cr',
            ],
            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
                'docs_json' => 'api-docs-cr.json',
                'docs_yaml' => 'api-docs-cr.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
                'annotations' => [
                    base_path('Modules/HotelContentRepository/API'),
                ],
            ],
        ],
    ],
];
