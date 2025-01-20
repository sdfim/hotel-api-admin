<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class ProductAffiliationRequest extends ApiRequest
{
    /**
     * @OA\Parameter(
     *   name="id",
     *   in="path",
     *   required=true,
     *   description="ID of the hotel affiliation",
     *   @OA\Schema(
     *     type="integer",
     *     example=1
     *   )
     * ),
     * @OA\RequestBody(
     *   request="ProductAffiliationRequest",
     *   required=true,
     *   @OA\JsonContent(
     *     type="object",
     *     required={"product_id", "consortia_id", "description", "start_date", "end_date"},
     *     @OA\Property(property="product_id", type="integer", example=1),
     *     @OA\Property(property="consortia_id", type="integer", example=1),
     *     @OA\Property(property="description", type="string", example="Description of the affiliation"),
     *     @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *     @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *     @OA\Property(property="amenities", type="array", @OA\Items(type="string"), example={"WiFi", "Pool"})
     *   )
     * ),
     * @OA\Response(
     *   response=200,
     *   description="OK"
     * ),
     * @OA\Response(
     *   response=401,
     *   description="Unauthenticated",
     *   @OA\JsonContent(
     *     ref="#/components/schemas/UnAuthenticatedResponse",
     *     examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *     }
     *   )
     * ),
     * @OA\Response(
     *   response=400,
     *   description="Bad Request",
     *   @OA\JsonContent(
     *     ref="#/components/schemas/BadRequestResponse",
     *     examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse")
     *     }
     *   )
     * ),
     * @OA\Response(
     *   response=404,
     *   description="Not Found",
     *   @OA\JsonContent(
     *     ref="#/components/schemas/NotFoundResponse",
     *     examples={
     *       "example1": @OA\Schema(ref="#/components/examples/NotFoundResponse", example="NotFoundResponse")
     *     }
     *   )
     * ),
     * security={{ "apiAuth": {} }}
     * )
     * @OA\Post(
     *   tags={"Product | Affiliations"},
     *   path="/api/repo/product-affiliations",
     *   summary="Create a new hotel affiliation",
     *   description="Create a new hotel affiliation.",
     *   @OA\RequestBody(
     *     request="ProductAffiliationRequest",
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "consortia_id", "description", "start_date", "end_date"},
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="consortia_id", type="integer", example=1),
     *       @OA\Property(property="description", type="string", example="Description of the affiliation"),
     *       @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *       @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *       @OA\Property(property="amenities", type="array", @OA\Items(type="string"), example={"WiFi", "Pool"})
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
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse")
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * ),
     * @OA\Put(
     *   tags={"Product | Affiliations"},
     *   path="/api/repo/product-affiliations/{id}",
     *   summary="Update a hotel affiliation",
     *   description="Update a specific hotel affiliation.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel affiliation",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     request="ProductAffiliationRequest",
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "consortia_id", "description", "start_date", "end_date"},
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="consortia_id", type="integer", example=1),
     *       @OA\Property(property="description", type="string", example="Description of the affiliation"),
     *       @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *       @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *       @OA\Property(property="amenities", type="array", @OA\Items(type="string"), example={"WiFi", "Pool"})
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
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse")
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/NotFoundResponse", example="NotFoundResponse")
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * ),
     * @OA\Delete(
     *   tags={"Product | Affiliations"},
     *   path="/api/repo/product-affiliations/{id}",
     *   summary="Delete a hotel affiliation",
     *   description="Delete a specific hotel affiliation.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel affiliation",
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
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/NotFoundResponse", example="NotFoundResponse")
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * ),
     * @OA\Get(
     *   tags={"Product | Affiliations"},
     *   path="/api/repo/product-affiliations",
     *   summary="List all hotel affiliations",
     *   description="Get a list of all hotel affiliations.",
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/NotFoundResponse", example="NotFoundResponse")
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * ),
     * @OA\Get(
     *   tags={"Product | Affiliations"},
     *   path="/api/repo/product-affiliations/{id}",
     *   summary="Get a specific hotel affiliation",
     *   description="Get details of a specific hotel affiliation.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel affiliation",
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
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/NotFoundResponse", example="NotFoundResponse")
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:pd_products,id',
            'consortia_id' => 'required|integer|exists:config_consortia,id',
            'description' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'amenities' => 'nullable|array',
        ];
    }
}
