<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;

class HotelWebFinderRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finder",
     *   summary="Get all hotel web finders",
     *   description="Retrieve all hotel web finder records with optional filters.",
     *
     *   @OA\Parameter(
     *     name="type",
     *     in="query",
     *     required=false,
     *     description="Filter by type",
     *
     *     @OA\Schema(
     *       type="string",
     *       example="Example Type"
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
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finder",
     *   summary="Create a new hotel web finder",
     *   description="Create a new hotel web finder entry.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"base_url", "finder", "website", "example"},
     *
     *       @OA\Property(property="base_url", type="string", example="https://example.com"),
     *       @OA\Property(property="finder", type="string", example="Example Finder"),
     *       @OA\Property(property="website", type="string", example="Example Website"),
     *       @OA\Property(property="example", type="string", example="Example")
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
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finder/{id}",
     *   summary="Get hotel web finder details",
     *   description="Retrieve details of a specific hotel web finder.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel web finder",
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
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finder/{id}",
     *   summary="Update hotel web finder details",
     *   description="Update details of a specific hotel web finder.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel web finder",
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
     *       required={"base_url", "finder", "website", "example"},
     *
     *       @OA\Property(property="base_url", type="string", example="https://example.com"),
     *       @OA\Property(property="finder", type="string", example="Example Finder"),
     *       @OA\Property(property="website", type="string", example="Example Website"),
     *       @OA\Property(property="example", type="string", example="Example")
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
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finder/{id}",
     *   summary="Delete a hotel web finder",
     *   description="Delete a specific hotel web finder.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel web finder",
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
            'base_url' => 'required|string|max:255',
            'finder' => 'required|string|max:255',
            'website' => 'required|string|max:255',
            'example' => 'string|max:255',
        ];
    }
}
