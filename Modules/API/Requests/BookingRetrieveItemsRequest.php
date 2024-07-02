<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingRetrieveItemsRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Booking API | Booking Endpoints"},
     *   path="/api/booking/retrieve-booking",
     *   summary="Retrieve detailed information about a specific booking reservation. ",
     *   description="Retrieve detailed information about a specific booking reservation. This endpoint allows you to access all the information related to a particular reservation.",
     *
     *    @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="Booking ID",
     *
     *      @OA\Schema(
     *        type="string",
     *        example="5a67bbbc-0c30-47d9-8b01-ef70c2da196f"
     *      )
     *    ),
     *
     *    @OA\Response(
     *      response=200,
     *      description="OK",
     *
     *     @OA\JsonContent(
     *     ref="#/components/schemas/BookingRetrieveBookingResponse",
     *     examples={
     *     "example1": @OA\Schema(ref="#/components/examples/BookingRetrieveBookingResponse", example="BookingRetrieveBookingResponse"),
     *     }
     *     )
     *    ),
     *
     *    @OA\Response(
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
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
