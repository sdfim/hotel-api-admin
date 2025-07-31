<?php

namespace Modules\API\PushContent\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class HotelTraderContentProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'messageId' => 'required|string|max:255',
            'propertyCode' => 'required|string|max:255',
            'rateplanCode' => 'required|string|max:255',
            'products' => 'required|array|min:1',
            'products.*.rateplanCode' => 'required|string|max:255',
            'products.*.roomtypeCode' => 'required|string|max:255',
            'products.*.taxes' => 'required|array|min:1',
            'products.*.taxes.*' => 'required|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $messageId = $this->input('messageId', Str::uuid()->toString());
        $errors = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = [
                    'errorCode' => '4005',
                    'errorMessage' => $message,
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
}

