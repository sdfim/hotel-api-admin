<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelFeeTaxRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Fee and Tax"},
     *   path="/api/repo/hotel-fee-taxes",
     *   summary="Get all hotel fee taxes",
     *   description="Retrieve all hotel fee tax records with optional filters.",
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
     *     description="Filter by name",
     *     @OA\Schema(
     *       type="string",
     *       example="Service Fee"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="net_value",
     *     in="query",
     *     required=false,
     *     description="Filter by net value",
     *     @OA\Schema(
     *       type="number",
     *       format="float",
     *       example=100.0
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="rack_value",
     *     in="query",
     *     required=false,
     *     description="Filter by rack value",
     *     @OA\Schema(
     *       type="number",
     *       format="float",
     *       example=120.0
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="tax",
     *     in="query",
     *     required=false,
     *     description="Filter by tax",
     *     @OA\Schema(
     *       type="number",
     *       format="float",
     *       example=20.0
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="type",
     *     in="query",
     *     required=false,
     *     description="Filter by type",
     *     @OA\Schema(
     *       type="string",
     *       enum={"per_person", "per_night", "per_person_per_night"},
     *       example="per_person"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="fee_category",
     *     in="query",
     *     required=false,
     *     description="Filter by fee category",
     *     @OA\Schema(
     *       type="string",
     *       example="Service"
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
     *   tags={"Fee and Tax"},
     *   path="/api/repo/hotel-fee-taxes",
     *   summary="Create a new hotel fee tax",
     *   description="Create a new hotel fee tax entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "name", "net_value", "rack_value", "tax", "type", "fee_category"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Service Fee"),
     *       @OA\Property(property="net_value", type="number", format="float", example=100.0),
     *       @OA\Property(property="rack_value", type="number", format="float", example=120.0),
     *       @OA\Property(property="tax", type="number", format="float", example=20.0),
     *       @OA\Property(property="type", type="string", enum={"per_person", "per_night", "per_person_per_night"}, example="per_person"),
     *       @OA\Property(property="fee_category", type="string", example="Service")
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
     *   tags={"Fee and Tax"},
     *   path="/api/repo/hotel-fee-taxes/{id}",
     *   summary="Get hotel fee tax details",
     *   description="Retrieve details of a specific hotel fee tax.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel fee tax",
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
     *   tags={"Fee and Tax"},
     *   path="/api/repo/hotel-fee-taxes/{id}",
     *   summary="Update hotel fee tax details",
     *   description="Update details of a specific hotel fee tax.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel fee tax",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"hotel_id", "name", "net_value", "rack_value", "tax", "type", "fee_category"},
     *       @OA\Property(property="hotel_id", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Service Fee"),
     *       @OA\Property(property="net_value", type="number", format="float", example=100.0),
     *       @OA\Property(property="rack_value", type="number", format="float", example=120.0),
     *       @OA\Property(property="tax", type="number", format="float", example=20.0),
     *       @OA\Property(property="type", type="string", enum={"per_person", "per_night", "per_person_per_night"}, example="per_person"),
     *       @OA\Property(property="fee_category", type="string", example="Service")
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
     *   tags={"Fee and Tax"},
     *   path="/api/repo/hotel-fee-taxes/{id}",
     *   summary="Delete a hotel fee tax",
     *   description="Delete a specific hotel fee tax.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel fee tax",
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

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer|exists:pd_hotels,id',
            'name' => 'required|string|max:255',
            'net_value' => 'required|numeric',
            'rack_value' => 'required|numeric',
            'tax' => 'required|numeric',
            'type' => 'required|in:per_person,per_night,per_person_per_night',
            'fee_category' => 'required|string|max:255',
        ];
    }
}
