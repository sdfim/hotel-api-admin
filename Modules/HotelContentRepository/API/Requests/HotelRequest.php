<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Hotels"},
     *   path="/api/repo/hotels",
     *   summary="Get all hotels",
     *   description="Retrieve all hotel records with optional filters.",
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=false,
     *     description="Filter by hotel name",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="type",
     *     in="query",
     *     required=false,
     *     description="Filter by hotel type",
     *     @OA\Schema(
     *       type="string",
     *       enum={"Direct connection", "Manual contract", "Commission tracking"}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="verified",
     *     in="query",
     *     required=false,
     *     description="Filter by verification status",
     *     @OA\Schema(
     *       type="boolean"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="address",
     *     in="query",
     *     required=false,
     *     description="Filter by hotel address",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="star_rating",
     *     in="query",
     *     required=false,
     *     description="Filter by star rating",
     *     @OA\Schema(
     *       type="integer",
     *       minimum=1,
     *       maximum=5
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="website",
     *     in="query",
     *     required=false,
     *     description="Filter by hotel website",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="num_rooms",
     *     in="query",
     *     required=false,
     *     description="Filter by number of rooms",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="location",
     *     in="query",
     *     required=false,
     *     description="Filter by hotel location",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="content_source_id",
     *     in="query",
     *     required=false,
     *     description="Filter by content source ID",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="room_images_source_id",
     *     in="query",
     *     required=false,
     *     description="Filter by room images source ID",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="property_images_source_id",
     *     in="query",
     *     required=false,
     *     description="Filter by property images source ID",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="travel_agent_commission",
     *     in="query",
     *     required=false,
     *     description="Filter by travel_agent_commission",
     *     @OA\Schema(
     *       type="float"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="hotel_board_basis",
     *     in="query",
     *     required=false,
     *     description="Filter by hotel board basis",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="default_currency",
     *     in="query",
     *     required=false,
     *     description="Filter by default currency",
     *     @OA\Schema(
     *       type="string"
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
     *   tags={"Hotels"},
     *   path="/api/repo/hotels",
     *   summary="Create a new hotel",
     *   description="Create a new hotel entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"name", "type", "verified", "address", "star_rating", "website", "num_rooms", "location", "content_source_id", "room_images_source_id", "property_images_source_id", "travel_agent_commission", "hotel_board_basis", "default_currency"},
     *       @OA\Property(property="name", type="string", example="Example Hotel"),
     *       @OA\Property(property="type", type="string", enum={"Direct connection", "Manual contract", "Commission tracking"}, example="Direct connection"),
     *       @OA\Property(property="verified", type="boolean", example=true),
     *       @OA\Property(property="address", type="string", example="123 Example Street, Example City, EX 12345"),
     *       @OA\Property(property="star_rating", type="integer", example=5),
     *       @OA\Property(property="website", type="string", example="https://examplehotel.com"),
     *       @OA\Property(property="num_rooms", type="integer", example=100),
     *       @OA\Property(property="location", type="string", example="Example City"),
     *       @OA\Property(property="content_source_id", type="integer", example=1),
     *       @OA\Property(property="room_images_source_id", type="integer", example=1),
     *       @OA\Property(property="property_images_source_id", type="integer", example=1),
     *       @OA\Property(property="travel_agent_commission", type="float", example=true),
     *       @OA\Property(property="hotel_board_basis", type="string", example="All Inclusive"),
     *       @OA\Property(property="default_currency", type="string", example="USD")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
 *
     * @OA\Get(
     *   tags={"Hotels"},
     *   path="/api/repo/hotels/{id}",
     *   summary="Get hotel details",
     *   description="Retrieve details of a specific hotel.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/NotFoundResponse", example="NotFoundResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
 *
     * @OA\Put(
     *   tags={"Hotels"},
     *   path="/api/repo/hotels/{id}",
     *   summary="Update hotel details",
     *   description="Update details of a specific hotel.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"name", "type", "verified", "address", "star_rating", "website", "num_rooms", "location", "content_source_id", "room_images_source_id", "property_images_source_id", "travel_agent_commission", "hotel_board_basis", "default_currency"},
     *       @OA\Property(property="name", type="string", example="Example Hotel"),
     *       @OA\Property(property="type", type="string", enum={"Direct connection", "Manual contract", "Commission tracking"}, example="Direct connection"),
     *       @OA\Property(property="verified", type="boolean", example=true),
     *       @OA\Property(property="address", type="string", example="123 Example Street, Example City, EX 12345"),
     *       @OA\Property(property="star_rating", type="integer", example=5),
     *       @OA\Property(property="website", type="string", example="https://examplehotel.com"),
     *       @OA\Property(property="num_rooms", type="integer", example=100),
     *       @OA\Property(property="location", type="string", example="Example City"),
     *       @OA\Property(property="content_source_id", type="integer", example=1),
     *       @OA\Property(property="room_images_source_id", type="integer", example=1),
     *       @OA\Property(property="property_images_source_id", type="integer", example=1),
     *       @OA\Property(property="travel_agent_commission", type="float", example=true),
     *       @OA\Property(property="hotel_board_basis", type="string", example="All Inclusive"),
     *       @OA\Property(property="default_currency", type="string", example="USD")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/NotFoundResponse", example="NotFoundResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
 *
     * @OA\Delete(
     *   tags={"Hotels"},
     *   path="/api/repo/hotels/{id}",
     *   summary="Delete a hotel",
     *   description="Delete a specific hotel.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/NotFoundResponse", example="NotFoundResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )

     * @OA\Post(
     *   tags={"Hotels"},
     *   path="/api/repo/hotels/{id}/attach-gallery",
     *   summary="Attach a gallery to a hotel",
     *   description="Attach a gallery to a specific hotel.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
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

     * @OA\Post(
     *   tags={"Hotels"},
     *   path="/api/repo/hotels/{id}/detach-gallery",
     *   summary="Detach a gallery from a hotel",
     *   description="Detach a gallery from a specific hotel.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
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

     * @OA\Post(
     *   tags={"Hotels"},
     *   path="/api/repo/hotels/{id}/attach-web-finder",
     *   summary="Attach a web finder to a hotel",
     *   description="Attach a web finder to a specific hotel.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"web_finder_id"},
     *       @OA\Property(property="web_finder_id", type="integer", example=1)
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

     * @OA\Post(
     *   tags={"Hotels"},
     *   path="/api/repo/hotels/{id}/detach-web-finder",
     *   summary="Detach a web finder from a hotel",
     *   description="Detach a web finder from a specific hotel.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"web_finder_id"},
     *       @OA\Property(property="web_finder_id", type="integer", example=1)
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

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Direct connection,Manual contract,Commission tracking',
            'verified' => 'required|boolean',
            'address' => 'required|string',
            'star_rating' => 'required|integer|min:1|max:5',
            'website' => 'required|string|max:255',
            'num_rooms' => 'required|integer',
            'location' => 'required|string|max:255',
            'content_source_id' => 'required|exists:pd_content_sources,id',
            'room_images_source_id' => 'required|exists:pd_content_sources,id',
            'property_images_source_id' => 'required|exists:pd_content_sources,id',
            'travel_agent_commission' => 'required|numeric',
            'hotel_board_basis' => 'required|string|max:255',
            'default_currency' => 'required|string|max:10',
            'weight' => 'integer',
        ];
    }
}
