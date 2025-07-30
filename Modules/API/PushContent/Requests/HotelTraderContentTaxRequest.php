<?php

namespace Modules\API\PushContent\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class HotelTraderContentTaxRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $model = app(\App\Models\HotelTraderContentTax::class);
        $connection = $model->getConnectionName();
        $table = $model->getTable();

        $common = [
            'messageId' => 'required|string|max:255',
            'updateType' => 'required|string|in:NEW,UPDATE,DISABLE,ENABLE',
            'propertyCode' => 'required|string|max:255',
        ];

        if (($this->isMethod('PUT') || $this->isMethod('PATCH')) || $this->input('updateType') === 'UPDATE') {
            $taxRules = [
                'tax' => 'required|array',
                'tax.code' => 'required|string|max:255',
                'tax.name' => 'required|string|max:255',
                'tax.percentOrFlat' => 'required|string|in:PERCENT,FLAT',
                'tax.chargeFrequency' => 'required|string',
                'tax.chargeBasis' => 'required|string',
                'tax.value' => 'required|numeric',
                'tax.taxType' => 'nullable|string',
                'tax.appliesToChildren' => 'required|boolean',
                'tax.payAtProperty' => 'required|boolean',
            ];
            return array_merge($common, $taxRules);
        }

        $uniqueRule = "unique:{$connection}.{$table},code,NULL,id,hotel_code,{$this->input('propertyCode')}";
        $taxesRules = [
            'taxes' => 'required|array|min:1',
            'taxes.*.code' => [
                'required',
                'string',
                'max:255',
                $uniqueRule,
            ],
            'taxes.*.name' => 'required|string|max:255',
            'taxes.*.percentOrFlat' => 'required|string|in:PERCENT,FLAT',
            'taxes.*.chargeFrequency' => 'required|string',
            'taxes.*.chargeBasis' => 'required|string',
            'taxes.*.value' => 'required|numeric',
            'taxes.*.taxType' => 'nullable|string',
            'taxes.*.appliesToChildren' => 'required|boolean',
            'taxes.*.payAtProperty' => 'required|boolean',
        ];
        return array_merge($common, $taxesRules);
    }

    public function prepareForValidation()
    {
        if ($this->has('tax') && !$this->has('taxes')) {
            $this->merge(['taxes' => [$this->input('tax')]]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $messageId = $this->input('messageId', Str::uuid()->toString());
        $isUpdate = ($this->isMethod('PUT') || $this->isMethod('PATCH')) || $this->input('updateType') === 'UPDATE';
        $objectCode = $isUpdate
            ? $this->input('tax.code', 'TAX')
            : $this->input('taxes.0.code', 'TAX');
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

