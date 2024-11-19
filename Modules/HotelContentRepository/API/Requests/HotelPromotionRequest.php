<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelPromotionRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Promotions"},
     *   path="/api/repo/hotel-promotions",
     *   summary="Get all hotel promotions",
     *   description="Retrieve all hotel promotion records with optional filters.",
     *   @OA\Parameter(
     *     name="hotel_id",
     *     in="query",
     *     required=false,
     *     description="Filter by hotel ID",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="promotion_name",
     *     in="query",
     *     required=false,
     *     description="Filter by promotion name",
     *     @OA\Schema(
     *       type="string",
     *       example="Summer Sale"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="description",
     *     in="query",
     *     required=false,
     *     description="Filter by description",
     *     @OA\Schema(
     *       type="string",
     *       example="Discount on summer bookings"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="validity_start",
     *     in="query",
     *     required=false,
     *     description="Filter by validity start date",
     *     @OA\Schema(
     *       type="string",
     *       format="date",
     *       example="2024-06-01"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="validity_end",
     *     in="query",
     *     required=false,
     *     description="Filter by validity end date",
     *     @OA\Schema(
     *       type="string",
     *       format="date",
     *       example="2024-08-31"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="booking_start",
     *     in="query",
     *     required=false,
     *     description="Filter by booking start date",
     *     @OA\Schema(
     *       type="string",
     *       format="date",
     *       example="2024-05-01"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="booking_end",
     *     in="query",
     *     required=false,
     *     description="Filter by booking end date",
     *     @OA\Schema(
     *       type="string",
     *       format="date",
     *       example="2024-07-31"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="terms_conditions",
     *     in="query",
     *     required=false,
     *     description="Filter by terms and conditions",
     *     @OA\Schema(
     *       type="string",
     *       example="Terms and conditions apply"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="exclusions",
     *     in="query",
     *     required=false,
     *     description="Filter by exclusions",
     *     @OA\Schema(
     *       type="string",
     *       example="Excludes public holidays"
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )

     * @OA\Post(
     *   tags={"Promotions"},
     *   path="/api/repo/hotel-promotions",
     *   summary="Create a new hotel promotion",
     *   description="Create a new hotel promotion entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "promotion_name", "description", "validity_start", "validity_end", "booking_start", "booking_end", "terms_conditions", "exclusions"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="promotion_name", type="string", example="Summer Sale"),
     *       @OA\Property(property="description", type="string", example="Discount on summer bookings"),
     *       @OA\Property(property="validity_start", type="string", format="date", example="2024-06-01"),
     *       @OA\Property(property="validity_end", type="string", format="date", example="2024-08-31"),
     *       @OA\Property(property="booking_start", type="string", format="date", example="2024-05-01"),
     *       @OA\Property(property="booking_end", type="string", format="date", example="2024-07-31"),
     *       @OA\Property(property="terms_conditions", type="string", example="Terms and conditions apply"),
     *       @OA\Property(property="exclusions", type="string", example="Excludes public holidays")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )

     * @OA\Get(
     *   tags={"Promotions"},
     *   path="/api/repo/hotel-promotions/{id}",
     *   summary="Get hotel promotion details",
     *   description="Retrieve details of a specific hotel promotion.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel promotion",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )

     * @OA\Put(
     *   tags={"Promotions"},
     *   path="/api/repo/hotel-promotions/{id}",
     *   summary="Update hotel promotion details",
     *   description="Update details of a specific hotel promotion.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel promotion",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "promotion_name", "description", "validity_start", "validity_end", "booking_start", "booking_end", "terms_conditions", "exclusions"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="promotion_name", type="string", example="Summer Sale"),
     *       @OA\Property(property="description", type="string", example="Discount on summer bookings"),
     *       @OA\Property(property="validity_start", type="string", format="date", example="2024-06-01"),
     *       @OA\Property(property="validity_end", type="string", format="date", example="2024-08-31"),
     *       @OA\Property(property="booking_start", type="string", format="date", example="2024-05-01"),
     *       @OA\Property(property="booking_end", type="string", format="date", example="2024-07-31"),
     *       @OA\Property(property="terms_conditions", type="string", example="Terms and conditions apply"),
     *       @OA\Property(property="exclusions", type="string", example="Excludes public holidays")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )

     * @OA\Delete(
     *   tags={"Promotions"},
     *   path="/api/repo/hotel-promotions/{id}",
     *   summary="Delete a hotel promotion",
     *   description="Delete a specific hotel promotion.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel promotion",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\Response(
     *     response=204,
     *     description="No Content"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
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
            'hotel_id' => 'required|integer',
            'promotion_name' => 'required|string|max:255',
            'description' => 'required|string',
            'validity_start' => 'required|date',
            'validity_end' => 'required|date',
            'booking_start' => 'required|date',
            'booking_end' => 'required|date',
            'terms_conditions' => 'required|string',
            'exclusions' => 'required|string',
        ];
    }
}
