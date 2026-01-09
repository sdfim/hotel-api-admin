<?php

namespace Modules\API\Payment\Controllers\Providers;

use App\Contracts\PaymentProviderInterface;
use CyberSource\ApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\API\BaseController;
use Modules\API\Payment\Cybersource\Client\CybersourceClient;
use Modules\API\Payment\Cybersource\Client\CybersourceValidator;
use Throwable;

class CybersourcePaymentProvider extends BaseController implements PaymentProviderInterface
{
    public function __construct(
        private readonly CybersourceClient    $client,
        private readonly CybersourceValidator $validator,
    ) {
    }

    /**
     * Payment Create endpoint.
     *
     * 1. Determine origin.
     * 2. Generate capture context via SDK.
     * 3. Validate its signature and expiration.
     * 4. Return JWT to the frontend.
     */
    public function createPaymentIntent(array $data)
    {
        try {
            $origin = $data['origin'] ?? config('cybersource.default_origin');

            if (empty($origin)) {
                return $this->sendError(
                    'Missing origin for Cybersource Microform.',
                    'Validation Error',
                );
            }

            $captureContext = $this->client->generateCaptureContext($origin);

            if (!$this->validator->validateCaptureContext($captureContext)) {
                return $this->sendError('Generated capture context failed validation.', 'Validation Error', 500);
            }

            // Store captureContext temporarily so we can validate transient token later.
            $bookingId = $data['booking_id'] ?? null;
            if ($bookingId) {
                $this->cacheCaptureContext($bookingId, $captureContext);
            }

            return $this->sendResponse(
                [
                    'captureContext' => $captureContext,
                    'booking_id'     => $data['booking_id'] ?? null,
                ],
                'Capture context generated successfully.'
            );
        } catch (Throwable $e) {
            Log::error('Cybersource Payment Create failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return $this->sendError(
                'Internal server error.',
                'Internal Server Error',
                500
            );
        }
    }

    /**
     * Payment Confirmation for Cybersource.
     *
     * Expected payload (for provider=cybersource):
     *  - booking_id       (uuid)
     *  - amount           (float)
     *  - currency         (string)
     *  - transient_token  (string) – Microform transient token JWT
     *  - optional billing_* fields (first_name, last_name, email, address1, locality, administrative_area, postal_code, country)
     */
    public function confirmationPaymentIntent(array $data)
    {
        try {
            $bookingId      = $data['booking_id'] ?? null;
            $transientToken = $data['transient_token'] ?? null;

            if (!$bookingId || !$transientToken) {
                return $this->sendError(
                    'Missing booking_id or transient_token for Cybersource confirmation.',
                    'Validation Error',
                    422
                );
            }

            $amount   = (float) ($data['amount'] ?? 0);
            $currency = $data['currency'] ?? 'USD';

            // Build minimal billTo information.
            $billTo = $this->buildBillToPayload($bookingId, $data);

            $captureContext = $this->getCachedCaptureContext($bookingId);
            if (!$captureContext) {
                return $this->sendError(
                    'Capture context not found or expired. Please refresh checkout and try again.',
                    'Validation Error',
                    422
                );
            }

            // (Optional but recommended) ensure cached captureContext is still valid.
            if (!$this->validator->validateCaptureContext($captureContext)) {
                return $this->sendError(
                    'Capture context is invalid or expired. Please refresh checkout and try again.',
                    'Validation Error',
                    422
                );
            }

            // Validate transient token integrity using embedded JWK from capture context.
            if (!$this->validator->validateTransientToken($transientToken, $captureContext)) {
                return $this->sendError(
                    'Transient token validation failed. Please retry payment.',
                    'Validation Error',
                    422
                );
            }

            $payment = $this->client->createPaymentWithTransientToken(
                $transientToken,
                $amount,
                $currency,
                $billTo
            );

            $status = strtoupper((string) ($payment['status'] ?? ''));

            $isAuthorized = $status === 'AUTHORIZED';
            $isPending    = $status === 'PENDING'; // optional
            $isPaid       = in_array($status, ['CAPTURED', 'SETTLED'], true);

            // Currently, “success” = authorization completed (and/or pending/paid)
            $isSuccessful = $isAuthorized || $isPending || $isPaid;

            if (!$isSuccessful) {
                Log::warning('Cybersource payment not successful.', [
                    'booking_id' => $bookingId,
                    'status'     => $status ?: null,
                    'response'   => $payment,
                ]);

                return $this->sendError(
                    'Cybersource payment was not successful.',
                    'Payment Error',
                    402
                );
            }

            Cache::forget($this->cacheKeyForBooking($bookingId));

            return $this->sendResponse(
                [
                'booking_id'     => $bookingId,
                'amount'         => $amount,
                'currency'       => $currency,
                'status'         => $status,
                'is_authorized'  => $isAuthorized,
                'is_paid'        => $isPaid, // almost always false for now
                'payment'        => $payment,
            ],
                $isPaid
                ? 'Cybersource payment captured successfully.'
                : 'Cybersource payment authorized successfully.'
            );
        } catch (ApiException $e) {
            Log::error('Cybersource ApiException', [
                'code'            => $e->getCode(),
                'message'         => $e->getMessage(),
                'response_body'   => $e->getResponseBody(),
                'response_header' => $e->getResponseHeaders(),
            ]);

            return $this->sendError(
                'Cybersource request failed.',
                'Payment Error',
                502
            );
        } catch (Throwable $e) {
            Log::error('Cybersource confirmation failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return $this->sendError(
                'Internal server error during Cybersource confirmation.',
                'Internal Server Error',
                500
            );
        }
    }

