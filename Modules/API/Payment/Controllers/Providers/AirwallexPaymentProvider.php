<?php

namespace Modules\API\Payment\Controllers\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Models\AirwallexApiLog;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingPaymentInit;
use App\Models\Enums\PaymentStatusEnum;
use App\Repositories\ApiBookingItemRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\API\BaseController;
use Modules\API\Payment\Airwallex\Client\AirwallexClient;

class AirwallexPaymentProvider extends BaseController implements PaymentProviderInterface
{
    public function __construct(
        private AirwallexClient $client
    ) {}

    /**
     * @throws GuzzleException
     */
    public function createPaymentIntent(array $data)
    {
        $direction = [
            'client_rq' => $data,
            'token' => $data['token'] ?? null,
        ];

        // Check if isset deposit then create customer and payment consent
        $bookingId = Arr::get($data, 'booking_id');
        $deposit = ApiBookingItemRepository::getDepositData($bookingId);
        if (! empty($deposit)) {
            $book = ApiBookingInspector::where('booking_id', $bookingId)
                ->where('type', 'book')
                ->where('sub_type', 'create')
                ->where('status', 'success')
                ->first();
            $bookRequest = json_decode($book?->request, true);
            $user = Arr::get($bookRequest, 'booking_contact.first_name', 'Test User');
            $email = Arr::get($bookRequest, 'booking_contact.email', 'test.user@example.com');

            $uniqueId = $user.time();
            $merchantOrderId = $data['merchant_order_id'] ?? 'ORDER_PI_'.time();
            [$customer, $createCustomerPayload] = $this->client->createCustomer($uniqueId, $user, $email);
            $this->logAirwallexApiData($data, 'createCustomer', $customer, $direction, $createCustomerPayload);

            $customerId = $customer['id'];
            [$consent, $createPaymentConsentPayload] = $this->client->createPaymentConsent($customerId, $merchantOrderId);
            $this->logAirwallexApiData($data, 'createPaymentConsent', $consent, $direction, $createPaymentConsentPayload);
            $consentId = $consent['id'];

            $data['customer_id'] = $customerId;
        }

        [$result, $payload] = $this->client->createPaymentIntent($data) ?: [[], []];

        $this->logAirwallexApiData($data, 'createPaymentIntent', $result, $direction, $payload);
        if ($error = $result['error'] ?? null) {
            return $this->sendError($error, 'Airwallex API error', 400);
        }

        if (isset($result['id'])) {
            $result['payment_intent_id'] = $result['id'];
            unset($result['id']);
        }

        $responseData = $result;

        return $this->sendResponse($responseData, 'success');
    }

    /**
     * Создание нового Payment Intent для оставшейся суммы (MoFoF).
     * Требует передачи в $data: booking_id, amount, currency, customer_id.
     *
     * @throws GuzzleException
     */
    public function createPaymentIntentMoFoF(string $bookingId, float $amount)
    {
        $data = [];
        $airwallexApiLog = AirwallexApiLog::where('booking_id', $bookingId)
            ->where('method', 'createPaymentIntent')
            ->where('status_code', 201)
            ->latest()
            ->first();
        $data['consent_id'] = $airwallexApiLog->response['customer_payment_consents']['customer_id'] ?? null;
        $data = $airwallexApiLog->payload ?? [];
        $data['booking_id'] = $bookingId;
        $data['amount'] = $amount;
        [$result, $payload] = $this->client->createPaymentIntent($data) ?: [[], []];

        $this->logAirwallexApiData($data, 'createPaymentIntentMoFoF', $result, [], $payload);

        if ($error = $result['error'] ?? null) {
            logger('Airwallex MIT API error: '.$error);

            return $this->sendError($error, 'Airwallex MIT API error', 400);
        }

        $this->confirmPaymentIntentMoFoF($result['id'], $amount, $currency = $data['currency'] ?? 'USD');

        if (isset($result['id'])) {
            $result['payment_intent_id'] = $result['id'];
            unset($result['id']);
        }

        return $this->sendResponse($result, 'success');
    }

