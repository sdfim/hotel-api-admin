<?php


return [
    'credential' => [
        'email' => env('CREDENTIAL_ALERT_EMAIL', 'italerts@ultimatejetvacations.com'),
        'cooldown' => env('CREDENTIAL_ALERT_COOLDOWN_MINUTES', 60),
    ],
];
