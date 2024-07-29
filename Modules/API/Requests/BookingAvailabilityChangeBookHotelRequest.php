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
     * @OA\Get(
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
     *       oneOf={
     *
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestPlace"),
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestDestination"),
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestCoordinates"),
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestSupplierHotelName"),
     *         },
     *       examples={
     *           "searchByPlace": @OA\Schema(ref="#/components/examples/ContentSearchRequestPlace", example="ContentSearchRequestPlace"),
     *           "searchByDestination": @OA\Schema(ref="#/components/examples/ContentSearchRequestDestination", example="ContentSearchRequestDestination"),
     *           "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchRequestCoordinates", example="ContentSearchRequestCoordinates"),
     *           "searchBySupplierHotelName": @OA\Schema(ref="#/components/examples/ContentSearchRequestSupplierHotelName", example="ContentSearchRequestSupplierHotelName"),
     *       },
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/ContentSearchResponse",
     *       examples={
     *       "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchResponse", example="ContentSearchResponse"),
     *       }
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
        return [
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
