<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;


class BookingChangeBookHotelRequest extends ApiRequest
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
			'booking_id' => 'required|size:36',
			'booking_item' => 'required|size:36',
            'query' => 'required|array',
            'query.given_name' => 'required|string|between:1,255',
            'query.family_name' => 'required|string|between:1,255',
            'query.smoking' => 'required|boolean',
            'query.special_request' => 'string|max:255',
            'query.loyalty_id' => 'string|max:10',
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
