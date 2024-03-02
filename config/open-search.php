<?php

return [

    'connection' => env('OPENSEARCH_CONNECTION', 'elasticsearch'),

    'connections' => [

        'elasticsearch' => [
            'host' => env('ELASTICSEARCH_HOST', 'http://localhost:9200'),
            'index' => env('ELASTICSEARCH_INDEX', 'giata_properies_v2'),
        ],

        'aws' => [
            'host' => env('OPENSEARCH_HOST', ''),
            'user' => env('OPENSEARCH_USER', ''),
            'pass' => env('OPENSEARCH_PASS', ''),
            'key' => env('OPENSEARCH_KEY', ''),
            'secret' => env('OPENSEARCH_SECRET', ''),
            'region' => env('OPENSEARCH_REGION', 'us-east-1'),
            'service' => env('OPENSEARCH_SERVICE', 'es'),
            'index' => env('OPENSEARCH_INDEX', 'giata_properies_v1'),
        ],
    ],
];
