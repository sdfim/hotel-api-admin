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
            'type' => 'required|in:hotel,flight',
            'rating' => 'numeric|between:1,5.5',
            'page' => 'integer|between:1,1000',
            'results_per_page' => 'integer|between:1,1000',
            'destination' => 'required_without_all:latitude,longitude|integer|min:1',
            'latitude' => 'required_without:destination|decimal:2,8|min:-90|max:90',
            'longitude' => 'required_without:destination|decimal:2,8|min:-180|max:180',
            'radius' => 'required_without:destination|numeric|between:1,100',
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
