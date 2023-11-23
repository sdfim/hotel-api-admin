<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\API\Validate\ApiRequest;
use Illuminate\Support\Facades\Auth;

class BookingBookRequest extends ApiRequest
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
        $rules = [
			'booking_id' => 'required|size:36',
			'amount_pay' => 'required|string|in:Deposit,Full Payment',
			'booking_contact.first_name' => 'required|string',
			'booking_contact.last_name' => 'required|string',
			'booking_contact.email' => 'required|email',
			'booking_contact.phone.country_code' => 'required|string',
			'booking_contact.phone.area_code' => 'required|string',
			'booking_contact.phone.number' => 'required|string',
			'booking_contact.address.line_1' => 'required|string',
			'booking_contact.address.city' => 'required|string',
			'booking_contact.address.state_province_code' => 'required|string',
			'booking_contact.address.postal_code' => 'required|string',
			'booking_contact.address.country_code' => 'required|string',
		];
	
		if (request()->has('credit_card')) {
			$rules['credit_card.name_card'] = 'required|string';
			$rules['credit_card.number'] = 'required|numeric|digits:16';
			$rules['credit_card.card_type'] = 'required|string|in:MSC,VISA,AMEX,DIS';
			$rules['credit_card.expiry_date'] = 'required|date_format:m/Y|after_or_equal:today';
			$rules['credit_card.cvv'] = 'required|numeric';
			$rules['credit_card.billing_address'] = 'nullable|string';
		}
	
		return $rules;
    }

    /**
     * @return array
     */
    public function validatedDate(): array
    {
        return parent::validated();
    }
}
