<?php

namespace Modules\API\Payment\Controllers;

use Modules\API\BaseController;
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
            $validated['descriptor'] ?? null,
            $validated['return_url'] ?? null,
            $validated['metadata'] ?? [],
            $direction
        );

        if ($data['error'] = $result['error'] ?? null) {
            return $this->sendError($data['error'], 'Airwallex API error', 400);
        }

        $data['payment_intent_id'] = $result['id'] ?? null;
        $data['request_id'] = $result['request_id'] ?? null;

        return $this->sendResponse($data, 'success');
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
}
