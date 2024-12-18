<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelRoomRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Hotel | Rooms"},
     *   path="/api/repo/hotel-rooms",
     *   summary="Get all hotel rooms",
     *   description="Retrieve all hotel room records with optional filters.",
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
     *     name="name",
     *     in="query",
     *     required=false,
     *     description="Filter by room name",
     *     @OA\Schema(
     *       type="string",
     *       example="Deluxe Room"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="hbsi_data_mapped_name",
     *     in="query",
     *     required=false,
     *     description="Filter by HBSI data mapped name",
     *     @OA\Schema(
     *       type="string",
     *       example="Deluxe Room Mapped"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="description",
     *     in="query",
     *     required=false,
     *     description="Filter by room description",
     *     @OA\Schema(
     *       type="string",
     *       example="A luxurious room with all amenities."
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
     *   security={{ "apiAuth": {} }}
     * )

     *
     * @OA\Post(
     *   tags={"Hotel | Rooms"},
     *   path="/api/repo/hotel-rooms",
     *   summary="Create a new hotel room",
     *   description="Create a new hotel room entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "name", "hbsi_data_mapped_name", "description"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Deluxe Room"),
     *       @OA\Property(property="hbsi_data_mapped_name", type="string", example="Deluxe Room Mapped"),
     *       @OA\Property(property="description", type="string", example="A luxurious room with all amenities.")
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
     *   tags={"Hotel | Rooms"},
     *   path="/api/repo/hotel-rooms/{id}",
     *   summary="Get hotel room details",
     *   description="Retrieve details of a specific hotel room.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel room",
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
     *   tags={"Hotel | Rooms"},
     *   path="/api/repo/hotel-rooms/{id}",
     *   summary="Update hotel room details",
     *   description="Update details of a specific hotel room.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel room",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "name", "hbsi_data_mapped_name", "description"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Deluxe Room"),
     *       @OA\Property(property="hbsi_data_mapped_name", type="string", example="Deluxe Room Mapped"),
     *       @OA\Property(property="description", type="string", example="A luxurious room with all amenities.")
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
     *   tags={"Hotel | Rooms"},
     *   path="/api/repo/hotel-rooms/{id}",
     *   summary="Delete a hotel room",
     *   description="Delete a specific hotel room.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel room",
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
     *
     * @OA\Post(
     *   tags={"Hotel | Rooms"},
     *   path="/api/repo/hotel-rooms/{id}/attach-gallery",
     *   summary="Attach a gallery to a hotel room",
     *   description="Attach a gallery to a specific hotel room.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel room",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"gallery_id"},
     *       @OA\Property(property="gallery_id", type="integer", example=1)
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
     *   security={{ "apiAuth": {} }}
     * )
     *
     * @OA\Post(
     *   tags={"Hotel | Rooms"},
     *   path="/api/repo/hotel-rooms/{id}/detach-gallery",
     *   summary="Detach a gallery from a hotel room",
     *   description="Detach a gallery from a specific hotel room.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel room",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"gallery_id"},
     *       @OA\Property(property="gallery_id", type="integer", example=1)
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
     *   security={{ "apiAuth": {} }}
     * )
     */


    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer|exists:pd_hotels,id',
            'name' => 'required|string|max:255',
            'hbsi_data_mapped_name' => 'required|string|max:255',
            'description' => 'required|string',
            'supplier_codes' => 'nullable|array',
        ];
    }
}