    /**
     * Build basic billing information for Cybersource payment.
     *
     * You can later replace this with data from your Booking models.
     */
    private function buildBillToPayload(string $bookingId, array $data): array
    {
        return [
            'reference'  => $bookingId,
            'firstName'  => $data['billing_first_name'] ?? 'Guest',
            'lastName'   => $data['billing_last_name'] ?? 'Customer',
            'email'      => $data['billing_email'] ?? $data['email'] ?? 'no-reply@example.com',
            'address1'   => $data['billing_address1'] ?? 'N/A',
            'locality'   => $data['billing_locality'] ?? 'N/A',
            'postalCode' => $data['billing_postal_code'] ?? '00000',
            'country'    => $data['billing_country'] ?? 'US',
            'administrativeArea' => $data['administrativeArea'] ?? null
        ];
    }

    public function retrievePaymentConsent($id)
    {
        return $this->sendError('Not implemented', 'Error');
    }

    public function retrievePaymentIntent($id)
    {
        return $this->sendError('Not implemented', 'Error');
    }

    public function getTransactionByBookingId($bookingId)
    {
        return $this->sendError('Not implemented', 'Error');
    }

    private function cacheKeyForBooking(string $bookingId): string
    {
        return "cybs:capture_context:booking:$bookingId";
    }

    private function cacheCaptureContext(string $bookingId, string $captureContext): void
    {
        // Compute TTL based on captureContext exp to avoid deleting too early.
        $exp = $this->validator->getExp($captureContext);
        $now = time();

        // If exp is missing, fallback to 15 minutes.
        $ttlSeconds = 15 * 60;

        if ($exp !== null) {
            $ttlSeconds = max(60, $exp - $now); // at least 60s
            $ttlSeconds = min($ttlSeconds, 60 * 60); // cap at 60 minutes (tunable)
        }

        Cache::put($this->cacheKeyForBooking($bookingId), $captureContext, $ttlSeconds);
    }

    private function getCachedCaptureContext(string $bookingId): ?string
    {
        $val = Cache::get($this->cacheKeyForBooking($bookingId));
        return is_string($val) && $val !== '' ? $val : null;
    }
}
