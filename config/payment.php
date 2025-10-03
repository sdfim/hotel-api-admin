<?php
return [
    'default_provider' => env('PAYMENT_PROVIDER', 'airwallex'),
    'providers' => [
        'airwallex' => \Modules\API\Payment\Controllers\Providers\AirwallexPaymentProvider::class,
        // Add other providers here
    ],
];

