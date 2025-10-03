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
 *         @OA\JsonContent(
 *             ref="#/components/schemas/CreatePaymentIntentRequest",
 *             example={
 *                     "pprovider": "airwallex",
 *                     "order": {
 *                         "products": {
 *                             {
 *                                 "name": "Signature Family Room",
 *                                 "quantity": 1
 *                             }
 *                         }
 *                     },
 *                     "amount": 622.46,
 *                     "currency": "USD",
 *                     "booking_id": "767390c7-f9b7-4e1b-ad4d-16866270926f",
 *                     "descriptor": "Hotel Booking Payment",
 *                     "return_url": "https://domen/search-results/767390c7-f9b7-4e1b-ad4d-16866270926f/booking-confirmed",
 *                     "merchant_order_id": "BK-1759069790539"
 *                 }
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Airwallex API response",
 *         @OA\JsonContent(
 *             type="object",
 *             example={
 *                 "success": true,
 *                 "data": {
 *                     "request_id": "8329f85d-515d-4cee-a014-c3c2eb0edbc2",
 *                     "amount": 622.46,
 *                     "currency": "USD",
 *                     "merchant_order_id": "BK-1759069790539",
 *                     "order": {
 *                         "products": {
 *                             {
 *                                 "name": "Signature Family Room",
 *                                 "quantity": 1
 *                             }
 *                         }
 *                     },
 *                     "descriptor": "Hotel Booking Payment",
 *                     "metadata": {
 *                         "foo": "bar"
 *                     },
 *                     "status": "REQUIRES_PAYMENT_METHOD",
 *                     "captured_amount": 0,
 *                     "created_at": "2025-10-02T14:51:56+0000",
 *                     "updated_at": "2025-10-02T14:51:56+0000",
 *                     "available_payment_method_types": {
 *                         "card",
 *                         "airwallex_pay",
 *                         "googlepay",
 *                         "applepay"
 *                     },
 *                     "payment_method_options": {
 *                         "card": {
 *                             "card_input_via": "moto"
 *                         }
 *                     },
 *                     "client_secret": "eyJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTk0MTY3MTYsImV4cCI6MTc1OTQyMDMxNiwidHlwZSI6ImNsaWVudC1zZWNyZXQiLCJwYWRjIjoiSEsiLCJhY2NvdW50X2lkIjoiMTdhMDY0MzktNzJiYy00YWQyLWI3NjYtMTk0OWM3MTFiYTgwIiwiaW50ZW50X2lkIjoiaW50X2hrZG1tNnI2cmhibnN2cWMwM3giLCJidXNpbmVzc19uYW1lIjoiRm9yYSBUcmF2ZWwgRGVtbyJ9.eTc-QvGOwj0VWx6c5T7e8PMx9PNIRRvpgarjuWqz8WQ",
 *                     "return_url": "https://domen/search-results/767390c7-f9b7-4e1b-ad4d-16866270926f/booking-confirmed",
 *                     "base_amount": 622.46,
 *                     "base_currency": "USD",
 *                     "payment_intent_id": "int_hkdmm6r6rhbnsvqc03x"
 *                 },
 *                 "message": "success"
 *             }
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
 *     @OA\Property(property="request_id", type="string", format="uuid", example="b01737e5-c5ab-4765-8834-cbd92dfeaf81"),
 *     @OA\Property(property="booking_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="descriptor", type="string", example="Airwallex - Test Descriptor"),
 *     @OA\Property(property="return_url", type="string", example="https://www.airwallex.com"),
 *     @OA\Property(property="metadata", type="object", example={"foo":"bar"}),
 *     @OA\Property(property="client_secret", type="string", example="eyJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MzgzMDY2MzAsImV4cCI6MTczODMxMDIzMCwidHlwZSI6ImNsaWVudC1zZWNyZXQiLCJwYWRjIjoiSEsiLCJhY2NvdW50X2lkIjoiN2EwYzQ3YzctNzM0Zi00NzdmLTk1OWItMzIxNTQyNzgxYTgyIiwiaW50ZW50X2lkIjoiaW50X2hrcGRza3o3dmcxeGM3dXNjZGoiLCJjdXN0b21lcl9pZCI6IjlmYWZjMmZiLTQyNjItNGZmOC1hMjA1LWQ2MGZiNDc4MWNiMCJ9.Rg1M2Vl0GDARU0rnTghenUVe9v1ix1IrSQOxQO7Zqyw"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-31T06:57:10+0000"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-31T06:57:10+0000"),
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
            'provider' => 'sometimes|string',
        ];
    }
}
