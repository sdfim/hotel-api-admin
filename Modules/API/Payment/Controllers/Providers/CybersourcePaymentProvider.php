<?php

namespace Modules\API\Payment\Controllers\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Models\ApiBookingPaymentInit;
use App\Models\CybersourceApiLog;
use App\Models\Enums\PaymentStatusEnum;
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

            [$result, $payload] = $this->client->generateCaptureContext($origin);

            $captureContext = $result['captureContext'];

            if (!$this->validator->validateCaptureContext($captureContext)) {
                $this->logCybersourceApiData($data, 'createPaymentIntent', $result, ['origin' => $origin], $payload);
                return $this->sendError('Generated capture context failed validation.', 'Validation Error', 500);
            }

            // Store captureContext temporarily so we can validate transient token later.
            $bookingId = $data['booking_id'] ?? null;
            if ($bookingId) {
                $this->cacheCaptureContext($bookingId, $captureContext);
            }

            $this->logCybersourceApiData($data, 'createPaymentIntent', $result, $data, $payload);

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

            $this->logCybersourceApiData($data, 'createPaymentIntent', ['error' => $e->getMessage(), 'status_code' => 500], ['origin' => $data['origin'] ?? null], []);

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

            $amount = (float) ($data['amount'] ?? 0);
            $currency = $data['currency'] ?? 'USD';

            // Build minimal billTo information.
            $billTo = $this->buildBillToPayload($bookingId, $data);

            $captureContext = $this->getCachedCaptureContext($bookingId);
            if (!$captureContext) {
                Log::warning('Cybersource confirmation failed: Capture context not found in cache.', [
                    'booking_id' => $bookingId,
                    'cache_key' => $this->cacheKeyForBooking($bookingId),
                ]);

                return $this->sendError(
                    'Capture context not found or expired. Please refresh checkout and try again.',
                    'Validation Error',
                    422
                );
            }

            // (Optional but recommended) ensure cached captureContext is still valid.
            if (!$this->validator->validateCaptureContext($captureContext)) {
                Log::warning('Cybersource confirmation failed: Cached capture context failed validation.', [
                    'booking_id' => $bookingId,
                ]);

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

            [$payment, $payload] = $this->client->createPaymentWithTransientToken(
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

            $this->logCybersourceApiData($data, 'confirmationPaymentIntent', $payment, $data, $payload);

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

    private function logCybersourceApiData(array $data, string $method, array $result, array $direction, array $payload): void
    {
        $id = $result['id'] ?? null;
        $cybersourceApiLogData = [
            'method' => $method,
            'payment_intent_id' => $id,
            'method_action_id' => $id,
            'direction' => $direction,
            'payload' => $payload,
            'response' => $result,
            'status_code' => $result['status_code'] ?? 201,
            'booking_id' => $data['booking_id'] ?? null,
        ];

        if ($error = $result['error'] ?? null) {
            $cybersourceApiLogData['status_code'] = $result['status_code'] ?? 400;
            CybersourceApiLog::create($cybersourceApiLogData);

            return;
        }

        $log = CybersourceApiLog::create($cybersourceApiLogData);

        if (in_array($method, ['createPaymentIntent', 'confirmationPaymentIntent']) && $log) {
            $action = $method === 'confirmationPaymentIntent' ? PaymentStatusEnum::CONFIRMED->value : PaymentStatusEnum::INIT->value;
            ApiBookingPaymentInit::create([
                'booking_id' => $log->booking_id,
                'payment_intent_id' => $log->booking_id,
                'action' => $action,
                'amount' => $data['amount'] ?? 0,
                'currency' => $data['currency'] ?? 'USD',
                'provider' => 'cybersource',
                'related_id' => $log->id,
                'related_type' => CybersourceApiLog::class,
            ]);
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
        $logs = CybersourceApiLog::where('booking_id', $bookingId)
            ->where('method', 'confirmationPaymentIntent')
            ->get();

        if ($logs->isEmpty()) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $transactions = [];
        foreach ($logs as $log) {
            $response = $log->response;
            $transactions[] = [
                'booking_id' => $log->booking_id,
                'transaction_id' => $response['id'] ?? null,
                'amount' => $response['orderInformation']['amountDetails']['totalAmount'] ?? null,
                'currency' => $response['orderInformation']['amountDetails']['currency'] ?? null,
                'merchant_order_id' => $response['clientReferenceInformation']['code'] ?? null,
                'date' => $log->created_at->toDateTimeString(),
                'request_id' => $response['id'] ?? null,
            ];
        }

        return response()->json($transactions);
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

        // If exp is missing, fallback to 1 hour.
        $ttlSeconds = 60 * 60;

        if ($exp !== null) {
            // We use at least 1 hour even if JWT exp is close, to handle time drift.
            // Cybersource tokens usually last 15 mins, but if our server time is ahead,
            // we might perceive it as expiring much sooner.
            $ttlSeconds = max(3600, $exp - $now);
            $ttlSeconds = min($ttlSeconds, 24 * 3600); // cap at 24h
        }

        Log::info('Caching Cybersource capture context.', [
            'booking_id' => $bookingId,
            'ttl_seconds' => $ttlSeconds,
            'exp_diff' => $exp ? ($exp - $now) : 'N/A',
        ]);

        Cache::put($this->cacheKeyForBooking($bookingId), $captureContext, $ttlSeconds);
    }

    private function getCachedCaptureContext(string $bookingId): ?string
    {
        $val = Cache::get($this->cacheKeyForBooking($bookingId));
        return is_string($val) && $val !== '' ? $val : null;
    }
}
