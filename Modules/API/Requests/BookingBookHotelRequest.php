<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingBookHotelRequest extends ApiRequest
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
            'amount_pay' => 'required|in:Deposit,Full Payment',
            'email' => 'required|email',
            'phone.country_code' => 'required|numeric',
            'phone.area_code' => 'required|numeric',
            'phone.number' => 'required|numeric',
            'booking_contact.given_name' => 'required|string',
            'booking_contact.family_name' => 'required|string',
            'booking_contact.address.line_1' => 'required|string',
            'booking_contact.address.city' => 'required|string',
            'booking_contact.address.state_province_code' => 'required|string',
            'booking_contact.address.postal_code' => 'required|string',
            'booking_contact.address.country_code' => 'required|string',
            'credit_card.name_card' => 'sometimes|string',
            'credit_card.number' => 'sometimes|numeric',
            'credit_card.card_type' => 'sometimes|string',
            'credit_card.expiry_date' => 'sometimes|string',
            'credit_card.cvv' => 'sometimes|numeric',
            'credit_card.billing_address' => 'sometimes|string',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
