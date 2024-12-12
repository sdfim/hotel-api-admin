<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;
use Modules\Enums\DaysPriorTypeEnum;

class ProductDepositInformationRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Product | Deposit Information"},
     *   path="/api/repo/product-deposit-information",
     *   summary="Get all product deposit information",
     *   description="Retrieve all product deposit information records with optional filters.",
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
     *   tags={"Product | Deposit Information"},
     *   path="/api/repo/product-deposit-information",
     *   summary="Create a new product deposit information",
     *   description="Create a new product deposit information entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "days_prior_type", "days", "pricing_parameters", "pricing_value"},
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="days_prior_type", type="string", enum={"Departure", "Date"}, example="Departure"),
     *       @OA\Property(property="days", type="integer", example=10),
     *       @OA\Property(property="pricing_parameters", type="string", enum={"per_channel", "per_room", "per_rate"}, example="per_channel"),
     *       @OA\Property(property="pricing_value", type="number", format="float", example=100.00)
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
     *   tags={"Product | Deposit Information"},
     *   path="/api/repo/product-deposit-information/{id}",
     *   summary="Get product deposit information details",
     *   description="Retrieve details of a specific product deposit information.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product deposit information",
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
     *   tags={"Product | Deposit Information"},
     *   path="/api/repo/product-deposit-information/{id}",
     *   summary="Update product deposit information details",
     *   description="Update details of a specific product deposit information.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product deposit information",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"product_id", "days_prior_type", "days", "pricing_parameters", "pricing_value"},
     *       @OA\Property(property="product_id", type="integer", example=1),
     *       @OA\Property(property="days_prior_type", type="string", enum={"Departure", "Date"}, example="Departure"),
     *       @OA\Property(property="days", type="integer", example=10),
     *       @OA\Property(property="pricing_parameters", type="string", enum={"per_channel", "per_room", "per_rate"}, example="per_channel"),
     *       @OA\Property(property="pricing_value", type="number", format="float", example=100.00)
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
     *   tags={"Product | Deposit Information"},
     *   path="/api/repo/product-deposit-information/{id}",
     *   summary="Delete a product deposit information",
     *   description="Delete a specific product deposit information.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the product deposit information",
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
            'days_prior_type' => 'required|string|in:' . implode(',', DaysPriorTypeEnum::values()),
            'days' => 'required|integer',
            'pricing_parameters' => 'required|string|in:per_channel,per_room,per_rate',
            'pricing_value' => 'required|numeric',
        ];
    }
}
