<?php

return [
    'credential' => [
        'email' => [
            'to' => env('CREDENTIAL_ALERT_EMAIL', 'italerts@TerraMare.com'),
        ],
        'cooldown' => env('CREDENTIAL_ALERT_COOLDOWN_MINUTES', 60),
    ],
    'unmapped_data' => [
        'email' => [
            'to' => env('UNMAPPED_DATA_ALERT_EMAIL', 'productoperations@TerraMare.com'),
            'cc' => env('UNMAPPED_DATA_ALERT_CC_EMAIL', 'ccicero@TerraMare.com'),
        ],
    ],
];
