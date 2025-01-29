<?php

namespace Modules\Insurance\API\Requests;

use Modules\API\Validate\ApiRequest;

class InsuranceAddRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Booking API | Insurance and Informational Services"},
     *   path="/api/insurance/add",
     *   summary="Add a new insurance plan",
     *   description="Create a new insurance plan for a booking item.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"vendor"},
     *
     *       @OA\Property(property="booking_id", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
     *       @OA\Property(property="booking_item", type="string", example="123e4567-e89b-12d3-a456-426614174001"),
     *       @OA\Property(property="vendor", type="string", example="TripMate"),
     *
     *       @OA\Examples(
     *          example="example1",
     *          summary="Example with booking_id",
     *          value={
     *              "booking_id": "123e4567-e89b-12d3-a456-426614174000",
     *              "vendor": "TripMate"
     *          }
     *       ),
     *       @OA\Examples(
     *          example="example2",
     *          summary="Example with booking_item",
     *          value={
     *              "booking_item": "123e4567-e89b-12d3-a456-426614174001",
     *              "vendor": "TripMate"
     *          }
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Created"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     *
     * @OA\Delete(
     *   tags={"Booking API | Insurance and Informational Services"},
     *   path="/api/insurance/delete",
     *   summary="Delete an insurance plan",
     *   description="Delete an existing insurance plan for a booking item.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"vendor"},
     *
     *       @OA\Property(property="booking_id", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
     *       @OA\Property(property="booking_item", type="string", example="123e4567-e89b-12d3-a456-426614174001"),
     *       @OA\Property(property="vendor", type="string", example="TripMate"),
     *
     *       @OA\Examples(
     *          example="example1",
     *          summary="Example with booking_id",
     *          value={
     *              "booking_id": "123e4567-e89b-12d3-a456-426614174000",
     *              "vendor": "TripMate"
     *          }
     *       ),
     *       @OA\Examples(
     *          example="example2",
     *          summary="Example with booking_item",
     *          value={
     *              "booking_item": "123e4567-e89b-12d3-a456-426614174001",
     *              "vendor": "TripMate"
     *          }
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=204,
     *     description="No Content"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     *
     * @OA\Get(
     *   tags={"Booking API | Insurance and Informational Services"},
     *   path="/api/insurance/retrieve",
     *   summary="Retrieve insurance plans",
     *   description="Retrieve insurance plans for a booking item or booking ID.",
     *
     *   @OA\Parameter(
     *     name="booking_id",
     *     in="query",
     *     required=false,
     *     description="Booking ID",
     *
     *     @OA\Schema(
     *       type="string",
     *       example="123e4567-e89b-12d3-a456-426614174000"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="booking_item",
     *     in="query",
     *     required=false,
     *     description="Booking Item",
     *
     *     @OA\Schema(
     *       type="string",
     *       example="123e4567-e89b-12d3-a456-426614174001"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */

    public function rules(): array
    {
        return [
            'booking_id' => 'required_without:booking_item|size:36|exists:api_booking_inspector,booking_id',
            'booking_item' => 'required_without:booking_id|size:36|exists:api_booking_items,booking_item',
            'vendor' => 'required|exists:pd_vendors,name',
        ];
    }
}
