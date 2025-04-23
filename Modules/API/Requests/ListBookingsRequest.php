<?php

namespace Modules\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class ListBookingsRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Booking API | Booking"},
     *   path="/api/booking/list-bookings",
     *   summary="Retrieve a list of all your booking reservations. ",
     *   description="Retrieve a list of all your booking reservations. This endpoint provides an overview of your booking history and their current statuses.",
     *
     *    @OA\Parameter(
     *      name="type",
     *      in="query",
     *      required=true,
     *      description="Type",
     *
     *      @OA\Schema(
     *        type="string",
     *        example="hotel"
     *      )
     *    ),
     *
     *    @OA\Parameter(
     *      name="supplier",
     *      in="query",
     *      required=true,
     *      description="Supplier",
     *
     *      @OA\Schema(
     *        type="string",
     *        example="Expedia"
     *      )
     *    ),
     *
     *    @OA\Response(
     *      response=200,
     *      description="OK",
     *    ),
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

    public function rules(): array
    {
        return [
            'supplier' => 'required|string',
            'type' => 'required|string|in:hotel,flight,combo',
        ];
    }
}
