<?php

namespace App\Support;

use App\Contracts\PaymentProviderInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class PaymentProviderResolver
{
    public static function resolve(?string $provider = null): PaymentProviderInterface
    {
        $provider = $provider ?: Config::get('payment.default_provider');
        $providers = Config::get('payment.providers');
        $providerClass = $providers[$provider] ?? null;
        if (! $providerClass) {
            throw new \InvalidArgumentException("Unknown payment provider: $provider");
        }

        return App::make($providerClass);
    }
}
