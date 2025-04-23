<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;

class ProductInformativeServiceRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Product | Informational Service"},
     *   path="/api/repo/product-informative-services",
     *   summary="Get all product informative services",
     *   description="Retrieve all product informative service records with optional filters.",
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
     *   @OA\Parameter(
     *     name="service_id",
     *     in="query",
     *     required=false,
     *     description="Filter by service ID",
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
     *   tags={"Product | Informational Service"},
     *   path="/api/repo/product-informative-services",
     *   summary="Create a new product informative service",
     *   description="Create a new product informative service entry.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "service_id"},
     *
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="service_id", type="integer", example=1),
     *       @OA\Property(property="cost", type="number", format="float", example=100.00),
     *       @OA\Property(property="name", type="string", example="Service Name"),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="service_time", type="string", example="08:30 AM"),
     *       @OA\Property(property="show_service_on_pdf", type="boolean", example=true),
     *       @OA\Property(property="show_service_data_on_pdf", type="boolean", example=true),
     *       @OA\Property(property="auto_book", type="boolean", example=false)
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
     *   tags={"Product | Informational Service"},
     *   path="/api/repo/product-informative-services/{id}",
     *   summary="Get product informative service details",
     *   description="Retrieve details of a specific product informative service.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product informative service",
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
     *   tags={"Product | Informational Service"},
     *   path="/api/repo/product-informative-services/{id}",
     *   summary="Update product informative service details",
     *   description="Update details of a specific product informative service.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product informative service",
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
     *       required={"product_id", "service_id"},
     *
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="service_id", type="integer", example=1),
     *       @OA\Property(property="cost", type="number", format="float", example=100.00),
     *       @OA\Property(property="name", type="string", example="Service Name"),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="service_time", type="string", example="08:30 AM"),
     *       @OA\Property(property="show_service_on_pdf", type="boolean", example=true),
     *       @OA\Property(property="show_service_data_on_pdf", type="boolean", example=true),
     *       @OA\Property(property="auto_book", type="boolean", example=false)
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
     *   tags={"Product | Informational Service"},
     *   path="/api/repo/product-informative-services/{id}",
     *   summary="Delete a product informative service",
     *   description="Delete a specific product informative service.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product informative service",
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
            'service_id' => 'required|integer|exists:config_service_types,id',
            'cost' => 'nullable|numeric',
            'name' => 'nullable|string',
            'currency' => 'nullable|string',
            'service_time' => 'nullable|string',
            'show_service_on_pdf' => 'nullable|boolean',
            'show_service_data_on_pdf' => 'nullable|boolean',
            'commissionable' => 'nullable|boolean',
            'auto_book' => 'nullable|boolean',
            'dynamic_columns' => 'nullable|array',
            'dynamic_columns.*.name' => 'required|string',
            'dynamic_columns.*.value' => 'required|string',
        ];
    }
}
