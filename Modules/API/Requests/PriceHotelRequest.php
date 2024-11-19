<?php

namespace Modules\API\Requests;

use App\Models\Supplier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class PriceHotelRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Pricing API"},
     *   path="/api/pricing/search",
     *   summary="Search Price Hotels",
     *   description="Pricing Search for hotels by places/destination or coordinates.<br> The '<b>place</b>' value should be used from the endpoint api/content/destinations",
     *
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/PricingSearchRequest",
     *       examples={
     *           "GIATA Place Eiffel Tower": @OA\Schema(ref="#/components/examples/PricingSearchRequestPlace", example="PricingSearchRequestPlace"),
     *           "GIATA Place Cancun": @OA\Schema(ref="#/components/examples/PricingSearchRequestPlaceCancun", example="PricingSearchRequestPlaceCancun"),
     *           "NewYork": @OA\Schema(ref="#/components/examples/PricingSearchRequestNewYork", example="PricingSearchRequestNewYork"),
     *           "London": @OA\Schema(ref="#/components/examples/PricingSearchRequestLondon", example="PricingSearchRequestLondon"),
     *           "Cancun": @OA\Schema(ref="#/components/examples/PricingSearchRequestCancun", example="PricingSearchRequestCancun"),
     *           "SupplierCurrency": @OA\Schema(ref="#/components/examples/PricingSearchRequestCurrencySupplier", example="PricingSearchRequestCurrencySupplier"),
     *       },
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/PricingSearchResponse",
     *         examples={
     *           "NewYork": @OA\Schema(ref="#/components/examples/PricingSearchResponseNewYork", example="PricingSearchResponseNewYork"),
     *           "London": @OA\Schema(ref="#/components/examples/PricingSearchResponseLondon", example="PricingSearchResponseLondon"),
     *       },
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */

    public function rules(): array
    {
        $validCurrencies = [
            'AED', 'ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'CNY', 'COP', 'DKK', 'EGP',
            'EUR', 'GBP', 'HKD', 'IDR', 'ILS', 'INR', 'JPY', 'KRW', 'LBP', 'MAD',
            'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'QAR', 'RUB', 'SAR', 'SEK',
            'SGD', 'THB', 'TRY', 'TWD', 'USD', 'VND', 'ZAR', '*',
        ];

        $supplier = Supplier::get()->pluck('name')->map('ucfirst')->toArray();

        $occupancy = request()->occupancy;
        if (is_null($occupancy)) {
            return [
                'occupancy' => ['required', function ($attribute, $value, $fail) {
                    $fail('The occupancy must be an array.');
                }],
            ];
        }
        foreach ($occupancy as $key => $value) {
            if (empty($value['children_ages']) && isset($value['children_ages'])) {
                return [
                    'occupancy.'.$key.'.children_ages' => ['required'],
                ];
            } elseif (isset($value['children']) && ! isset($value['children_ages'])) {
                return [
                    'occupancy.'.$key.'.children_ages' => 'required|array',
                ];
            } elseif (isset($value['children']) && (count($value['children_ages']) !== $value['children'])) {
                return [
                        'occupancy.'.$key.'.children_ages' => ['required',
                            function ($attribute, $value, $fail) {
                                $fail('The number of children must equal the number of records of their age children_ages.');
                            },
                        ],
                    ];
            }
        }

        return [
            'type' => 'required|string',
            'currency' => ['string', 'in:'.implode(',', $validCurrencies)],
            'hotel_name' => 'string',
            'supplier' => 'string',
            'checkin' => 'required|date_format:Y-m-d|after:yesterday',
            'checkout' => 'required|date_format:Y-m-d|after:checkin',

            'giata_ids' => 'required_without_all:latitude,longitude,destination,place|array',
            'giata_ids.*' => 'integer',

            'place' => 'required_without_all:giata_ids,latitude,longitude,destination|nullable|string|max:32',
            'session' => 'string|max:36',

            'destination' => 'required_without_all:giata_ids,latitude,longitude,place|integer|min:1,max:999999',

            'latitude' => 'required_without_all:giata_ids,destination,place,session|nullable|decimal:0,8|min:-90|max:90',
            'longitude' => 'required_without_all:giata_ids,destination,place,session|nullable|decimal:0,8|min:-180|max:180',
            'radius' => 'required_without_all:giata_ids,destination,place|numeric|between:1,1000',

            'query_package' => 'string|in:both,standalone,package',

            'rating' => 'numeric|between:1,5.5',
            'occupancy' => 'required|array',
            'occupancy.*.adults' => 'required|integer|between:1,20',
            'occupancy.*.children' => 'numeric',
            'occupancy.*.children_ages' => 'array',
            'occupancy.*.children_ages.*' => 'integer|between:0,17',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
