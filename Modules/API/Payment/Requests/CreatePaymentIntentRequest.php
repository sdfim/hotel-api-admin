<?php

namespace Modules\API\Payment\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Post(
 *     path="/api/payment/create",
 *     summary="Proxy for Airwallex createPaymentIntent",
 *     tags={"Payment"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/CreatePaymentIntentRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Airwallex API response",
 *         @OA\JsonContent(
 *             type="object",
 *             example={"id":"int_xxx","status":"REQUIRES_PAYMENT_METHOD"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             example={"message":"The given data was invalid.","errors":{"amount":{"The amount field is required."}}}
 *         )
 *     )
 * )
 * @OA\Schema(
 *     schema="CreatePaymentIntentRequest",
 *     required={"amount","currency","merchant_order_id","request_id","descriptor","return_url","order"},
 *     @OA\Property(property="amount", type="number", format="float", example=100),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="merchant_order_id", type="string", example="D202503210001"),
 *     @OA\Property(property="booking_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="descriptor", type="string", example="Airwallex - Test Descriptor"),
 *     @OA\Property(property="return_url", type="string", example="https://www.airwallex.com"),
 *     @OA\Property(property="metadata", type="object", example={"foo":"bar"}),
 *     @OA\Property(
 *         property="order",
 *         type="object",
 *         required={"products"},
 *         @OA\Property(
 *             property="products",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 required={"name","quantity"},
 *                 @OA\Property(property="name", type="string", example="Product Name"),
 *                 @OA\Property(property="quantity", type="integer", example=2)
 *             )
 *         )
 *     )
 * )
 */
class CreatePaymentIntentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'merchant_order_id' => 'required|string',
            'booking_id' => 'required|string|uuid',
            'descriptor' => 'string',
            'return_url' => 'string',
            'metadata' => 'array',
            'order' => 'required|array',
            'order.products' => 'required|array|min:1',
            'order.products.*.name' => 'required|string',
            'order.products.*.quantity' => 'required|numeric',
        ];
    }
}
