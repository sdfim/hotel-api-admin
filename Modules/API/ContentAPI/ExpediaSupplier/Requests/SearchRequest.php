<?php

namespace Modules\API\ContentAPI\ExpediaSupplier\Requests;

use Modules\API\Validate\ApiRequest;
use Illuminate\Support\Facades\Auth;


class SearchRequest extends ApiRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
		return [
            'checkin' => 'string',
            'checkout' => 'string',
            'destination' => ['required', 'string'],
            'rating' => 'string',
			'room1' => ['required', 'string'],
			'room2' => 'string',
			'room3' => 'string',
        ];
    }

	public function validatedDate(): array
    {
        $search = parent::validated();
        $search['type'] = 'search';
        
        return $search;
    }

}
