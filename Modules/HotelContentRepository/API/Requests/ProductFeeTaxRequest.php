<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\FeeTaxTypeEnum;
use Modules\Enums\FeeTaxValueTypeEnum;

class ProductFeeTaxRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Product | Fee and Tax"},
     *   path="/api/repo/product-fee-taxes",
     *   summary="Get all product fee taxes",
     *   description="Retrieve all product fee tax records with optional filters.",
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
     *     name="name",
     *     in="query",
     *     required=false,
     *     description="Filter by name",
     *     @OA\Schema(
     *       type="string",
     *       example="Service Fee"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="net_value",
     *     in="query",
     *     required=false,
     *     description="Filter by net value",
     *     @OA\Schema(
     *       type="number",
     *       format="float",
     *       example=100.0
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="rack_value",
     *     in="query",
     *     required=false,
     *     description="Filter by rack value",
     *     @OA\Schema(
     *       type="number",
     *       format="float",
     *       example=120.0
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="type",
     *     in="query",
     *     required=false,
     *     description="Filter by type",
     *     @OA\Schema(
     *       type="string",
     *       enum={"Tax", "Fee"},
     *       example="Tax"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="value_type",
     *     in="query",
     *     required=false,
     *     description="Filter by value type",
     *     @OA\Schema(
     *       type="string",
     *       enum={"Percentage", "Amount"},
     *       example="Percentage"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="collected_by",
     *     in="query",
     *     required=false,
     *     description="Filter by collected by",
     *     @OA\Schema(
     *       type="string",
     *       enum={"Direct", "Vendor"},
     *       example="Direct"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="fee_category",
     *     in="query",
     *     required=false,
     *     description="Filter by fee category",
     *     @OA\Schema(
     *       type="string",
     *       example="optional"
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
     *   security={{ "apiAuth": {} }}
     * )

     *
     * @OA\Post(
     *   tags={"Product | Fee and Tax"},
     *   path="/api/repo/product-fee-taxes",
     *   summary="Create a new product fee tax",
     *   description="Create a new product fee tax entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "name", "net_value", "rack_value", "type", "value_type", "collected_by", "commissionable", "fee_category"},
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Service Fee"),
     *       @OA\Property(property="net_value", type="number", format="float", example=100.0),
     *       @OA\Property(property="rack_value", type="number", format="float", example=120.0),
     *       @OA\Property(property="type", type="string", enum={"Tax", "Fee"}, example="Tax"),
     *       @OA\Property(property="value_type", type="string", enum={"Percentage", "Amount"}, example="Percentage"),
     *       @OA\Property(property="collected_by", type="string", enum={"Direct", "Vendor"}, example="Direct"),
     *       @OA\Property(property="commissionable", type="boolean", example=true),
     *       @OA\Property(property="fee_category", type="string", example="optional")
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
     * )
     *
     * @OA\Get(
     *   tags={"Product | Fee and Tax"},
     *   path="/api/repo/product-fee-taxes/{id}",
     *   summary="Get product fee tax details",
     *   description="Retrieve details of a specific product fee tax.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product fee tax",
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
     *
     * @OA\Put(
     *   tags={"Product | Fee and Tax"},
     *   path="/api/repo/product-fee-taxes/{id}",
     *   summary="Update product fee tax details",
     *   description="Update details of a specific product fee tax.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product fee tax",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "name", "net_value", "rack_value", "type", "value_type", "collected_by", "commissionable", "fee_category"},
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Service Fee"),
     *       @OA\Property(property="net_value", type="number", format="float", example=100.0),
     *       @OA\Property(property="rack_value", type="number", format="float", example=120.0),
     *       @OA\Property(property="type", type="string", enum={"Tax", "Fee"}, example="Tax"),
     *       @OA\Property(property="value_type", type="string", enum={"Percentage", "Amount"}, example="Percentage"),
     *       @OA\Property(property="collected_by", type="string", enum={"Direct", "Vendor"}, example="Direct"),
     *       @OA\Property(property="commissionable", type="boolean", example=true),
     *       @OA\Property(property="fee_category", type="string", example="optional")
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
     * )
     *
     * @OA\Delete(
     *   tags={"Product | Fee and Tax"},
     *   path="/api/repo/product-fee-taxes/{id}",
     *   summary="Delete a product fee tax",
     *   description="Delete a specific product fee tax.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product fee tax",
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
     * )
     */

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:pd_products,id',
            'name' => 'required|string|max:255',
            'net_value' => 'required|numeric',
            'rack_value' => 'required|numeric',
            'type' => 'required|in:' . implode(',', array_column(FeeTaxTypeEnum::cases(), 'value')),
            'value_type' => 'required|in:' . implode(',', array_column(FeeTaxValueTypeEnum::cases(), 'value')),
            'collected_by' => 'required|in:' . implode(',', array_column(FeeTaxCollectedByEnum::cases(), 'value')),
            'commissionable' => 'required|boolean',
            'fee_category' => 'required|string|max:255',
        ];
    }
}
