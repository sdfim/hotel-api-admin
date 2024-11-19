<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class TravelAgencyCommissionRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Travel Agency Commissions"},
     *   path="/api/repo/travel-agency-commissions",
     *   summary="Get all travel agency commissions",
     *   description="Retrieve all travel agency commission records with optional filters.",
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=false,
     *     description="Filter by name",
     *     @OA\Schema(
     *       type="string",
     *       example="Summer Promotion"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="commission_value",
     *     in="query",
     *     required=false,
     *     description="Filter by commission value",
     *     @OA\Schema(
     *       type="number",
     *       format="float",
     *       example=10.5
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="date_range_start",
     *     in="query",
     *     required=false,
     *     description="Filter by start date",
     *     @OA\Schema(
     *       type="string",
     *       format="date",
     *       example="2023-01-01"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="date_range_end",
     *     in="query",
     *     required=false,
     *     description="Filter by end date",
     *     @OA\Schema(
     *       type="string",
     *       format="date",
     *       example="2023-12-31"
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
     *   tags={"Travel Agency Commissions"},
     *   path="/api/repo/travel-agency-commissions",
     *   summary="Create a new travel agency commission",
     *   description="Create a new travel agency commission entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"name", "commission_value", "date_range_start", "date_range_end"},
     *       @OA\Property(property="name", type="string", example="Summer Promotion"),
     *       @OA\Property(property="commission_value", type="number", format="float", example=10.5),
     *       @OA\Property(property="date_range_start", type="string", format="date", example="2023-01-01"),
     *       @OA\Property(property="date_range_end", type="string", format="date", example="2023-12-31")
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
     *   tags={"Travel Agency Commissions"},
     *   path="/api/repo/travel-agency-commissions/{id}",
     *   summary="Get travel agency commission details",
     *   description="Retrieve details of a specific travel agency commission.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the travel agency commission",
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
     *   tags={"Travel Agency Commissions"},
     *   path="/api/repo/travel-agency-commissions/{id}",
     *   summary="Update travel agency commission details",
     *   description="Update details of a specific travel agency commission.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the travel agency commission",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"name", "commission_value", "date_range_start", "date_range_end"},
     *       @OA\Property(property="name", type="string", example="Summer Promotion"),
     *       @OA\Property(property="commission_value", type="number", format="float", example=10.5),
     *       @OA\Property(property="date_range_start", type="string", format="date", example="2023-01-01"),
     *       @OA\Property(property="date_range_end", type="string", format="date", example="2023-12-31")
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
     *   tags={"Travel Agency Commissions"},
     *   path="/api/repo/travel-agency-commissions/{id}",
     *   summary="Delete a travel agency commission",
     *   description="Delete a specific travel agency commission.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the travel agency commission",
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
            'name' => 'required|string',
            'commission_value' => 'required|numeric',
            'date_range_start' => 'required|date',
            'date_range_end' => 'required|date',
        ];
    }
}
