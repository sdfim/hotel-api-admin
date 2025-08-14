<?php

namespace Modules\API\PushContent\Requests;

use App\Models\HotelTraderContentHotelPush;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class HotelTraderContentHotelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $model = app(HotelTraderContentHotelPush::class);
        $connection = $model->getConnectionName();
        $table = $model->getTable();

        // Modify unique rule for update operations
        $uniqueRule = "unique:{$connection}.{$table},code";
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $code = $this->route('code');
            if ($code) {
                $uniqueRule .= ",{$code},code";
            }
        }

        return [
            'messageId' => 'required|string|max:255',
            'updateType' => 'required|string|in:NEW,UPDATE,DISABLE,ENABLE',
            'hotel.code' => "required|string|{$uniqueRule}|max:255",
            'hotel.mappingProviders' => 'nullable|array',
            'hotel.name' => 'required|string|max:255',
            'hotel.starRating' => 'nullable|numeric',
            'hotel.defaultCurrencyCode' => 'nullable|string|max:3',
            'hotel.maxRoomsBookable' => 'nullable|integer',
            'hotel.numberOfRooms' => 'nullable|integer',
            'hotel.numberOfFloors' => 'nullable|integer',
            'hotel.addressLine1' => 'required|string|max:255',
            'hotel.addressLine2' => 'nullable|string|max:255',
            'hotel.city' => 'required|string|max:255',
            'hotel.state' => 'nullable|string|max:255',
            'hotel.stateCode' => 'nullable|string|max:255',
            'hotel.country' => 'required|string|max:255',
            'hotel.countryCode' => 'required|string|max:255',
            'hotel.zip' => 'nullable|max:20',
            'hotel.phone1' => 'nullable|string|max:50',
            'hotel.phone2' => 'nullable|string|max:50',
            'hotel.fax1' => 'nullable|string|max:50',
            'hotel.fax2' => 'nullable|string|max:50',
            'hotel.websiteUrl' => 'nullable|string|max:255',
            'hotel.longitude' => 'nullable|string|max:255',
            'hotel.latitude' => 'nullable|string|max:255',
            'hotel.longDescription' => 'nullable|string',
            'hotel.shortDescription' => 'string',
            'hotel.checkInTime' => 'nullable|string|max:20',
            'hotel.checkOutTime' => 'nullable|string|max:20',
            'hotel.timeZone' => 'nullable|string|max:50',
            'hotel.adultAge' => 'nullable|integer',
            'hotel.defaultLanguage' => 'nullable|string|max:10',
            'hotel.adultOnly' => 'nullable|boolean',
            'hotel.currencies' => 'nullable|array',
            'hotel.languages' => 'nullable|array',
            'hotel.creditCardTypes' => 'nullable|array',
            'hotel.bedtypes' => 'nullable|array',
            'hotel.amenities' => 'nullable|array',
            'hotel.ageCategories' => 'nullable|array',
            'hotel.checkInPolicy' => 'nullable|string',
            'hotel.images' => 'nullable|array',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Get the message ID from the request or generate a new one if not present
        $messageId = $this->input('messageId', Str::uuid()->toString());

        // Get the hotel.code from the request or use default HTPKG
        $objectCode = $this->input('hotel.code', 'HTPKG');

        // Format validation errors
        $errors = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                // Check if the field is required based on validation rules
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

        // Create the response structure
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
