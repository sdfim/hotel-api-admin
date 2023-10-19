<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;
use Illuminate\Support\Facades\Auth;

class PriceHotelRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize (): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules (): array
    {
        return [
            'checkin' => ['required', 'date_format:Y-m-d', 'after:today'],
            'checkout' => ['required', 'date_format:Y-m-d', 'after:checkin'],
            'destination' => ['required', 'string'],
			'travel_purpose' => ['string'],
            'rating' => ['required', 'string'],
            'occupancy' => ['required', 'array'],
        ];
    }

    public function validatedDate (): array
    {
        return parent::validated();
    }

}
