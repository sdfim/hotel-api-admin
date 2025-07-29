<?php

namespace Modules\API\PushContent\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class HotelTraderContentRoomTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'messageId' => 'required|string|max:255',
            'updateType' => 'required|string|in:NEW,UPDATE,DISABLE,ENABLE',
            'propertyCode' => 'required|string|max:255',
            'rooms' => 'required|array|min:1',
            'rooms.*.code' => 'required|string|max:255',
            'rooms.*.name' => 'required|string|max:255',
            'rooms.*.longDescription' => 'nullable|string',
            'rooms.*.shortDescription' => 'required|string',
            'rooms.*.maxAdultOccupancy' => 'required|integer|min:1',
            'rooms.*.minAdultOccupancy' => 'required|integer|min:0',
            'rooms.*.maxChildOccupancy' => 'required|integer|min:0',
            'rooms.*.minChildOccupancy' => 'required|integer|min:0',
            'rooms.*.totalMaxOccupancy' => 'required|integer|min:1',
            'rooms.*.maxOccupancyForDefaultPrice' => 'required|integer|min:1',
            'rooms.*.bedtypes' => 'required|array|min:1',
            'rooms.*.bedtypes.*.code' => 'required|string',
            'rooms.*.bedtypes.*.name' => 'required|string',
            'rooms.*.amenities' => 'required|array|min:1',
            'rooms.*.amenities.*.code' => 'required|string',
            'rooms.*.amenities.*.name' => 'required|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $messageId = $this->input('messageId', Str::uuid()->toString());
        $objectCode = $this->input('rooms.0.code', 'ROOM');
        $errors = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                $isRequired = false;
                $rules = $this->rules();
                if (isset($rules[$field]) && strpos($rules[$field], 'required') !== false) {
                    $isRequired = true;
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
