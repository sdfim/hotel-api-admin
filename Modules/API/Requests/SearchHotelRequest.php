<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SearchHotelRequest extends ApiRequest
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
            'destination' => ['required', 'string'],
            'rating' => [ 'numeric'],
			'page' => [ 'integer'],
			'results_per_page' => [ 'integer'],
        ];
    }

    public function validatedDate (): array
    {
        return parent::validated();
    }

}
