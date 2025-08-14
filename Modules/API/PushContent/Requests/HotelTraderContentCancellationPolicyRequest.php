<?php

namespace Modules\API\PushContent\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class HotelTraderContentCancellationPolicyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $model = app(\App\Models\HotelTraderContentCancellationPolicyPush::class);
        $connection = $model->getConnectionName();
        $table = $model->getTable();

        $common = [
            'messageId' => 'required|string|max:255',
            'updateType' => 'required|string|in:NEW,UPDATE,DISABLE,ENABLE',
            'propertyCode' => 'required|string|max:255',
        ];

        if (($this->isMethod('PUT') || $this->isMethod('PATCH')) || $this->input('updateType') === 'UPDATE') {
            $policyRules = [
                'cancellationPolicy' => 'required|array',
                'cancellationPolicy.code' => 'required|string|max:255',
                'cancellationPolicy.name' => 'required|string|max:255',
                'cancellationPolicy.description' => 'nullable|string',
                'cancellationPolicy.penaltyWindows' => 'required|array|min:1',
                'cancellationPolicy.penaltyWindows.*.relativeDeadline' => 'required|array',
                'cancellationPolicy.penaltyWindows.*.relativeDeadline.relativeTo' => 'required|string',
                'cancellationPolicy.penaltyWindows.*.relativeDeadline.type' => 'required|string',
                'cancellationPolicy.penaltyWindows.*.relativeDeadline.offsetUnit' => 'required|string',
                'cancellationPolicy.penaltyWindows.*.relativeDeadline.offsetValue' => 'required|integer',
                'cancellationPolicy.penaltyWindows.*.penalty' => 'required|array',
                'cancellationPolicy.penaltyWindows.*.penalty.percent' => 'nullable|numeric',
                'cancellationPolicy.penaltyWindows.*.penalty.nights' => 'nullable|integer',
                'cancellationPolicy.penaltyWindows.*.penalty.taxInclusive' => 'required|boolean',
            ];

            return array_merge($common, $policyRules);
        }

        $uniqueRule = "unique:{$connection}.{$table},code,NULL,id,hotel_code,{$this->input('propertyCode')}";
        $policiesRules = [
            'cancellationPolicies' => 'required|array|min:1',
            'cancellationPolicies.*.code' => [
                'required',
                'string',
                'max:255',
                $uniqueRule,
            ],
            'cancellationPolicies.*.name' => 'required|string|max:255',
            'cancellationPolicies.*.description' => 'nullable|string',
            'cancellationPolicies.*.penaltyWindows' => 'required|array|min:1',
            'cancellationPolicies.*.penaltyWindows.*.relativeDeadline' => 'required|array',
            'cancellationPolicies.*.penaltyWindows.*.relativeDeadline.relativeTo' => 'required|string',
            'cancellationPolicies.*.penaltyWindows.*.relativeDeadline.type' => 'required|string',
            'cancellationPolicies.*.penaltyWindows.*.relativeDeadline.offsetUnit' => 'required|string',
            'cancellationPolicies.*.penaltyWindows.*.relativeDeadline.offsetValue' => 'required|integer',
            'cancellationPolicies.*.penaltyWindows.*.penalty' => 'required|array',
            'cancellationPolicies.*.penaltyWindows.*.penalty.percent' => 'nullable|numeric',
            'cancellationPolicies.*.penaltyWindows.*.penalty.nights' => 'nullable|integer',
            'cancellationPolicies.*.penaltyWindows.*.penalty.taxInclusive' => 'required|boolean',
        ];

        return array_merge($common, $policiesRules);
    }

    public function prepareForValidation()
    {
        if ($this->has('cancellationPolicy') && ! $this->has('cancellationPolicies')) {
            $this->merge(['cancellationPolicies' => [$this->input('cancellationPolicy')]]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $messageId = $this->input('messageId', Str::uuid()->toString());
        $objectCode = $this->input('cancellationPolicies.0.code', 'CANCEL_POLICY');

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
