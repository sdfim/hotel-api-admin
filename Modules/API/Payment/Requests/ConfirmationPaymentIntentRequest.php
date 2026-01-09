<?php

namespace Modules\API\Payment\Requests;

use Modules\API\Requests\Traits\ValidatesApiClient;
use Modules\API\Validate\ApiRequest;

/**
 * @OA\Post(
 *     path="/api/payment/confirmation",
 *     tags={"Payment"},
 *     summary="Confirm payment (Airwallex or Cybersource)",
 *     @OA\RequestBody(
 *         required=true,
 *         description="Confirm a payment by provider type",
 *         @OA\JsonContent(
 *             discriminator=@OA\Discriminator(
 *                 propertyName="provider",
 *                 mapping={
 *                     "airwallex"="#/components/schemas/ConfirmPaymentAirwallex",
 *                     "cybersource"="#/components/schemas/ConfirmPaymentCybersource"
 *                 }
 *             ),
 *             oneOf={
 *                 @OA\Schema(ref="#/components/schemas/ConfirmPaymentAirwallex"),
 *                 @OA\Schema(ref="#/components/schemas/ConfirmPaymentCybersource")
 *             },
 *             @OA\Examples(
 *                 example="Airwallex Confirmation",
 *                 summary="Confirm Airwallex payment",
 *                 value={
 *                     "provider":"airwallex",
 *                     "payment_intent_id":"int_hkdmrjzd6hb1gge4rv0",
 *                     "amount":100.5,
 *                     "currency":"USD"
 *                 }
 *             ),
 *             @OA\Examples(
 *                 example="Cybersource Confirmation",
 *                 summary="Confirm Cybersource payment",
 *                 value={
 *                     "provider":"cybersource",
 *                     "booking_id":"13c2cf26-77b2-411f-a0a6-082da1d61b41",
 *                     "amount":100.5,
 *                     "currency":"USD",
 *                     "transient_token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Payment confirmed"),
 *     @OA\Response(response=400, description="Bad request"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Schema(
 *     schema="ConfirmPaymentAirwallex",
 *     required={"payment_intent_id","amount","currency"},
 *     @OA\Property(property="provider", type="string", example="airwallex"),
 *     @OA\Property(property="payment_intent_id", type="string"),
 *     @OA\Property(property="amount", type="number", format="float"),
 *     @OA\Property(property="currency", type="string")
 * )
 *
 * @OA\Schema(
 *     schema="ConfirmPaymentCybersource",
 *     required={"booking_id","amount","currency","transient_token"},
 *     @OA\Property(property="provider", type="string", example="cybersource"),
 *     @OA\Property(property="booking_id", type="string", format="uuid"),
 *     @OA\Property(property="amount", type="number", format="float"),
 *     @OA\Property(property="currency", type="string"),
 *     @OA\Property(property="transient_token", type="string"),
 *     @OA\Property(property="billing_first_name", type="string"),
 *     @OA\Property(property="billing_last_name", type="string"),
 *     @OA\Property(property="billing_email", type="string", format="email"),
 *     @OA\Property(property="billing_address1", type="string"),
 *     @OA\Property(property="billing_locality", type="string"),
 *     @OA\Property(property="billing_administrative_area", type="string"),
 *     @OA\Property(property="billing_postal_code", type="string"),
 *     @OA\Property(property="billing_country", type="string", example="US")
 * )
 */
class ConfirmationPaymentIntentRequest extends ApiRequest
{
    use ValidatesApiClient;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Allow both transientToken and transient_token from frontend
        if ($this->has('transientToken') && !$this->has('transient_token')) {
            $this->merge([
                'transient_token' => $this->input('transientToken'),
            ]);
        }
    }

    public function rules(): array
    {
        $provider = $this->input('provider', config('payment.default_provider'));

        // Common for all providers
        $base = [
            'amount'   => 'required|numeric',
            'currency' => 'required|string',
            'provider' => 'sometimes|string|in:airwallex,cybersource',
        ];

        if ($provider === 'cybersource') {
            // Cybersource Microform confirmation
            return $base + [
                    'booking_id'      => 'required|string|uuid',
                    'transient_token' => 'required|string',

                    // Optional billing info
                    'billing_first_name'          => 'sometimes|string',
                    'billing_last_name'           => 'sometimes|string',
                    'billing_email'               => 'sometimes|email',
                    'billing_address1'            => 'sometimes|string',
                    'billing_locality'            => 'sometimes|string',
                    'billing_administrative_area' => 'sometimes|string|max:50',
                    'billing_postal_code'         => 'sometimes|string|max:20',
                    'billing_country'             => 'sometimes|string|size:2',
                ];
        }

        // Default: Airwallex
        return $base + [
                'payment_intent_id' => 'required|string',
            ];
    }
}
