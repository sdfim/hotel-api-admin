<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingChangeHardBookHotelRequest extends ApiRequest
{
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
            'search_id' => 'required|uuid',
            'booking_id' => 'required|uuid',
            'booking_item' => 'required|uuid',
            'new_booking_item' => 'required|uuid',

            'passengers' => 'array',
            'passengers.*.title' => 'required_with:passengers|in:mr,Mr,MR,ms,Ms,MS,Mrs,MRS,mrs,Miss,MISS,miss,Dr,dr,DR,Prof,prof,PROF',
            'passengers.*.given_name' => 'required_with:passengers|string|between:1,255',
            'passengers.*.family_name' => 'required_with:passengers|string|between:1,255',
            'passengers.*.date_of_birth' => 'required_with:passengers|date_format:Y-m-d',
            'passengers.*.room' => 'numeric',
        ];
    }
}
