<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;


class BookingAvailabilityChangeBookHotelRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * @OA\Post(
     *   tags={"Booking API | Change Booking"},
     *   path="/api/booking/change/availability",
     *   summary="Retrieve available changes for modifying an existing booking.",
     *   description="This endpoint provides information about available changes for modifying an existing booking.",
     *
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/AvailabilitySearchRequest",
     *       examples={
     *           "GIATA Place Eiffel Tower": @OA\Schema(ref="#/components/examples/AvailabilitySearchRequestPlace", example="AvailabilitySearchRequestPlace"),
     *           "GIATA Place Cancun": @OA\Schema(ref="#/components/examples/AvailabilitySearchRequestPlaceCancun", example="AvailabilitySearchRequestPlaceCancun"),
     *           "NewYork": @OA\Schema(ref="#/components/examples/AvailabilitySearchRequestNewYork", example="AvailabilitySearchRequestNewYork"),
     *           "Cancun": @OA\Schema(ref="#/components/examples/AvailabilitySearchRequestCancun", example="AvailabilitySearchRequestCancun"),
     *           "SupplierCurrency": @OA\Schema(ref="#/components/examples/AvailabilitySearchRequestCurrencySupplier", example="AvailabilitySearchRequestCurrencySupplier"),
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
        $baseSearchRequest = new PriceHotelRequest();
        $baseSearchRules = $baseSearchRequest->rules();

        unset($baseSearchRules['type']);
        unset($baseSearchRules['giata_ids']);
        unset($baseSearchRules['place']);
        unset($baseSearchRules['destination']);
        unset($baseSearchRules['latitude']);
        unset($baseSearchRules['longitude']);
        unset($baseSearchRules['radius']);

        return $baseSearchRules + [
            'booking_id' => 'required|size:36',
            'booking_item' => 'required|size:36',
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
