<?php

return [
    'default_provider' => env('PAYMENT_PROVIDER', 'cybersource'),

    'providers' => [
        'airwallex'   => \Modules\API\Payment\Controllers\Providers\AirwallexPaymentProvider::class,
        'cybersource' => \Modules\API\Payment\Controllers\Providers\CybersourcePaymentProvider::class,
    ],
];
