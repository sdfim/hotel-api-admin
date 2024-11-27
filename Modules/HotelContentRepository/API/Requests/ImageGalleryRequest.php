<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class ImageGalleryRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Image Galleries"},
     *   path="/api/repo/image-galleries",
     *   summary="Get all image galleries",
     *   description="Retrieve all image gallery records with optional filters.",
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
     *   tags={"Image Galleries"},
     *   path="/api/repo/image-galleries",
     *   summary="Create a new image gallery",
     *   description="Create a new image gallery entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"gallery_name"},
     *       @OA\Property(property="gallery_name", type="string", example="Summer Vacation"),
     *       @OA\Property(property="description", type="string", example="Photos from summer vacation")
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
     *   tags={"Image Galleries"},
     *   path="/api/repo/image-galleries/{id}",
     *   summary="Get image gallery details",
     *   description="Retrieve details of a specific image gallery.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the image gallery",
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
     *   tags={"Image Galleries"},
     *   path="/api/repo/image-galleries/{id}",
     *   summary="Update image gallery details",
     *   description="Update details of a specific image gallery.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the image gallery",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"gallery_name"},
     *       @OA\Property(property="gallery_name", type="string", example="Summer Vacation"),
     *       @OA\Property(property="description", type="string", example="Photos from summer vacation")
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
     *   tags={"Image Galleries"},
     *   path="/api/repo/image-galleries/{id}",
     *   summary="Delete an image gallery",
     *   description="Delete a specific image gallery.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the image gallery",
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

     * @OA\Post(
     *   tags={"Image Galleries"},
     *   path="/api/repo/image-galleries/{id}/attach-image",
     *   summary="Attach an image to a gallery",
     *   description="Attach an image to a specific image gallery.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the image gallery",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"image_id"},
     *       @OA\Property(property="image_id", type="integer", example=1)
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

     * @OA\Post(
     *   tags={"Image Galleries"},
     *   path="/api/repo/image-galleries/{id}/detach-image",
     *   summary="Detach an image from a gallery",
     *   description="Detach an image from a specific image gallery.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the image gallery",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"image_id"},
     *       @OA\Property(property="image_id", type="integer", example=1)
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
     */



    public function rules(): array
    {
        return [
            'gallery_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
