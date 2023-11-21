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
		if (!isset(request()->destination)) {
			if (!isset(request()->latitude)) return ['latitude' => 'required', 'numeric', 'between:-90,90'];
			if (!isset(request()->longitude)) return ['longitude' => 'required', 'numeric', 'between:-180,180'];
			if (!isset(request()->radius)) return ['radius' => 'required', 'numeric', 'between:1,100'];

		}
        return [
            'destination' => [function ($attribute, $value, $fail) {
				if (!is_string($value) && !is_int($value)) {
					$fail('The destination must be a string or an integer.');
				}
			}],
            'rating' => 'numeric|between:1,5.5',
            'page' => 'integer|between:1,1000',
            'results_per_page' => 'integer|between:1,1000',
			'latitude' => 'numeric|between:-90,90',
			'longitude' => 'numeric|between:-180,180',
			'radius' => 'numeric|between:1,100',
			'supplier' => 'string',
			'hotel_name' => 'string',
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
