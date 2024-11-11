<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelDescriptiveContentSectionRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Descriptive Content Section"},
     *   path="/api/repo/hotel-descriptive-content-sections",
     *   summary="Get all hotel descriptive content sections",
     *   description="Retrieve all hotel descriptive content section records with optional filters.",
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
     *   @OA\Parameter(
     *     name="section_name",
     *     in="query",
     *     required=false,
     *     description="Filter by section name",
     *     @OA\Schema(
     *       type="string",
     *       example="General Information"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="start_date",
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
     *     name="end_date",
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
     *
     * @OA\Post(
     *   tags={"Descriptive Content Section"},
     *   path="/api/repo/hotel-descriptive-content-sections",
     *   summary="Create a new hotel descriptive content section",
     *   description="Create a new hotel descriptive content section entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "section_name", "start_date"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="section_name", type="string", example="General Information"),
     *       @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *       @OA\Property(property="end_date", type="string", format="date", example="2023-12-31")
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
     *
     * @OA\Get(
     *   tags={"Descriptive Content Section"},
     *   path="/api/repo/hotel-descriptive-content-sections/{id}",
     *   summary="Get hotel descriptive content section details",
     *   description="Retrieve details of a specific hotel descriptive content section.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel descriptive content section",
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
     *
     * @OA\Put(
     *   tags={"Descriptive Content Section"},
     *   path="/api/repo/hotel-descriptive-content-sections/{id}",
     *   summary="Update hotel descriptive content section details",
     *   description="Update details of a specific hotel descriptive content section.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel descriptive content section",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "section_name", "start_date"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="section_name", type="string", example="General Information"),
     *       @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *       @OA\Property(property="end_date", type="string", format="date", example="2023-12-31")
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
     *
     * @OA\Delete(
     *   tags={"Descriptive Content Section"},
     *   path="/api/repo/hotel-descriptive-content-sections/{id}",
     *   summary="Delete a hotel descriptive content section",
     *   description="Delete a specific hotel descriptive content section.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel descriptive content section",
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

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|exists:pd_hotels,id',
            'section_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ];
    }
}
