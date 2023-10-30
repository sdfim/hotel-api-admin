<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\API\Validate\ApiRequest;
use Illuminate\Support\Facades\Auth;

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
            'checkin' => ['required', 'date_format:Y-m-d', 'after:today'],
            'checkout' => ['required', 'date_format:Y-m-d', 'after:checkin'],
            'destination' => ['required', function ($attribute, $value, $fail) {
				if (!is_string($value) && !is_int($value)) {
					$fail('The destination must be a string or an integer.');
				}
			}],
            'travel_purpose' => ['string'],
            'rating' => ['required', 'string'],
            'occupancy' => ['required', 'array'],
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
