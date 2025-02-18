<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Validation\Rule;
use Modules\API\Validate\ApiRequest;
use Modules\Enums\ContentSourceEnum;

class ProductRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Product | Products"},
     *   path="/api/repo/products",
     *   summary="Get all products",
     *   description="Retrieve all product records with optional filters.",
     *
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=false,
     *     description="Filter by product name",
     *
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="product_type",
     *     in="query",
     *     required=false,
     *     description="Filter by product type",
     *
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="verified",
     *     in="query",
     *     required=false,
     *     description="Filter by verification status",
     *
     *     @OA\Schema(
     *       type="boolean"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="default_currency",
     *     in="query",
     *     required=false,
     *     description="Filter by default currency",
     *
     *     @OA\Schema(
     *       type="string"
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
     *   tags={"Product | Products"},
     *   path="/api/repo/products",
     *   summary="Create a new product",
     *   description="Create a new product entry.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"vendor_id", "product_type", "name", "verified", "content_source_id", "property_images_source_id", "default_currency", "related_id", "related_type"},
     *
     *       @OA\Property(property="vendor_id", type="integer", example=1),
     *       @OA\Property(property="product_type", type="string", example="hotel"),
     *       @OA\Property(property="name", type="string", example="Example Product"),
     *       @OA\Property(property="verified", type="boolean", example=true),
     *       @OA\Property(property="content_source_id", type="integer", example=1),
     *       @OA\Property(property="property_images_source_id", type="integer", example=1),
     *       @OA\Property(property="default_currency", type="string", example="USD"),
     *       @OA\Property(property="website", type="string", example="https://exampleproduct.com"),
     *       @OA\Property(property="lat", type="number", format="float", example=12.345678),
     *       @OA\Property(property="lng", type="number", format="float", example=98.765432),
     *       @OA\Property(property="related_id", type="integer", example=1),
     *       @OA\Property(property="related_type", type="string", example="Modules\\HotelContentRepository\\Models\\Hotel")
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
     *   tags={"Product | Products"},
     *   path="/api/repo/products/{id}",
     *   summary="Get product details",
     *   description="Retrieve details of a specific product.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product",
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
     *   tags={"Product | Products"},
     *   path="/api/repo/products/{id}",
     *   summary="Update product details",
     *   description="Update details of a specific product.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product",
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
     *       required={"vendor_id", "product_type", "name", "verified", "content_source_id", "property_images_source_id", "default_currency", "related_id", "related_type"},
     *
     *       @OA\Property(property="vendor_id", type="integer", example=1),
     *       @OA\Property(property="product_type", type="string", example="hotel"),
     *       @OA\Property(property="name", type="string", example="Example Product"),
     *       @OA\Property(property="verified", type="boolean", example=true),
     *       @OA\Property(property="content_source_id", type="integer", example=1),
     *       @OA\Property(property="property_images_source_id", type="integer", example=1),
     *       @OA\Property(property="default_currency", type="string", example="USD"),
     *       @OA\Property(property="website", type="string", example="https://exampleproduct.com"),
     *       @OA\Property(property="lat", type="number", format="float", example=12.345678),
     *       @OA\Property(property="lng", type="number", format="float", example=98.765432),
     *       @OA\Property(property="related_id", type="integer", example=1),
     *       @OA\Property(property="related_type", type="string", example="Modules\\HotelContentRepository\\Models\\Hotel")
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
     *   tags={"Product | Products"},
     *   path="/api/repo/products/{id}",
     *   summary="Delete a product",
     *   description="Delete a specific product.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product",
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
     * @OA\Post(
     *   tags={"Product | Products"},
     *   path="/api/repo/products/{id}/attach-gallery",
     *   summary="Attach a gallery to a product",
     *   description="Attach a gallery to a specific product.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product",
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
     *   tags={"Product | Products"},
     *   path="/api/repo/products/{id}/detach-gallery",
     *   summary="Detach a gallery from a product",
     *   description="Detach a gallery from a specific product.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product",
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
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function rules(): array
    {
        return [
            'vendor_id' => 'required|exists:pd_vendors,id',
            'product_type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'verified' => 'required|boolean',
            'content_source_id' => 'required|integer',
            'property_images_source_id' => 'required|integer',
            'default_currency' => 'required|string|max:3',
            'website' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'related_id' => 'required|integer',
            'related_type' => 'required|string|max:255',
            'off_sale_by_sources' => [
                'nullable',
                'array',
                Rule::in(ContentSourceEnum::options()),
            ],
        ];
    }
}
