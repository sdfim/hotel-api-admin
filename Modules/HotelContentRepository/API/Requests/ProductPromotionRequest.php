<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;
use OpenApi\Annotations as OA;

class ProductPromotionRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Product | Promotions"},
     *   path="/api/repo/product-promotions",
     *   summary="Create a new product promotion",
     *   description="Create a new product promotion entry.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "promotion_name", "rate_code", "description", "validity_start", "validity_end", "booking_start", "booking_end", "terms_conditions", "exclusions", "min_night_stay", "max_night_stay", "not_refundable", "package"},
     *
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="promotion_name", type="string", example="Summer Sale"),
     *       @OA\Property(property="rate_code", type="string", example="SUMMER2024"),
     *       @OA\Property(property="description", type="string", example="Discount on summer bookings"),
     *       @OA\Property(property="validity_start", type="string", format="date", example="2024-06-01"),
     *       @OA\Property(property="validity_end", type="string", format="date", example="2024-08-31"),
     *       @OA\Property(property="booking_start", type="string", format="date", example="2024-05-01"),
     *       @OA\Property(property="booking_end", type="string", format="date", example="2024-07-31"),
     *       @OA\Property(property="terms_conditions", type="string", example="Terms and conditions apply"),
     *       @OA\Property(property="exclusions", type="string", example="Excludes public holidays"),
     *       @OA\Property(property="min_night_stay", type="integer", example=1),
     *       @OA\Property(property="max_night_stay", type="integer", example=10),
     *       @OA\Property(property="not_refundable", type="boolean", example=false),
     *       @OA\Property(property="package", type="boolean", example=false)
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Created"
     *   ),
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
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     *
     * @OA\Get(
     *   tags={"Product | Promotions"},
     *   path="/api/repo/product-promotions",
     *   summary="Get list of product promotions",
     *   description="Retrieve a list of product promotions.",
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     *
     * @OA\Get(
     *   tags={"Product | Promotions"},
     *   path="/api/repo/product-promotions/{id}",
     *   summary="Get product promotion details",
     *   description="Retrieve details of a specific product promotion.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product promotion",
     *
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
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
     * @OA\Put(
     *   tags={"Product | Promotions"},
     *   path="/api/repo/product-promotions/{id}",
     *   summary="Update product promotion details",
     *   description="Update details of a specific product promotion.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product promotion",
     *
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "promotion_name", "rate_code", "description", "validity_start", "validity_end", "booking_start", "booking_end", "terms_conditions", "exclusions", "min_night_stay", "max_night_stay", "not_refundable", "package"},
     *
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="promotion_name", type="string", example="Summer Sale"),
     *       @OA\Property(property="rate_code", type="string", example="SUMMER2024"),
     *       @OA\Property(property="description", type="string", example="Discount on summer bookings"),
     *       @OA\Property(property="validity_start", type="string", format="date", example="2024-06-01"),
     *       @OA\Property(property="validity_end", type="string", format="date", example="2024-08-31"),
     *       @OA\Property(property="booking_start", type="string", format="date", example="2024-05-01"),
     *       @OA\Property(property="booking_end", type="string", format="date", example="2024-07-31"),
     *       @OA\Property(property="terms_conditions", type="string", example="Terms and conditions apply"),
     *       @OA\Property(property="exclusions", type="string", example="Excludes public holidays"),
     *       @OA\Property(property="min_night_stay", type="integer", example=1),
     *       @OA\Property(property="max_night_stay", type="integer", example=10),
     *       @OA\Property(property="not_refundable", type="boolean", example=false),
     *       @OA\Property(property="package", type="boolean", example=false)
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
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
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
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
     *   tags={"Product | Promotions"},
     *   path="/api/repo/product-promotions/{id}",
     *   summary="Delete a product promotion",
     *   description="Delete a specific product promotion.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product promotion",
     *
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=204,
     *     description="No Content"
     *   ),
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
     * @OA\Post(
     *   tags={"Product | Promotions"},
     *   path="/api/repo/product-promotions/{id}/attach-gallery",
     *   summary="Attach a gallery to a product promotion",
     *   description="Attach a gallery to a specific product promotion.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product promotion",
     *
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"gallery_id"},
     *
     *       @OA\Property(property="gallery_id", type="integer", example=1)
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
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
     * @OA\Post(
     *   tags={"Product | Promotions"},
     *   path="/api/repo/product-promotions/{id}/detach-gallery",
     *   summary="Detach a gallery from a product promotion",
     *   description="Detach a gallery from a specific product promotion.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product promotion",
     *
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"gallery_id"},
     *
     *       @OA\Property(property="gallery_id", type="integer", example=1)
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
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
            'product_id' => 'required|integer|exists:pd_products,id',
            'promotion_name' => 'required|string|max:255',
            'rate_code' => 'required|string|max:255',
            'description' => 'required|string',
            'validity_start' => 'required|date',
            'validity_end' => 'required|date',
            'booking_start' => 'required|date',
            'booking_end' => 'required|date',
            'terms_conditions' => 'required|string',
            'exclusions' => 'required|string',
            'min_night_stay' => 'required|integer',
            'max_night_stay' => 'required|integer',
            'not_refundable' => 'boolean',
            'package' => 'boolean',
        ];
    }
}
