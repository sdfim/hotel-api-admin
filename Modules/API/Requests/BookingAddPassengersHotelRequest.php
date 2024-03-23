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
    /**
     * @OA\Post(
     *   tags={"Booking API | Cart Endpoints"},
     *   path="/api/booking/add-passengers",
     *   summary="Add passengers to a booking.",
     *   description="Add passengers to a booking. This endpoint is used to add passenger information to a booking.",
     *     @OA\Parameter(
     *       name="booking_id",
     *       in="query",
     *       required=true,
     *       description="To retrieve the **booking_id**, you need to execute a **'/api/booking/add-item'** request. <br>
     *       In the response object for each rate is a **booking_id** property.",
     *     ),
     *     @OA\RequestBody(
     *     description="JSON object containing the details of the reservation. If you don't pass booking_item(s), these passengers will be added to all booking_items that are in the cart (booking_id)",
     *     required=true,
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingAddPassengersRequest",
     *       examples={
     *           "example1": @OA\Schema(ref="#/components/examples/BookingAddPassengersRequest", example="BookingAddPassengersRequest"),
     *       },
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingAddPassengersResponse",
     *       examples={
     *           "Add": @OA\Schema(ref="#/components/examples/BookingAddPassengersResponseAdd", example="BookingAddPassengersResponseAdd"),
     *           "Update": @OA\Schema(ref="#/components/examples/BookingAddPassengersResponseUpdate", example="BookingAddPassengersResponseUpdate"),
     *       },
     *     ),
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingAddPassengersResponse",
     *       examples={
     *       "Error": @OA\Schema(ref="#/components/examples/BookingAddPassengersResponseError", example="BookingAddPassengersResponseError"),
     *       },
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
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

            'passengers' => 'required|array',
            'passengers.*.title' => 'required|in:mr,Mr,MR,ms,Ms,MS,Mrs,MRS,mrs,Miss,MISS,miss,Dr,dr,DR,Prof,prof,PROF',
            'passengers.*.given_name' => 'required|string',
            'passengers.*.family_name' => 'required|string',
            'passengers.*.date_of_birth' => 'required|date_format:Y-m-d',
            'passengers.*.booking_items' => 'required|array',
            'passengers.*.booking_items.*.booking_item' => 'required|uuid',
            'passengers.*.booking_items.*.room' => 'numeric',
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
