<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;
use Modules\Enums\HotelSaleTypeEnum;

class HotelRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Hotel | Hotels"},
     *   path="/api/repo/hotels",
     *   summary="Get all hotels",
     *   description="Retrieve all hotel records with optional filters.",
     *
     *   @OA\Parameter(
     *     name="sale_type",
     *     in="query",
     *     required=false,
     *     description="Filter by sale type",
     *
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="address",
     *     in="query",
     *     required=false,
     *     description="Filter by address",
     *
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="star_rating",
     *     in="query",
     *     required=false,
     *     description="Filter by star rating",
     *
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="num_rooms",
     *     in="query",
     *     required=false,
     *     description="Filter by number of rooms",
     *
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="hotel_board_basis",
     *     in="query",
     *     required=false,
     *     description="Filter by hotel board basis",
     *
     *     @OA\Schema(
     *       type="string"
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
     *   tags={"Hotel | Hotels"},
     *   path="/api/repo/hotels",
     *   summary="Create a new hotel",
     *   description="Create a new hotel entry.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"sale_type", "address", "star_rating", "num_rooms", "room_images_source_id", "hotel_board_basis"},
     *
     *       @OA\Property(property="sale_type", type="string", example="sale"),
     *       @OA\Property(property="address", type="string", example="123 Main St"),
     *       @OA\Property(property="star_rating", type="integer", example=5),
     *       @OA\Property(property="num_rooms", type="integer", example=100),
     *       @OA\Property(property="room_images_source_id", type="integer", example=1),
     *       @OA\Property(property="hotel_board_basis", type="string", example="All Inclusive"),
     *       @OA\Property(property="weight", type="integer", example=10)
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
     *   tags={"Hotel | Hotels"},
     *   path="/api/repo/hotels/{id}",
     *   summary="Get hotel details",
     *   description="Retrieve details of a specific hotel.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
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
     *   tags={"Hotel | Hotels"},
     *   path="/api/repo/hotels/{id}",
     *   summary="Update hotel details",
     *   description="Update details of a specific hotel.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
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
     *       required={"sale_type", "address", "star_rating", "num_rooms", "room_images_source_id", "hotel_board_basis"},
     *
     *       @OA\Property(property="sale_type", type="string", example="sale"),
     *       @OA\Property(property="address", type="string", example="123 Main St"),
     *       @OA\Property(property="star_rating", type="integer", example=5),
     *       @OA\Property(property="num_rooms", type="integer", example=100),
     *       @OA\Property(property="room_images_source_id", type="integer", example=1),
     *       @OA\Property(property="hotel_board_basis", type="string", example="All Inclusive"),
     *       @OA\Property(property="weight", type="integer", example=10)
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
     *   tags={"Hotel | Hotels"},
     *   path="/api/repo/hotels/{id}",
     *   summary="Delete a hotel",
     *   description="Delete a specific hotel.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
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
     * @OA\Post(
     *   tags={"Hotel | Hotels"},
     *   path="/api/repo/hotels/{id}/attach-web-finder",
     *   summary="Attach a web finder to a hotel",
     *   description="Attach a web finder to a specific hotel.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
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
     *       required={"web_finder_id"},
     *
     *       @OA\Property(property="web_finder_id", type="integer", example=1)
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
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
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
     *   tags={"Hotel | Hotels"},
     *   path="/api/repo/hotels/{id}/detach-web-finder",
     *   summary="Detach a web finder from a hotel",
     *   description="Detach a web finder from a specific hotel.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
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
     *       required={"web_finder_id"},
     *
     *       @OA\Property(property="web_finder_id", type="integer", example=1)
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
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
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
            'giata_code' => 'required|int',
            'sale_type' => 'required|string|in:'.implode(',', array_column(HotelSaleTypeEnum::cases(), 'value')),
            'featured_flag' => 'boolean',
            'address' => 'required|string',
            'star_rating' => 'required|integer|min:1|max:5',
            'num_rooms' => 'required|integer',
            'room_images_source_id' => 'required|exists:pd_content_sources,id',
            'hotel_board_basis' => 'required|string|max:255',
            'weight' => 'integer',
            'travel_agent_commission' => 'numeric',
            'holdable' => 'boolean',
        ];
    }
}
