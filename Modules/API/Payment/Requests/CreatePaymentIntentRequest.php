<?php

namespace Modules\API\Payment\Requests;

use Modules\API\Requests\Traits\ValidatesApiClient;
use Modules\API\Validate\ApiRequest;

/**
 * @OA\Post(
 *     path="/api/payment/create",
 *     summary="Create payment (Airwallex or Cybersource)",
 *     tags={"Payment"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Initialize a payment intent using Airwallex or Cybersource",
 *         @OA\JsonContent(
 *             discriminator=@OA\Discriminator(
 *                 propertyName="provider",
 *                 mapping={
 *                     "airwallex"="#/components/schemas/CreatePaymentIntentRequestAirwallex",
 *                     "cybersource"="#/components/schemas/CreatePaymentIntentRequestCybersource"
 *                 }
 *             ),
 *             oneOf={
 *                 @OA\Schema(ref="#/components/schemas/CreatePaymentIntentRequestAirwallex"),
 *                 @OA\Schema(ref="#/components/schemas/CreatePaymentIntentRequestCybersource")
 *             },
 *             @OA\Examples(
 *                 example="Airwallex Example",
 *                 summary="Example for Airwallex",
 *                 value={
 *                     "provider":"airwallex",
 *                     "amount":100,
 *                     "currency":"USD",
 *                     "merchant_order_id":"D202503210001",
 *                     "booking_id":"123e4567-e89b-12d3-a456-426614174000",
 *                     "descriptor":"Hotel Booking Payment",
 *                     "return_url":"https://www.example.com/return",
 *                     "metadata":{"foo":"bar"},
 *                     "order":{
 *                         "products":{
 *                             {"name":"Room Night", "quantity":2}
 *                         }
 *                     }
 *                 }
 *             ),
 *             @OA\Examples(
 *                 example="Cybersource Example",
 *                 summary="Example for Cybersource",
 *                 value={
 *                     "provider":"cybersource",
 *                     "amount":100,
 *                     "currency":"USD",
 *                     "booking_id":"123e4567-e89b-12d3-a456-426614174000",
 *                     "origin":"https://checkout.example.com"
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Payment created successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Schema(
 *     schema="CreatePaymentIntentRequestAirwallex",
 *     required={"amount","currency","merchant_order_id","descriptor","return_url","order","booking_id"},
 *     @OA\Property(property="provider", type="string", example="airwallex"),
 *     @OA\Property(property="amount", type="number", format="float"),
 *     @OA\Property(property="currency", type="string"),
 *     @OA\Property(property="merchant_order_id", type="string"),
 *     @OA\Property(property="booking_id", type="string", format="uuid"),
 *     @OA\Property(property="descriptor", type="string"),
 *     @OA\Property(property="return_url", type="string"),
 *     @OA\Property(property="metadata", type="object"),
 *     @OA\Property(
 *         property="order",
 *         type="object",
 *         @OA\Property(
 *             property="products",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="quantity", type="integer")
 *             )
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="CreatePaymentIntentRequestCybersource",
 *     required={"amount","currency","booking_id"},
 *     @OA\Property(property="provider", type="string", example="cybersource"),
 *     @OA\Property(property="amount", type="number", format="float"),
 *     @OA\Property(property="currency", type="string"),
 *     @OA\Property(property="booking_id", type="string", format="uuid"),
 *     @OA\Property(property="origin", type="string")
 * )
 */
class CreatePaymentIntentRequest extends ApiRequest
{
    use ValidatesApiClient;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $provider = $this->input('provider', config('payment.default_provider'));

        // Basic fields for both providers
        $base = [
            'amount'     => 'required|numeric',
            'currency'   => 'required|string',
            'booking_id' => 'required|string|uuid',
            'provider'   => 'sometimes|string|in:airwallex,cybersource',
        ];

        if ($provider === 'cybersource') {
            // For Cybersource, these fields + optional origin are enough.
            return $base + [
                    'origin' => 'sometimes|string',
                ];
        }

        // Airwallex
        return $base + [
                'merchant_order_id'         => 'required|string',
                'descriptor'                => 'string',
                'return_url'                => 'string',
                'metadata'                  => 'array',
                'order'                     => 'required|array',
                'order.products'            => 'required|array|min:1',
                'order.products.*.name'     => 'required|string',
                'order.products.*.quantity' => 'required|numeric',
            ];
    }
}
