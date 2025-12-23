<?php

return [
    'credential' => [
        'email' => [
            'to' => env('CREDENTIAL_ALERT_EMAIL', 'italerts@'  . env('APP_NAME') .  '.com'),
        ],
        'cooldown' => env('CREDENTIAL_ALERT_COOLDOWN_MINUTES', 60),
    ],
    'unmapped_data' => [
        'email' => [
            'to' => env('UNMAPPED_DATA_ALERT_EMAIL', 'productoperations@'  . env('APP_NAME') .  '.com'),
            'cc' => env('UNMAPPED_DATA_ALERT_CC_EMAIL', 'ccicero@'  . env('APP_NAME') .  '.com'),
        ],
    ],
];
