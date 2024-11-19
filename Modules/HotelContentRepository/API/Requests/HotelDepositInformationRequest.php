<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelDepositInformationRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Deposit Information"},
     *   path="/api/repo/hotel-deposit-information",
     *   summary="Get all hotel deposit information",
     *   description="Retrieve all hotel deposit information records with optional filters.",
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
     *   tags={"Deposit Information"},
     *   path="/api/repo/hotel-deposit-information",
     *   summary="Create a new hotel deposit information",
     *   description="Create a new hotel deposit information entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "days_departure", "pricing_parameters", "pricing_value"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="days_departure", type="integer", example=10),
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
     *   tags={"Deposit Information"},
     *   path="/api/repo/hotel-deposit-information/{id}",
     *   summary="Get hotel deposit information details",
     *   description="Retrieve details of a specific hotel deposit information.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel deposit information",
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
     *   tags={"Deposit Information"},
     *   path="/api/repo/hotel-deposit-information/{id}",
     *   summary="Update hotel deposit information details",
     *   description="Update details of a specific hotel deposit information.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel deposit information",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "days_departure", "pricing_parameters", "pricing_value"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="days_departure", type="integer", example=10),
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
     *   tags={"Deposit Information"},
     *   path="/api/repo/hotel-deposit-information/{id}",
     *   summary="Delete a hotel deposit information",
     *   description="Delete a specific hotel deposit information.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel deposit information",
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
            'hotel_id' => 'required|exists:pd_hotels,id',
            'days_departure' => 'required|integer',
            'pricing_parameters' => 'required|string|in:per_channel,per_room,per_rate',
            'pricing_value' => 'required|numeric',
        ];
    }
}
