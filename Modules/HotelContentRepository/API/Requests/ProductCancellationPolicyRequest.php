<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;

class ProductCancellationPolicyRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Product | Cancellation Policy"},
     *   path="/api/repo/product-cancellation-policy",
     *   summary="Get all product cancellation policies",
     *   description="Retrieve all product cancellation policy records with optional filters.",
     *
     *   @OA\Parameter(
     *     name="product_id",
     *     in="query",
     *     required=false,
     *     description="Filter by product ID",
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
     * @OA\Post(
     *   tags={"Product | Cancellation Policy"},
     *   path="/api/repo/product-cancellation-policy",
     *   summary="Create a new product cancellation policy",
     *   description="Create a new product cancellation policy entry.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "name", "start_date", "expiration_date", "manipulable_price_type", "price_value", "price_value_type", "price_value_target"},
     *
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Sample Name"),
     *       @OA\Property(property="start_date", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
     *       @OA\Property(property="expiration_date", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
     *       @OA\Property(property="manipulable_price_type", type="string", enum={"total_price", "net_price"}, example="total_price"),
     *       @OA\Property(property="price_value", type="number", format="float", example=100.00),
     *       @OA\Property(property="price_value_type", type="string", enum={"fixed_value", "percentage"}, example="fixed_value"),
     *       @OA\Property(property="price_value_target", type="string", enum={"per_person", "per_room", "per_night", "not_applicable"}, example="per_person")
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
     *   tags={"Product | Cancellation Policy"},
     *   path="/api/repo/product-cancellation-policy/{id}",
     *   summary="Get product cancellation policy details",
     *   description="Retrieve details of a specific product cancellation policy.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product cancellation policy",
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
     *   tags={"Product | Cancellation Policy"},
     *   path="/api/repo/product-cancellation-policy/{id}",
     *   summary="Update product cancellation policy details",
     *   description="Update details of a specific product cancellation policy.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product cancellation policy",
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
     *       required={"product_id", "name", "start_date", "expiration_date", "manipulable_price_type", "price_value", "price_value_type", "price_value_target"},
     *
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Sample Name"),
     *       @OA\Property(property="start_date", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
     *       @OA\Property(property="expiration_date", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
     *       @OA\Property(property="manipulable_price_type", type="string", enum={"total_price", "net_price"}, example="total_price"),
     *       @OA\Property(property="price_value", type="number", format="float", example=100.00),
     *       @OA\Property(property="price_value_type", type="string", enum={"fixed_value", "percentage"}, example="fixed_value"),
     *       @OA\Property(property="price_value_target", type="string", enum={"per_person", "per_room", "per_night", "not_applicable"}, example="per_person")
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
     *   tags={"Product | Cancellation Policy"},
     *   path="/api/repo/product-cancellation-policy/{id}",
     *   summary="Delete a product cancellation policy",
     *   description="Delete a specific product cancellation policy.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product cancellation policy",
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
     */

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:pd_products,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'expiration_date' => 'required|date|after:start_date',
            'manipulable_price_type' => 'required|string|in:total_price,net_price',
            'price_value' => 'required|numeric',
            'price_value_type' => 'required|string|in:fixed_value,percentage',
            'price_value_target' => 'required|string|in:per_person,per_room,per_night,not_applicable',
        ];
    }
}
