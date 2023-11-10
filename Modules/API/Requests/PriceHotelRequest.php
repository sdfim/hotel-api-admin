<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class PriceHotelRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string',
            'currency' => 'required|string',
            'hotel_name' => 'required|string',
            'checkin' => 'required|date_format:Y-m-d|after:today',
            'checkout' => 'required|date_format:Y-m-d|after:checkin',
            'destination' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_string($value) && !is_int($value)) {
                        $fail('The destination must be a string or a number.');
                    } elseif (is_int($value) && (int) $value <= 0) {
                        $fail('The destination must be a non-negative integer.');
                    } elseif (is_int($value) && strlen((string) $value) > 6) {
                        $fail('The destination must be an integer with 6 or fewer digits.');
                    }
                },
            ],
            'rating' => 'required|numeric|between:1,5.5',
            'occupancy' => 'required|array',
            'occupancy.*.adults' => 'required|numeric|between:1,9',
            'occupancy.*.children' => 'numeric',
            'occupancy.*.children_ages' => 'array',
            'occupancy.*.children_ages.*' => 'numeric|between:0,17',
        ];
    }

    /**
     * @return array
     */
    public function validatedDate(): array
    {
        return parent::validated();
    }
}
