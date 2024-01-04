<?php

namespace Modules\API\Requests;

use App\Models\Supplier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class PriceHotelRequest extends ApiRequest
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
        $validCurrencies = [
            'AED', 'ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'CNY', 'COP', 'DKK', 'EGP',
            'EUR', 'GBP', 'HKD', 'IDR', 'ILS', 'INR', 'JPY', 'KRW', 'LBP', 'MAD',
            'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'QAR', 'RUB', 'SAR', 'SEK',
            'SGD', 'THB', 'TRY', 'TWD', 'USD', 'VND', 'ZAR'
        ];

        $supplier = Supplier::get()->pluck('name')->map('ucfirst')->toArray();

        $occupancy = request()->occupancy;
        if (is_null($occupancy)) return [
            'occupancy' => ['required', function ($attribute, $value, $fail) {
                $fail('The occupancy must be an array.');
            }],
        ];
        foreach ($occupancy as $key => $value) {
            if (empty($value['children_ages']) && isset($value['children_ages'])) return [
                'occupancy.' . $key . '.children_ages' => ['required'],
            ];
            else
                if (isset($value['children']) && !isset($value['children_ages'])) return [
                    'occupancy.' . $key . '.children_ages' => 'required|array',
                ];
                else if (isset($value['children']) && (count($value['children_ages']) !== $value['children'])) return [
                    'occupancy.' . $key . '.children_ages' => ['required',
                        function ($attribute, $value, $fail) {
                            $fail('The number of children must equal the number of records of their age children_ages.');
                        }
                    ],
                ];
        }

        return [
            'type' => 'required|string',
            'currency' => ['string', 'in:' . implode(',', $validCurrencies)],
            'hotel_name' => 'string',
            'supplier' => ['string', 'in:' . implode(',', $supplier)],
            'checkin' => 'required|date_format:Y-m-d|after:today',
            'checkout' => 'required|date_format:Y-m-d|after:checkin',
            'destination' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_string($value) && !is_int($value)) {
                        $fail('The destination must be a string or a number.');
                    } elseif (is_int($value) && (int)$value <= 0) {
                        $fail('The destination must be a non-negative integer.');
                    } elseif (is_int($value) && strlen((string)$value) > 6) {
                        $fail('The destination must be an integer with 6 or fewer digits.');
                    }
                },
            ],
            'rating' => 'numeric|between:1,5.5',
            'occupancy' => 'required|array',
            'occupancy.*.adults' => 'required|numeric|between:1,9',
            'occupancy.*.children' => 'numeric',
            'occupancy.*.children_ages' => 'array',
            'occupancy.*.children_ages.*' => 'numeric|between:0,17',
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
