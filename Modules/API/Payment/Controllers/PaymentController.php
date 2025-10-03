<?php

namespace Modules\API\Payment\Controllers;

use App\Contracts\PaymentProviderInterface;
use App\Repositories\ApiBookingInspectorRepository;
use App\Support\PaymentProviderResolver;
use Illuminate\Http\Request;
use Modules\API\BaseController;
use Modules\API\Payment\Requests\ConfirmationPaymentIntentRequest;
use Modules\API\Payment\Requests\CreatePaymentIntentRequest;

class PaymentController extends BaseController
{
    protected function getProvider(Request $request): PaymentProviderInterface
    {
        $provider = $request->input('provider');

        return PaymentProviderResolver::resolve($provider);
    }

    public function createPaymentIntent(CreatePaymentIntentRequest $request)
    {
        $provider = $this->getProvider($request);

        $booking_id = $request->input('booking_id');
        if (ApiBookingInspectorRepository::bookedItems($booking_id)->isEmpty()) {
            $error = $booking_id.' - booking_id not found or not booked';
            return $this->sendError($error, 'Booking not found or not booked', 404);
        }

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
