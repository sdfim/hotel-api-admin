<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;
use Modules\Enums\AgeRestrictionTypeEnum;

class ProductAgeRestrictionRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Age Restrictions"},
     *   path="/api/repo/age-restrictions",
     *   summary="Get all product age restrictions",
     *   description="Retrieve all product age restriction records with optional filters.",
     *   @OA\Parameter(
     *     name="product_id",
     *     in="query",
     *     required=false,
     *     description="Filter by product ID",
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
     *   summary="Create a new product age restriction",
     *   description="Create a new product age restriction entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "restriction_type_id", "value", "active"},
     *       @OA\Property(property="product_id", type="integer", example=1),
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
     *   summary="Get product age restriction details",
     *   description="Retrieve details of a specific product age restriction.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product age restriction",
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
     *   summary="Update product age restriction details",
     *   description="Update details of a specific product age restriction.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product age restriction",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "restriction_type_id", "value", "active"},
     *       @OA\Property(property="product_id", type="integer", example=1),
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
     *   summary="Delete a product age restriction",
     *   description="Delete a specific product age restriction.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product age restriction",
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
            'product_id' => 'required|exists:pd_products,id',
            'restriction_type' => 'required|in:' . implode(',', array_column(AgeRestrictionTypeEnum::cases(), 'value')),
            'value' => 'required|integer',
            'active' => 'required|boolean',
        ];
    }
}
