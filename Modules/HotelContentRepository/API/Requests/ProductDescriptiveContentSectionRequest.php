<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;

class ProductDescriptiveContentSectionRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Product | Descriptive Content"},
     *   path="/api/repo/product-descriptive-content-sections",
     *   summary="Get all product descriptive content sections",
     *   description="Retrieve all product descriptive content section records with optional filters.",
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
     *     name="section_name",
     *     in="query",
     *     required=false,
     *     description="Filter by section name",
     *
     *     @OA\Schema(
     *       type="string",
     *       example="General Information"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="start_date",
     *     in="query",
     *     required=false,
     *     description="Filter by start date",
     *
     *     @OA\Schema(
     *       type="string",
     *       format="date",
     *       example="2023-01-01"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="end_date",
     *     in="query",
     *     required=false,
     *     description="Filter by end date",
     *
     *     @OA\Schema(
     *       type="string",
     *       format="date",
     *       example="2023-12-31"
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
     *   tags={"Product | Descriptive Content"},
     *   path="/api/repo/product-descriptive-content-sections",
     *   summary="Create a new product descriptive content section",
     *   description="Create a new product descriptive content section entry.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "section_name", "start_date", "descriptive_type_id", "value"},
     *
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="section_name", type="string", example="General Information"),
     *       @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *       @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *       @OA\Property(property="descriptive_type_id", type="integer", example=1),
     *       @OA\Property(property="value", type="string", example="Some descriptive content")
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
     *   tags={"Product | Descriptive Content"},
     *   path="/api/repo/product-descriptive-content-sections/{id}",
     *   summary="Get product descriptive content section details",
     *   description="Retrieve details of a specific product descriptive content section.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product descriptive content section",
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
     *   tags={"Product | Descriptive Content"},
     *   path="/api/repo/product-descriptive-content-sections/{id}",
     *   summary="Update product descriptive content section details",
     *   description="Update details of a specific product descriptive content section.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product descriptive content section",
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
     *       required={"product_id", "section_name", "start_date", "descriptive_type_id", "value"},
     *
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="section_name", type="string", example="General Information"),
     *       @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *       @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *       @OA\Property(property="descriptive_type_id", type="integer", example=1),
     *       @OA\Property(property="value", type="string", example="Some descriptive content")
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
     *   tags={"Product | Descriptive Content"},
     *   path="/api/repo/product-descriptive-content-sections/{id}",
     *   summary="Delete a product descriptive content section",
     *   description="Delete a specific product descriptive content section.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product descriptive content section",
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
            'product_id' => 'required|exists:pd_products,id',
            'section_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'descriptive_type_id' => 'required|exists:config_descriptive_types,id',
            'value' => 'required|string',
        ];
    }
}
