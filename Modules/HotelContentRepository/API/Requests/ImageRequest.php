<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class ImageRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Images"},
     *   path="/api/repo/images",
     *   summary="Get all images",
     *   description="Retrieve all image records with optional filters.",
     *   @OA\Parameter(
     *     name="tag",
     *     in="query",
     *     required=false,
     *     description="Filter by tag",
     *     @OA\Schema(
     *       type="string",
     *       example="Lobby"
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
     *   tags={"Images"},
     *   path="/api/repo/images",
     *   summary="Create a new image",
     *   description="Create a new image entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"image_url", "tag", "weight", "section_id"},
     *       @OA\Property(property="image_url", type="string", example="http://example.com/image.jpg"),
     *       @OA\Property(property="tag", type="string", example="Lobby"),
     *       @OA\Property(property="weight", type="integer", example=1),
     *       @OA\Property(property="section_id", type="integer", example=1)
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
     *   tags={"Images"},
     *   path="/api/repo/images/{id}",
     *   summary="Get image details",
     *   description="Retrieve details of a specific image.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the image",
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
     *   tags={"Images"},
     *   path="/api/repo/images/{id}",
     *   summary="Update image details",
     *   description="Update details of a specific image.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the image",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"image_url", "tag", "weight", "section_id"},
     *       @OA\Property(property="image_url", type="string", example="http://example.com/image.jpg"),
     *       @OA\Property(property="tag", type="string", example="Lobby"),
     *       @OA\Property(property="weight", type="integer", example=1),
     *       @OA\Property(property="section_id", type="integer", example=1)
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
     *   tags={"Images"},
     *   path="/api/repo/images/{id}",
     *   summary="Delete an image",
     *   description="Delete a specific image.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the image",
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
            'image_url' => 'required|string|max:255',
            'tag' => 'required|string|max:100',
            'weight' => 'required|integer',
            'section_id' => 'required|exists:pd_image_sections,id',
        ];
    }
}
