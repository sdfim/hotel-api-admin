<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

/**
 * @OA\Get(
 *   tags={"Key Mappings"},
 *   path="/api/repo/key-mappings",
 *   summary="Get all key mappings",
 *   description="Retrieve all key mapping records with optional filters.",
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
 *   tags={"Key Mappings"},
 *   path="/api/repo/key-mappings",
 *   summary="Create a new key mapping",
 *   description="Create a new key mapping.",
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       type="object",
 *       required={"hotel_id", "key_id", "key_mapping_owner_id"},
 *       @OA\Property(property="hotel_id", type="integer", example=1),
 *       @OA\Property(property="key_id", type="string", example="Key123"),
 *       @OA\Property(property="key_mapping_owner_id", type="integer", example=1)
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
 *   tags={"Key Mappings"},
 *   path="/api/repo/key-mappings/{id}",
 *   summary="Get key mapping details",
 *   description="Retrieve details of a specific key mapping.",
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="ID of the key mapping",
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
 *   tags={"Key Mappings"},
 *   path="/api/repo/key-mappings/{id}",
 *   summary="Update key mapping details",
 *   description="Update details of a specific key mapping.",
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="ID of the key mapping",
 *     @OA\Schema(
 *       type="integer",
 *       example=1
 *     )
 *   ),
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       type="object",
 *       required={"hotel_id", "key_id", "key_mapping_owner_id"},
 *       @OA\Property(property="hotel_id", type="integer", example=1),
 *       @OA\Property(property="key_id", type="string", example="Key123"),
 *       @OA\Property(property="key_mapping_owner_id", type="integer", example=1)
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
 *   tags={"Key Mappings"},
 *   path="/api/repo/key-mappings/{id}",
 *   summary="Delete a key mapping",
 *   description="Delete a specific key mapping.",
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="ID of the key mapping",
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
class KeyMappingRequest extends ApiRequest
{    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer|exists:pd_hotels,id',
            'key_id' => 'required|string|max:255',
            'key_mapping_owner_id' => 'required|exists:pd_key_mapping_owners,id',
        ];
    }
}