    /**
     * Подтверждение Payment Intent для MoFoF с использованием согласия на оплату.
     *
     * @throws GuzzleException
     */
    public function confirmPaymentIntentMoFoF(string $paymentIntentId, float $amount, string $currency = 'USD'): void
    {
        $airwallexApiLog = AirwallexApiLog::where('payment_intent_id', $paymentIntentId)
            ->where('method', 'createPaymentIntentMoFoF')
            ->where('status_code', 201)
            ->first();
        $consentId = $airwallexApiLog->response['customer_payment_consents'][0]['id'] ?? null;
        $customerId = $airwallexApiLog->response['customer_payment_consents'][0]['customer_id'] ?? null;
        $paymentMethodId = $airwallexApiLog->response['customer_payment_consents'][0]['payment_method']['id'] ?? null;

        if (! $paymentIntentId || ! $consentId || ! $paymentMethodId) {
            logger('Airwallex MIT API error: Missing payment_intent_id or consent_id ', [
                'paymentIntentId' => $paymentIntentId,
                'customer_payment_consents' => $airwallexApiLog->response['customer_payment_consents'][0]['customer_id'],
            ]);

            return;
        }

        $confirmPayload = [
            'request_id' => Str::uuid()->toString(),
            'payment_consent_id' => $consentId,
            'customer_id' => $customerId,
        ];

        [$result, $payload] = $this->client->confirmPaymentIntentWithConsent($paymentIntentId, $confirmPayload) ?: [[], []];

        if ($error = $result['error'] ?? null) {
            logger('Airwallex MIT API error: '.$error);

            return;
        }

        $logData = [
            'booking_id' => $airwallexApiLog->booking_id,
            'payment_intent_id' => $paymentIntentId,
            'amount' => $amount,
            'currency' => $currency,
        ];
        $this->logAirwallexApiData($logData, 'confirmationPaymentIntentMoFoF', $result, [], $payload);
    }

    private function logAirwallexApiData(array $data, string $method, array $result, array $direction, array $payload): void
    {
        $payment_intent_id = in_array($method, ['createPaymentIntent', 'createPaymentIntentMoFoF', 'confirmationPaymentIntentMoFoF'])
            ? ($result['id'] ?? null) : null;
        $airwallexApiLogData = [
            'method' => $method,
            'payment_intent_id' => $payment_intent_id,
            'method_action_id' => $result['id'] ?? null,
            'direction' => $direction,
            'payload' => $payload,
            'response' => $result,
            'status_code' => $result['status_code'] ?? 201,
            'booking_id' => $data['booking_id'],
        ];

        if ($error = $result['error'] ?? null) {
            $airwallexApiLogData['status_code'] = $result['status_code'] ?? 400;
            AirwallexApiLog::create($airwallexApiLogData);

            return;
        }

        $log = AirwallexApiLog::create($airwallexApiLogData);

        if (in_array($method, ['createPaymentIntent', 'createPaymentIntentMoFoF', 'confirmationPaymentIntentMoFoF']) && $log) {
            $action = $method === 'confirmationPaymentIntentMoFoF' ? PaymentStatusEnum::CONFIRMED->value : PaymentStatusEnum::INIT->value;
            ApiBookingPaymentInit::create([
                'booking_id' => $log->booking_id,
                'payment_intent_id' => $log->payment_intent_id,
                'action' => $action,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'provider' => 'airwallex',
                'related_id' => $log->id,
                'related_type' => AirwallexApiLog::class,
            ]);
        }
    }

