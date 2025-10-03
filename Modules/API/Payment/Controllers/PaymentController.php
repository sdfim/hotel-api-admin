<?php

namespace Modules\API\Payment\Controllers;

use App\Contracts\PaymentProviderInterface;
use App\Support\PaymentProviderResolver;
use Illuminate\Http\Request;
use Modules\API\Payment\Requests\ConfirmationPaymentIntentRequest;
use Modules\API\Payment\Requests\CreatePaymentIntentRequest;

class PaymentController
{
    protected function getProvider(Request $request): PaymentProviderInterface
    {
        $provider = $request->input('provider');

        return PaymentProviderResolver::resolve($provider);
    }

    public function createPaymentIntent(CreatePaymentIntentRequest $request)
    {
        $provider = $this->getProvider($request);

        return $provider->createPaymentIntent($request->validated());
    }

    public function confirmationPaymentIntent(ConfirmationPaymentIntentRequest $request)
    {
        $provider = $this->getProvider($request);

        return $provider->confirmationPaymentIntent($request->validated());
    }

    public function retrievePaymentIntent(Request $request, $id)
    {
        $provider = $this->getProvider($request);

        return $provider->retrievePaymentIntent($id);
    }

    public function getTransactionByBookingId(Request $request, $bookingId)
    {
        $provider = $this->getProvider($request);

        return $provider->getTransactionByBookingId($bookingId);
    }
}
