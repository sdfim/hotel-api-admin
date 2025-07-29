<?php

namespace Modules\API\PushContent\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
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
        $model = app(\App\Models\HotelTraderContentRoomType::class);
        $connection = $model->getConnectionName();
        $table = $model->getTable();

        $common = [
            'messageId' => 'required|string|max:255',
            'updateType' => 'required|string|in:NEW,UPDATE,DISABLE,ENABLE',
            'propertyCode' => 'required|string|max:255',
        ];

        // Если это обновление (PUT/PATCH/UPDATE) — только для одного room
        if (($this->isMethod('PUT') || $this->isMethod('PATCH')) || $this->input('updateType') === 'UPDATE') {
            $roomRules = [
                'room' => 'required|array',
                'room.code' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'room.name' => 'required|string|max:255',
                'room.longDescription' => 'nullable|string',
                'room.shortDescription' => 'required|string',
                'room.maxAdultOccupancy' => 'required|integer|min:1',
                'room.minAdultOccupancy' => 'required|integer|min:0',
                'room.maxChildOccupancy' => 'required|integer|min:0',
                'room.minChildOccupancy' => 'required|integer|min:0',
                'room.totalMaxOccupancy' => 'required|integer|min:1',
                'room.maxOccupancyForDefaultPrice' => 'required|integer|min:1',
                'room.bedtypes' => 'required|array|min:1',
                'room.bedtypes.*.code' => 'required|string',
                'room.bedtypes.*.name' => 'required|string',
                'room.amenities' => 'required|array|min:1',
                'room.amenities.*.code' => 'required|string',
                'room.amenities.*.name' => 'required|string',
            ];

            return array_merge($common, $roomRules);
        }

        // Для создания — только массив rooms
        $uniqueRule = "unique:{$connection}.{$table},code,NULL,id,hotel_code,{$this->input('propertyCode')}";
        $roomsRules = [
            'rooms' => 'required|array|min:1',
            'rooms.*.code' => [
                'required',
                'string',
                'max:255',
                $uniqueRule,
            ],
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

        return array_merge($common, $roomsRules);
    }

    public function prepareForValidation()
    {
        if ($this->has('room') && ! $this->has('rooms')) {
            $this->merge(['rooms' => [$this->input('room')]]);
        }
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