    /**
     * Retrieve Payment Consent by ID
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws GuzzleException
     */
    public function retrievePaymentConsent($consentId)
    {
        [$result, $payload] = $this->client->retrievePaymentConsent($consentId);
        $data['booking_id'] = AirwallexApiLog::where('method', 'createPaymentConsent')
            ->where('method_action_id', $consentId)
            ->latest()
            ->value('booking_id');
        $this->logAirwallexApiData($data, 'retrievePaymentConsent', $result, [], $payload);

        if ($error = $result['error'] ?? null) {
            return $this->sendError($error, 'Airwallex API error', 400);
        }

        $data = [
            'consent_id' => $consentId,
            'response' => $result,
        ];

        return $this->sendResponse($data, 'Payment consent retrieved successfully');
    }

    /**
     * @OA
     *
     * @OA\Get(
     *     path="/api/payment/transaction/{booking_id}",
     *     tags={"Payment"},
     *     summary="Retrieve Airwallex transactions by booking_id",
     *
     *     @OA\Parameter(
     *         name="booking_id",
     *         in="path",
     *         required=true,
     *         description="Booking ID (UUID)",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Transactions found",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *
     *                 @OA\Property(property="booking_id", type="string", example="13c2cf26-77b2-411f-a0a6-082da1d61b41"),
     *                 @OA\Property(property="transaction_id", type="string", example="int_hkdmgdff7hb5zyo7snp"),
     *                 @OA\Property(property="amount", type="integer", example=100),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="merchant_order_id", type="string", example="D202503210001"),
     *                 @OA\Property(property="date", type="string", example="2025-09-16 11:19:37"),
     *                 @OA\Property(property="request_id", type="string", example="1494cbc3-28c0-47de-992e-5c72b6f6f031")
     *             ),
     *             example={
     *                 {
     *                     "booking_id": "13c2cf26-77b2-411f-a0a6-082da1d61b41",
     *                     "transaction_id": "int_hkdmgdff7hb5zyo7snp",
     *                     "amount": 100,
     *                     "currency": "USD",
     *                     "merchant_order_id": "D202503210001",
     *                     "date": "2025-09-16 11:19:37",
     *                     "request_id": "1494cbc3-28c0-47de-992e-5c72b6f6f031"
     *                 },
     *                 {
     *                     "booking_id": "13c2cf26-77b2-411f-a0a6-082da1d61b41",
     *                     "transaction_id": "int_hkdmgdff7hb60b9co5j",
     *                     "amount": 150,
     *                     "currency": "USD",
     *                     "merchant_order_id": "D202503210002",
     *                     "date": "2025-09-16 11:32:18",
     *                     "request_id": "20c2233a-bdb7-4c2d-9935-b6e748b4f72d"
     *                 },
     *                 {
     *                     "booking_id": "13c2cf26-77b2-411f-a0a6-082da1d61b41",
     *                     "transaction_id": "int_hkdmgdff7hb60nfl3yb",
     *                     "amount": 150,
     *                     "currency": "USD",
     *                     "merchant_order_id": "D202503210003",
     *                     "date": "2025-09-16 11:44:34",
     *                     "request_id": "8f1a785b-f09f-4ff7-9fec-354739df02cd"
     *                 },
     *                 {
     *                     "booking_id": "13c2cf26-77b2-411f-a0a6-082da1d61b41",
     *                     "transaction_id": "int_hkdmvbt8lhb60nj54nc",
     *                     "amount": 200,
     *                     "currency": "USD",
     *                     "merchant_order_id": "D202503210004",
     *                     "date": "2025-09-16 11:44:40",
     *                     "request_id": "ba336715-ef04-4ac1-b756-31ea3e5ec870"
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found",
     *
     *         @OA\JsonContent(
     *             example={"error": "Transaction not found"}
     *         )
     *     )
     * )
     */
    public function getTransactionByBookingId($booking_id)
    {
        $logsConfirmation = \App\Models\AirwallexApiLog::where('booking_id', $booking_id)
            ->where('method', 'confirmationPaymentIntent')
            ->where('status_code', 200)
            ->get()
            ->pluck('payment_intent_id');

        if ($logsConfirmation->isEmpty()) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $logs = \App\Models\AirwallexApiLog::where('booking_id', $booking_id)
            ->where('method', 'createPaymentIntent')
            ->where('status_code', 201)
            ->whereIn('payment_intent_id', $logsConfirmation)
            ->get();

        $transactions = [];
        foreach ($logs as $log) {
            $response = is_array($log->response) ? $log->response : json_decode($log->response, true);
            $transactions[] = [
                'booking_id' => $log->booking_id,
                'transaction_id' => $response['id'] ?? null,
                'amount' => $response['amount'] ?? null,
                'currency' => $response['currency'] ?? null,
                'merchant_order_id' => $response['merchant_order_id'] ?? null,
                'date' => $log->created_at->toDateTimeString(),
                'request_id' => $response['request_id'] ?? null,
            ];
        }

        return response()->json($transactions);
    }

