<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelDescriptiveContentRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Descriptive Content"},
     *   path="/api/repo/hotel-descriptive-contents",
     *   summary="Get all hotel descriptive contents",
     *   description="Retrieve all hotel descriptive content records with optional filters.",
     *   @OA\Parameter(
     *     name="content_sections_id",
     *     in="query",
     *     required=false,
     *     description="Filter by content sections ID",
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
     *
     * @OA\Post(
     *   tags={"Descriptive Content"},
     *   path="/api/repo/hotel-descriptive-contents",
     *   summary="Create a new hotel descriptive content",
     *   description="Create a new hotel descriptive content entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"content_sections_id", "descriptive_type_id", "value"},
     *       @OA\Property(property="content_sections_id", type="integer", example=1),
     *       @OA\Property(property="descriptive_type_id", type="integer", example=1),
     *       @OA\Property(property="value", type="string", example="Description text")
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
     *   tags={"Descriptive Content"},
     *   path="/api/repo/hotel-descriptive-contents/{id}",
     *   summary="Get hotel descriptive content details",
     *   description="Retrieve details of a specific hotel descriptive content.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel descriptive content",
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
     *   tags={"Descriptive Content"},
     *   path="/api/repo/hotel-descriptive-contents/{id}",
     *   summary="Update hotel descriptive content details",
     *   description="Update details of a specific hotel descriptive content.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel descriptive content",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"content_sections_id", "descriptive_type_id", "value"},
     *       @OA\Property(property="content_sections_id", type="integer", example=1),
     *       @OA\Property(property="descriptive_type_id", type="integer", example=1),
     *       @OA\Property(property="value", type="string", example="Updated description text")
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
     *   tags={"Descriptive Content"},
     *   path="/api/repo/hotel-descriptive-contents/{id}",
     *   summary="Delete a hotel descriptive content",
     *   description="Delete a specific hotel descriptive content.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel descriptive content",
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
            'content_sections_id' => 'required|integer',
            'descriptive_type_id' => 'required|integer',
            'value' => 'required|string',
        ];
    }
}
