<?php
return [
    'default_provider' => env('PAYMENT_PROVIDER', 'airwallex'),
    'providers' => [
        'airwallex' => \App\Providers\AirwallexPaymentProvider::class,
        // Add other providers here
    ],
];

