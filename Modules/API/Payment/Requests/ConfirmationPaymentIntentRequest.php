<?php

namespace Modules\API\Payment\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Post(
 *     path="/api/payment/confirmation",
 *     tags={"Payment"},
 *     summary="Confirm Airwallex payment intent",
 *     requestBody=@OA\RequestBody(
 *         required=true,
 *         description="Confirmation of Airwallex payment intent",
 *         @OA\JsonContent(
 *             required={"payment_intent_id", "amount", "currency"},
 *             @OA\Property(property="payment_intent_id", type="string", example="int_hkdmrjzd6hb1gge4rv0"),
 *             @OA\Property(property="amount", type="number", format="float", example=100.50),
 *             @OA\Property(property="currency", type="string", example="USD"),
 *             example={
 *                 "payment_intent_id": "int_hkdmrjzd6hb1gge4rv0",
 *                 "amount": 100.50,
 *                 "currency": "USD"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payment intent confirmed",
 *         @OA\JsonContent(
 *             example={
 *                 "success": true,
 *                 "data": {
 *                     "payment_intent_id": "int_hkdmrjzd6hb1gge4rv0",
 *                     "amount": 100.50,
 *                     "currency": "USD",
 *                     "booking_id": "13c2cf26-77b2-411f-a0a6-082da1d61b41"
 *                 },
 *                 "message": "Payment intent confirmed"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */

class ConfirmationPaymentIntentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'payment_intent_id' => 'required|string',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
        ];
    }
}
