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
        // List of country codes ISO 3166-1 alpha-2
        $countryCodes = ['AF', 'AX', 'AL', 'DZ', 'AS', 'AD', 'AO', 'AI', 'AQ', 'AG', 'AR', 'AM', 'AW', 'AU', 'AT', 'AZ',
            'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BQ', 'BA', 'BW', 'BV', 'BR', 'IO', 'BN',
            'BG', 'BF', 'BI', 'CV', 'KH', 'CM', 'CA', 'KY', 'CF', 'TD', 'CL', 'CN', 'CX', 'CC', 'CO', 'KM', 'CG', 'CD',
            'CK', 'CR', 'CI', 'HR', 'CU', 'CW', 'CY', 'CZ', 'DK', 'DJ', 'DM', 'DO', 'EC', 'EG', 'SV', 'GQ', 'ER', 'EE',
            'SZ', 'ET', 'FK', 'FO', 'FJ', 'FI', 'FR', 'GF', 'PF', 'TF', 'GA', 'GM', 'GE', 'DE', 'GH', 'GI', 'GR', 'GL',
            'GD', 'GP', 'GU', 'GT', 'GG', 'GN', 'GW', 'GY', 'HT', 'HM', 'VA', 'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IR',
            'IQ', 'IE', 'IM', 'IL', 'IT', 'JM', 'JP', 'JE', 'JO', 'KZ', 'KE', 'KI', 'KP', 'KR', 'KW', 'KG', 'LA', 'LV',
            'LB', 'LS', 'LR', 'LY', 'LI', 'LT', 'LU', 'MO', 'MG', 'MW', 'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU',
            'YT', 'MX', 'FM', 'MD', 'MC', 'MN', 'ME', 'MS', 'MA', 'MZ', 'MM', 'NA', 'NR', 'NP', 'NL', 'NC', 'NZ', 'NI',
            'NE', 'NG', 'NU', 'NF', 'MK', 'MP', 'NO', 'OM', 'PK', 'PW', 'PS', 'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PL',
            'PT', 'PR', 'QA', 'RE', 'RO', 'RU', 'RW', 'BL', 'SH', 'KN', 'LC', 'MF', 'PM', 'VC', 'WS', 'SM', 'ST', 'SA',
            'SN', 'RS', 'SC', 'SL', 'SG', 'SX', 'SK', 'SI', 'SB', 'SO', 'ZA', 'GS', 'SS', 'ES', 'LK', 'SD', 'SR', 'SJ',
            'SE', 'CH', 'SY', 'TW', 'TJ', 'TZ', 'TH', 'TL', 'TG', 'TK', 'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV', 'UG',
            'UA', 'AE', 'GB', 'US', 'UM', 'UY', 'UZ', 'VU', 'VE', 'VN', 'VG', 'VI', 'WF', 'EH', 'YE', 'ZM', 'ZW'];
        // List of country telephone codes
        $phoneCountryCodes = [1, 7, 20, 27, 30, 31, 32, 33, 34, 36, 39, 40, 41, 43, 44, 45, 46, 47, 48, 49, 51, 52, 53,
            54, 55, 56, 57, 58, 60, 61, 62, 63, 64, 65, 66, 81, 82, 84, 86, 90, 91, 92, 93, 94, 95, 98, 211, 212, 213,
            216, 218, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239,
            240, 241, 242, 243, 244, 245, 246, 248, 249, 250, 251, 252, 253, 254, 255, 256, 257, 258, 260, 261, 262, 263,
            264, 265, 266, 267, 268, 269, 290, 291, 297, 298, 299, 350, 351, 352, 353, 354, 355, 356, 357, 358, 359, 370,
            371, 372, 373, 374, 375, 376, 377, 378, 379, 380, 381, 382, 383, 385, 386, 387, 389, 420, 421, 423, 500, 501,
            502, 503, 504, 505, 506, 507, 508, 509, 590, 591, 592, 593, 594, 595, 596, 597, 598, 599, 670, 672, 673, 674,
            675, 676, 677, 678, 679, 680, 681, 682, 683, 685, 686, 687, 688, 689, 690, 691, 692, 800, 808, 850, 852, 853,
            855, 856, 870, 878, 880, 881, 882, 883, 886, 888, 960, 961, 962, 963, 964, 965, 966, 967, 968, 970, 971, 972,
            973, 974, 975, 976, 977, 979, 992, 993, 994, 995, 996, 998];

        $rules = [
            'booking_id' => 'required|size:36',
            'amount_pay' => 'required|string|in:Deposit,Full Payment',
            'booking_contact.first_name' => 'required|string',
            'booking_contact.last_name' => 'required|string',
            'booking_contact.email' => 'required|email:rfc,dns',
            'booking_contact.phone.country_code' => 'required|int|in:' . implode(',', $phoneCountryCodes),
            'booking_contact.phone.area_code' => 'required|int|digits:3',
            'booking_contact.phone.number' => 'required|numeric|digits_between:4,8',
            'booking_contact.address.line_1' => 'required|string|min:1|max:255',
            'booking_contact.address.city' => 'required|string|min:1|max:100',
            'booking_contact.address.state_province_code' => ['required', 'regex:/^([A-Z]{2}|[A-Z]{2}-[A-Z]{2})$/'],
            'booking_contact.address.postal_code' => 'required|string|between:1,7',
            'booking_contact.address.country_code' => 'required|string|in:' . implode(',', $countryCodes),
        ];

        if (request()->has('credit_card')) {
            $rules['credit_card.name_card'] = 'required|string|between:2,255';
            $rules['credit_card.number'] = 'required|int|digits_between:13,19';
            $rules['credit_card.card_type'] = 'required|string|in:MSC,VISA,AMEX,DIS';
            $rules['credit_card.expiry_date'] = 'required|date_format:m/Y|after_or_equal:today';
            $rules['credit_card.cvv'] = 'required|numeric|digits:3';
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
