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
            'email' => 'required|email',
            'phone.country_code' => 'required|numeric|digits_between:1,4',
            'phone.area_code' => 'required|numeric|digits_between:1,5',
            'phone.number' => 'required|numeric|digits_between:1,10',
            'rooms' => 'required|array',
            'rooms.*.given_name' => 'required|string',
            'rooms.*.family_name' => 'required|string',
            'payments' => 'required|array',
            'payments.*.billing_contact' => 'required|array',
            'payments.*.billing_contact.given_name' => 'required|string',
            'payments.*.billing_contact.family_name' => 'required|string',
            'payments.*.billing_contact.address' => 'required|array',
            'payments.*.billing_contact.address.line_1' => 'required|string',
            'payments.*.billing_contact.address.city' => 'required|string',
            'payments.*.billing_contact.address.state_province_code' => 'required|string',
            'payments.*.billing_contact.address.postal_code' => 'required|string',
            'payments.*.billing_contact.address.country_code' => 'required|string|size:2',
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
