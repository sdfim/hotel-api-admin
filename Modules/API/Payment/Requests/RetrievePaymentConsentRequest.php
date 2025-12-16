<?php

namespace Modules\API\Payment\Requests;

use Modules\API\Requests\Traits\ValidatesApiClient;
use Modules\API\Validate\ApiRequest;

/**
 * @OA\Get(
 *     path="/api/payment/payment-consent/{consentId}",
 *     summary="Retrieve Payment Consent",
 *     tags={"Payment"},
 *
 *     @OA\Parameter(
 *         name="consentId",
 *         in="path",
 *         required=true,
 *         description="Payment Consent ID",
 *
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Payment consent retrieved successfully",
 *
 *         @OA\JsonContent(
 *             example={
 *                 "success": true,
 *                 "data": {
 *                     "consent_id": "pmt_cst_123456789",
 *                     "response": {
 *                         "id": "pmt_cst_123456789",
 *                         "status": "ACTIVE",
 *                         "customer_id": "cus_123456789",
 *                         "created_at": "2025-01-31T06:57:10+0000"
 *                     }
 *                 },
 *                 "message": "Payment consent retrieved successfully"
 *             }
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Validation error",
 *
 *         @OA\JsonContent(
 *             example={"message":"The given data was invalid.","errors":{"consentId":{"The consentId field is required."}}}
 *         )
 *     )
 * )
 */
class RetrievePaymentConsentRequest extends ApiRequest
{
    use ValidatesApiClient;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consentId' => 'required|string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'consentId' => $this->route('consentId'), // Извлекаем параметр пути
        ]);
    }
}
