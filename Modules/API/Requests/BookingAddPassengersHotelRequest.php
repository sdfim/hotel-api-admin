<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;


class BookingAddPassengersHotelRequest extends ApiRequest
{
     /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_item' => 'required|size:36',
            'booking_id' => 'required|size:36',
            'title' => 'required|string',
			'first_name' => 'required|string',
			'last_name' => 'required|string',
            'rooms' => 'required|array',
            'rooms.*.given_name' => 'required|string',
            'rooms.*.family_name' => 'required|string',
			'rooms.*.date_birth_adults' => 'string',
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
