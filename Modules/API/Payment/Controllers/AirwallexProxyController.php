<?php

namespace Modules\API\Payment\Controllers;

use App\Models\AirwallexApiLog;
use App\Models\ApiBookingPaymentInit;
use App\Models\Enums\PaymentStatusEnum;
use Modules\API\BaseController;
use Modules\API\Payment\Requests\ConfirmationPaymentIntentRequest;
use Modules\API\Payment\Requests\CreatePaymentIntentRequest;
use Modules\API\Suppliers\AirwallexSupplier\AirwallexClient;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Payment | Airwallex",
 *     description="Proxy endpoints for Airwallex API"
 * )
 */
class AirwallexProxyController extends BaseController
{
    public function __construct(
        private AirwallexClient $client
    ) {}

    public function createPaymentIntent(CreatePaymentIntentRequest $request)
    {
        $validated = $request->validated();

        $direction = [
            'client_rq' => $request->all(),
            'token' => $request->bearerToken(),
        ];

        $result = $this->client->createPaymentIntent(
            $validated['amount'],
            $validated['currency'],
            $validated['merchant_order_id'],
            $validated['order'],
            $validated['descriptor'] ?? null,
            $validated['return_url'] ?? null,
            $validated['metadata'] ?? [],
            $direction,
            $validated['booking_id']
        );

        if ($data['error'] = $result['error'] ?? null) {
            return $this->sendError($data['error'], 'Airwallex API error', 400);
        }

        if (isset($result['id'])) {
            $result['payment_intent_id'] = $result['id'];
            unset($result['id']);
        }

        // Создаём лог AirwallexApiLog
        $log = AirwallexApiLog::create([
            'method' => 'createPaymentIntent',
            'payment_intent_id' => $result['payment_intent_id'] ?? null,
            'direction' => $direction,
            'payload' => $validated,
            'response' => $result,
            'status_code' => $result['status_code'] ?? null,
            'booking_id' => $validated['booking_id'],
        ]);

        ApiBookingPaymentInit::create([
            'booking_id' => $validated['booking_id'],
            'payment_intent_id' => $result['payment_intent_id'] ?? null,
            'action' => PaymentStatusEnum::INIT->value,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'provider' => 'airwallex',
            'related_id' => $log->id,
            'related_type' => AirwallexApiLog::class,
        ]);

        $data = $result;

        return $this->sendResponse($data, 'success');
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
        $logs = \App\Models\AirwallexApiLog::where('booking_id', $booking_id)
            ->where('method', 'createPaymentIntent')
            ->where('status_code', 201)
            ->get();

        if ($logs->isEmpty()) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

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

    public function confirmationPaymentIntent(ConfirmationPaymentIntentRequest $request)
    {
        $validated = $request->validated();

        // Retrieve booking_id by payment_intent_id from previous createPaymentIntent log
        $log = AirwallexApiLog::where('payment_intent_id', $validated['payment_intent_id'])
            ->where('method', 'createPaymentIntent')
            ->latest()
            ->first();
        $bookingId = $log ? $log->booking_id : null;

        if (! $bookingId) {
            return $this->sendError('Booking ID not found for the given payment_intent_id', 'Error', 404);
        }

        ApiBookingPaymentInit::create([
            'booking_id' => $bookingId,
            'payment_intent_id' => $validated['payment_intent_id'],
            'action' => PaymentStatusEnum::CONFIRMED->value,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'provider' => 'airwallex',
            'related_id' => $log?->id,
            'related_type' => $log ? AirwallexApiLog::class : null,
        ]);

        // Prepare response (can be customized as needed)
        $response = [
            'payment_intent_id' => $validated['payment_intent_id'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'booking_id' => $bookingId,
        ];

        $payload['amount'] = $validated['amount'];
        $payload['currency'] = $validated['currency'];

        // Log the confirmation
        AirwallexApiLog::create([
            'method' => 'confirmationPaymentIntent',
            'direction' => $request->all(),
            'payload' => $payload,
            'response' => $response,
            'status_code' => 200,
            'payment_intent_id' => $validated['payment_intent_id'],
            'booking_id' => $bookingId,
        ]);

        return $this->sendResponse($response, 'success');
    }
}
