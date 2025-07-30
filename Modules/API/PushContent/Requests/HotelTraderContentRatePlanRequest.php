<?php

namespace Modules\API\PushContent\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class HotelTraderContentRatePlanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $model = app(\App\Models\HotelTraderContentRatePlan::class);
        $connection = $model->getConnectionName();
        $table = $model->getTable();

        $common = [
            'messageId' => 'required|string|max:255',
            'updateType' => 'required|string|in:NEW,UPDATE,DISABLE,ENABLE',
            'propertyCode' => 'required|string|max:255',
        ];

        if (($this->isMethod('PUT') || $this->isMethod('PATCH')) || $this->input('updateType') === 'UPDATE') {
            $rateplanRules = [
                'rateplan' => 'required|array',
                'rateplan.code' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'rateplan.name' => 'required|string|max:255',
                'rateplan.currency' => 'required|array',
                'rateplan.currency.code' => 'required|string',
                'rateplan.currency.name' => 'required|string',
                'rateplan.shortDescription' => 'required|string',
                'rateplan.detailDescription' => 'nullable|string',
                'rateplan.cancellationPolicyCode' => 'nullable|string',
                'rateplan.mealplan' => 'nullable|array',
                'rateplan.mealplan.mealplanCode' => 'required_with:rateplan.mealplan|string',
                'rateplan.mealplan.mealplanName' => 'required_with:rateplan.mealplan|string',
                'rateplan.mealplan.mealplanDescription' => 'nullable|string',
                'rateplan.isTaxInclusive' => 'required|boolean',
                'rateplan.isRefundable' => 'required|boolean',
                'rateplan.rateplanType' => 'required|array',
                'rateplan.rateplanType.*' => 'string',
                'rateplan.isPromo' => 'required|boolean',
                'rateplan.destinationExclusive' => 'nullable|array',
                'rateplan.destinationExclusive.type' => 'required_with:rateplan.destinationExclusive|string',
                'rateplan.destinationExclusive.name' => 'required_with:rateplan.destinationExclusive|string',
                'rateplan.destinationExclusive.code' => 'required_with:rateplan.destinationExclusive|string',
                'rateplan.destinationRestriction' => 'nullable|array',
                'rateplan.seasonalPolicies' => 'nullable|array',
                'rateplan.seasonalPolicies.*.name' => 'required_with:rateplan.seasonalPolicies|string',
                'rateplan.seasonalPolicies.*.code' => 'required_with:rateplan.seasonalPolicies|string',
                'rateplan.seasonalPolicies.*.startDate' => 'required_with:rateplan.seasonalPolicies|date',
                'rateplan.seasonalPolicies.*.endDate' => 'required_with:rateplan.seasonalPolicies|date',
            ];

            return array_merge($common, $rateplanRules);
        }

        $uniqueRule = "unique:{$connection}.{$table},code,NULL,id,hotel_code,{$this->input('propertyCode')}";
        $rateplansRules = [
            'rateplans' => 'required|array|min:1',
            'rateplans.*.code' => [
                'required',
                'string',
                'max:255',
                $uniqueRule,
            ],
            'rateplans.*.name' => 'required|string|max:255',
            'rateplans.*.currency' => 'required|array',
            'rateplans.*.currency.code' => 'required|string',
            'rateplans.*.currency.name' => 'required|string',
            'rateplans.*.shortDescription' => 'required|string',
            'rateplans.*.detailDescription' => 'nullable|string',
            'rateplans.*.cancellationPolicyCode' => 'nullable|string',
            'rateplans.*.mealplan' => 'nullable|array',
            'rateplans.*.mealplan.mealplanCode' => 'required_with:rateplans.*.mealplan|string',
            'rateplans.*.mealplan.mealplanName' => 'required_with:rateplans.*.mealplan|string',
            'rateplans.*.mealplan.mealplanDescription' => 'nullable|string',
            'rateplans.*.isTaxInclusive' => 'required|boolean',
            'rateplans.*.isRefundable' => 'required|boolean',
            'rateplans.*.rateplanType' => 'required|array',
            'rateplans.*.rateplanType.*' => 'string',
            'rateplans.*.isPromo' => 'required|boolean',
            'rateplans.*.destinationExclusive' => 'nullable|array',
            'rateplans.*.destinationExclusive.type' => 'required_with:rateplans.*.destinationExclusive|string',
            'rateplans.*.destinationExclusive.name' => 'required_with:rateplans.*.destinationExclusive|string',
            'rateplans.*.destinationExclusive.code' => 'required_with:rateplans.*.destinationExclusive|string',
            'rateplans.*.destinationRestriction' => 'nullable|array',
            'rateplans.*.seasonalPolicies' => 'nullable|array',
            'rateplans.*.seasonalPolicies.*.name' => 'required_with:rateplans.*.seasonalPolicies|string',
            'rateplans.*.seasonalPolicies.*.code' => 'required_with:rateplans.*.seasonalPolicies|string',
            'rateplans.*.seasonalPolicies.*.startDate' => 'required_with:rateplans.*.seasonalPolicies|date',
            'rateplans.*.seasonalPolicies.*.endDate' => 'required_with:rateplans.*.seasonalPolicies|date',
        ];

        return array_merge($common, $rateplansRules);
    }

    public function prepareForValidation()
    {
        if ($this->has('rateplan') && ! $this->has('rateplans')) {
            $this->merge(['rateplans' => [$this->input('rateplan')]]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $messageId = $this->input('messageId', Str::uuid()->toString());
        $objectCode = $this->input('rateplans.0.code', 'RATEPLAN');
        $errors = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                $isRequired = false;
                $rules = $this->rules();
                $rule = $rules[$field] ?? null;
                if (is_string($rule) && strpos($rule, 'required') !== false) {
                    $isRequired = true;
                } elseif (is_array($rule)) {
                    foreach ($rule as $r) {
                        if (is_string($r) && strpos($r, 'required') !== false) {
                            $isRequired = true;
                            break;
                        }
                    }
                }
                $errorMessage = $isRequired
                    ? "required field {$field} missing: {$message}"
                    : "invalid value for field {$field}: {$message}";
                $errors[] = [
                    'objectCode' => $objectCode,
                    'errorCode' => '4005',
                    'errorMessage' => $errorMessage,
                ];
            }
        }
        $response = [
            'messageId' => $messageId,
            'status' => [
                'success' => false,
                'message' => 'Validation failed due to invalid fields.',
                'errors' => $errors,
            ],
        ];
        throw new HttpResponseException(response()->json($response, 400));
    }

    public function messages()
    {
        return [];
    }
}
