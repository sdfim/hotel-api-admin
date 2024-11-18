<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelAgeRestrictionRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Age Restrictions"},
     *   path="/api/repo/age-restrictions",
     *   summary="Get all hotel age restrictions",
     *   description="Retrieve all hotel age restriction records with optional filters.",
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
     *     name="restriction_type_id",
     *     in="query",
     *     required=false,
     *     description="Filter by restriction type ID",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="value",
     *     in="query",
     *     required=false,
     *     description="Filter by value",
     *     @OA\Schema(
     *       type="integer",
     *       example=18
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="active",
     *     in="query",
     *     required=false,
     *     description="Filter by active status",
     *     @OA\Schema(
     *       type="boolean",
     *       example=true
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
     *   tags={"Age Restrictions"},
     *   path="/api/repo/age-restrictions",
     *   summary="Create a new hotel age restriction",
     *   description="Create a new hotel age restriction entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "restriction_type_id", "value", "active"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="restriction_type_id", type="integer", example=1),
     *       @OA\Property(property="value", type="integer", example=18),
     *       @OA\Property(property="active", type="boolean", example=true)
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
     *   tags={"Age Restrictions"},
     *   path="/api/repo/age-restrictions/{id}",
     *   summary="Get hotel age restriction details",
     *   description="Retrieve details of a specific hotel age restriction.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel age restriction",
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
     *   tags={"Age Restrictions"},
     *   path="/api/repo/age-restrictions/{id}",
     *   summary="Update hotel age restriction details",
     *   description="Update details of a specific hotel age restriction.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel age restriction",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "restriction_type_id", "value", "active"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="restriction_type_id", type="integer", example=1),
     *       @OA\Property(property="value", type="integer", example=18),
     *       @OA\Property(property="active", type="boolean", example=true)
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
     *   tags={"Age Restrictions"},
     *   path="/api/repo/age-restrictions/{id}",
     *   summary="Delete a hotel age restriction",
     *   description="Delete a specific hotel age restriction.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel age restriction",
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

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|exists:pd_hotels,id',
            'restriction_type_id' => 'required|exists:pd_hotel_age_restriction_types,id',
            'value' => 'required|integer',
            'active' => 'required|boolean',
        ];
    }
}
