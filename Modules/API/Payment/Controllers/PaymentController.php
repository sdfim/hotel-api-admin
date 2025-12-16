<?php

namespace Modules\API\Payment\Controllers;

use App\Contracts\PaymentProviderInterface;
use App\Mail\BookingClientConfirmationMail;
use App\Models\ApiBookingPaymentInit;
use App\Repositories\ApiBookingInspectorRepository;
use App\Support\PaymentProviderResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\API\BaseController;
use Modules\API\Payment\Requests\ConfirmationPaymentIntentRequest;
use Modules\API\Payment\Requests\CreatePaymentIntentRequest;
use Modules\API\Payment\Requests\RetrievePaymentConsentRequest;

class PaymentController extends BaseController
{
    protected function getProvider(?Request $request): PaymentProviderInterface
    {
        $provider = $request?->input('provider');

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

    public function createPaymentIntentMoFoF(string $booking_id, float $amount)
    {
        $provider = $this->getProvider(null);

        if (ApiBookingInspectorRepository::bookedItems($booking_id)->isEmpty()) {
            $error = $booking_id.' - booking_id not found or not booked';

            return $this->sendError($error, 'Booking not found or not booked', 404);
        }

        return $provider->createPaymentIntentMoFoF($booking_id, $amount);
    }

    public function retrievePaymentConsent(RetrievePaymentConsentRequest $request, string $consentId)
    {
        $provider = $this->getProvider($request);

        return $provider->retrievePaymentConsent($consentId);
    }

    public function confirmationPaymentIntent(ConfirmationPaymentIntentRequest $request)
    {
        $provider = $this->getProvider($request);

        // 1) Confirm payment intent on provider side
        $response = $provider->confirmationPaymentIntent($request->validated());

        // 2) Get booking_id from our DB by payment_intent_id (any row is fine)
        $paymentIntentId = $request->input('payment_intent_id');

        /** @var ApiBookingPaymentInit|null $paymentInit */
        $paymentInit = ApiBookingPaymentInit::query()
            ->where('payment_intent_id', $paymentIntentId)
            // we only need booking_id, so we can safely take the latest row
            ->latest('id')
            ->first();

        if ($paymentInit && $paymentInit->booking_id) {
            // 3) Fetch client email and booking_item from ApiBookingInspector
            [$email, $bookingItem] = ApiBookingInspectorRepository::getBookingContactEmailAndItemByBookingId(
                $paymentInit->booking_id
            );

            // 4) Send confirmation email if we have all required data
            if ($email && $bookingItem) {
                Mail::to($email)->queue(new BookingClientConfirmationMail($bookingItem));
            }
        }

        // 5) Return provider response as-is
        return $response;
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
