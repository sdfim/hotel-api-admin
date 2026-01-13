<?php

namespace Modules\API\Payment\Controllers;

use App\Contracts\PaymentProviderInterface;
use App\Mail\BookingAgentNotificationMail;
use App\Mail\BookingClientConfirmationMail;
use App\Models\ApiBookingPaymentInit;
use App\Models\User;
use App\Repositories\ApiBookingInspectorRepository;
use App\Support\PaymentProviderResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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
        $validated = $request->validated();
        $provider = $this->getProvider($request);
        $providerName = $validated['provider'] ?? config('payment.default_provider');

        // 1) Confirm payment on provider side (Airwallex or Cybersource)
        $response = $provider->confirmationPaymentIntent($validated);

        // 2) Resolve booking_id depending on provider
        $bookingId = null;

        if ($providerName === 'airwallex') {
            $paymentIntentId = $validated['payment_intent_id'] ?? null;

            if ($paymentIntentId) {
                /** @var ApiBookingPaymentInit|null $paymentInit */
                $paymentInit = ApiBookingPaymentInit::query()
                    ->where('payment_intent_id', $paymentIntentId)
                    ->latest('id')
                    ->first();

                $bookingId = $paymentInit?->booking_id;
            }
        } elseif ($providerName === 'cybersource') {
            // For Cybersource we receive booking_id directly from the request
            $bookingId = $validated['booking_id'] ?? null;
        }

        // 3) Send confirmation email (if booking_id was found)
        $this->sendClientConfirmationMailForBookingId($bookingId);

        // 4) Return provider response as-is
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

    /**
     * Sends BookingClientConfirmationMail for a given booking ID.
     */
    private function sendClientConfirmationMailForBookingId(?string $bookingId): void
    {
        if (! $bookingId) {
            return;
        }

        $items = ApiBookingInspectorRepository::bookedItems($bookingId);

        foreach ($items as $item) {
            // Send confirmation email for each item after booking
            $email_notification = Arr::get(json_decode($item->request, true), 'booking_contact.email');

            if ($email_notification) {
                try {
                    Mail::to($email_notification)->queue(new BookingClientConfirmationMail($item->booking_item));
                } catch (\Throwable $mailException) {
                    Log::error('Booking confirmation email queue error: '.$mailException->getMessage());
                }

                [$agentEmail, $agentId, $externalAdvisorEmail] = ApiBookingInspectorRepository::getEmailAgentBookingItem($item->booking_item);
                $notificationEmails = User::find($agentId)?->notification_emails ?? [];
                $notificationEmails = array_unique(array_merge($notificationEmails, [$externalAdvisorEmail]));
                foreach ($notificationEmails as $email) {
                    if (empty($email)) {
                        continue;
                    }
                    try {
                        Mail::to($email)->queue(new BookingAgentNotificationMail($item->booking_item));
                    } catch (\Exception $e) {
                        Log::error('Failed to send agent notification email for booking item '.$item->booking_item.': '.$e->getMessage(), ['email' => $email]);
                    }
                }
            }
        }
    }
}
