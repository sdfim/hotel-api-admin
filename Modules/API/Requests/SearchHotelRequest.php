<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\API\Validate\ApiRequest;
use Illuminate\Support\Facades\Auth;

class SearchHotelRequest extends ApiRequest
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
            'destination' => ['required', function ($attribute, $value, $fail) {
				if (!is_string($value) && !is_int($value)) {
					$fail('The destination must be a string or an integer.');
				}
			}],
            'rating' => ['numeric'],
            'page' => ['integer'],
            'results_per_page' => ['integer'],
			'latitude' => ['numeric'],
			'longitude' => ['numeric'],
			'radius' => ['numeric'],
			'supplier' => ['string'],
			'hotel_name' => ['string'],
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
