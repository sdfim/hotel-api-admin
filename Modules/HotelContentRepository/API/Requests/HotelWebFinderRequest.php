<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelWebFinderRequest extends FormRequest
{
    /**
     * @OA\Get(
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finders",
     *   summary="Get all hotel web finders",
     *   description="Retrieve all hotel web finder records with optional filters.",
     *   @OA\Parameter(
     *     name="base_url",
     *     in="query",
     *     required=false,
     *     description="Filter by base URL",
     *     @OA\Schema(
     *       website="string",
     *       example="https://example.com"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="finder",
     *     in="query",
     *     required=false,
     *     description="Filter by finder",
     *     @OA\Schema(
     *       website="string",
     *       example="Finder Example"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="type",
     *     in="query",
     *     required=false,
     *     description="Filter by type",
     *     @OA\Schema(
     *       type="string",
     *       example="Type Example"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="example",
     *     in="query",
     *     required=false,
     *     description="Filter by example",
     *     @OA\Schema(
     *       type="string",
     *       example="Example"
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
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finders",
     *   summary="Create a new hotel web finder",
     *   description="Create a new hotel web finder entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"base_url", "finder", "type"},
     *       @OA\Property(property="base_url", type="string", example="https://example.com"),
     *       @OA\Property(property="finder", type="string", example="Finder Example"),
     *       @OA\Property(property="type", type="string", example="Type Example"),
     *       @OA\Property(property="example", type="string", example="Example")
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
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finders/{id}",
     *   summary="Get hotel web finder details",
     *   description="Retrieve details of a specific hotel web finder.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel web finder",
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
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finders/{id}",
     *   summary="Update hotel web finder details",
     *   description="Update details of a specific hotel web finder.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel web finder",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"base_url", "finder", "type"},
     *       @OA\Property(property="base_url", type="string", example="https://example.com"),
     *       @OA\Property(property="finder", type="string", example="Finder Example"),
     *       @OA\Property(property="type", type="string", example="Type Example"),
     *       @OA\Property(property="example", type="string", example="Example")
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
     *   tags={"Hotel | Website Search Generation"},
     *   path="/api/repo/hotel-web-finders/{id}",
     *   summary="Delete a hotel web finder",
     *   description="Delete a specific hotel web finder.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel web finder",
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

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'base_url' => 'required|string|max:255',
            'finder' => 'required|string|max:255',
            'website' => 'required|string|max:255',
            'example' => 'string|max:255',
        ];
    }
}
