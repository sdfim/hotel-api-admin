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
            // 'booking_item' => 'required|size:36',
            'booking_id' => 'required|size:36',
			'booking_items' => 'array',
            'booking_items.*' => 'size:36',
            'passengers' => 'required|array',
			'passengers.*.title' => 'required|string',
            'passengers.*.given_name' => 'required|string',
            'passengers.*.family_name' => 'required|string',
			'passengers.*.date_of_birth' => 'required|date_format:Y-m-d|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
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