    /**
     * @OA\Get(
     *     path="/api/payment/payment-intent/{id}",
     *     tags={"Payment"},
     *     summary="Retrieve Airwallex Payment Intent",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment Intent ID",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment intent retrieved successfully",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "success": true,
     *                 "data": {
     *                     "payment_intent_id": "int_hkdmrjzd6hb1gge4rv0",
     *                     "request_id": "123",
     *                     "amount": 100,
     *                     "currency": "USD",
     *                     "merchant_order_id": "D202503210001",
     *                     "available_payment_method_types": {
     *                         "card",
     *                         "airwallex_pay",
     *                         "googlepay",
     *                         "applepay"
     *                     }
     *                 },
     *                 "message": "success"
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Error retrieving payment intent",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "data": {},
     *                 "success": false,
     *                 "error": "not_found",
     *                 "message": "Airwallex API error"
     *             }
     *         )
     *     )
     * )
     */
    public function retrievePaymentIntent($id)
    {
        $result = $this->client->getPaymentIntent($id);

        if ($error = $result['error'] ?? null) {
            return $this->sendError($error, 'Airwallex API error', 400);
        }

        $data['payment_intent_id'] = $result['id'] ?? null;
        $data['request_id'] = $result['request_id'] ?? null;
        $data['amount'] = $result['amount'] ?? null;
        $data['currency'] = $result['currency'] ?? null;
        $data['merchant_order_id'] = $result['merchant_order_id'] ?? null;
        $data['available_payment_method_types'] = $result['available_payment_method_types'] ?? null;

        return $this->sendResponse($data, 'success');
    }

    public function confirmationPaymentIntent(array $data)
    {
        // Retrieve booking_id by payment_intent_id from previous createPaymentIntent log
        $log = AirwallexApiLog::where('payment_intent_id', $data['payment_intent_id'])
            ->where('method', 'createPaymentIntent')
            ->latest()
            ->first();
        $bookingId = $log ? $log->booking_id : null;

        if (! $bookingId) {
            return $this->sendError('Booking ID not found for the given payment_intent_id', 'Error', 404);
        }

        $paymentInit = ApiBookingPaymentInit::create([
            'booking_id' => $bookingId,
            'payment_intent_id' => $data['payment_intent_id'],
            'action' => PaymentStatusEnum::CONFIRMED->value,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'provider' => 'airwallex',
            'related_id' => $log?->id,
            'related_type' => $log ? AirwallexApiLog::class : null,
        ]);

        // Prepare response (can be customized as needed)
        $response = [
            'payment_intent_id' => $data['payment_intent_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'booking_id' => $bookingId,
        ];

        $payload['amount'] = $data['amount'];
        $payload['currency'] = $data['currency'];

        // Log the confirmation
        $logConfirm = AirwallexApiLog::create([
            'method' => 'confirmationPaymentIntent',
            'direction' => $data,
            'payload' => $payload,
            'response' => $response,
            'status_code' => 200,
            'payment_intent_id' => $data['payment_intent_id'],
            'method_action_id' => $data['payment_intent_id'],
            'booking_id' => $bookingId,
        ]);

        $paymentInit->update(['related_id' => $logConfirm->id, 'related_type' => AirwallexApiLog::class]);

        return $this->sendResponse($response, 'success');
    }
}
