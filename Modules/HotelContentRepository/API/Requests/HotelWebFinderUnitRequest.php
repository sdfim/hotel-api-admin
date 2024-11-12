<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelWebFinderUnitRequest extends FormRequest
{
    /**
     * @OA\Get(
     *   tags={"Website Search Generation"},
     *   path="/api/repo/hotel-web-finder-units",
     *   summary="Get all hotel web finder units",
     *   description="Retrieve all hotel web finder unit records with optional filters.",
     *   @OA\Parameter(
     *     name="web_finder_id",
     *     in="query",
     *     required=false,
     *     description="Filter by web finder ID",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="field",
     *     in="query",
     *     required=false,
     *     description="Filter by field",
     *     @OA\Schema(
     *       type="string",
     *       example="Field Example"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="value",
     *     in="query",
     *     required=false,
     *     description="Filter by value",
     *     @OA\Schema(
     *       type="string",
     *       example="Value Example"
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
     *   tags={"Website Search Generation"},
     *   path="/api/repo/hotel-web-finder-units",
     *   summary="Create a new hotel web finder unit",
     *   description="Create a new hotel web finder unit entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"web_finder_id", "field", "value"},
     *       @OA\Property(property="web_finder_id", type="integer", example=1),
     *       @OA\Property(property="field", type="string", example="Field Example"),
     *       @OA\Property(property="value", type="string", example="Value Example")
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
     *   tags={"Website Search Generation"},
     *   path="/api/repo/hotel-web-finder-units/{id}",
     *   summary="Get hotel web finder unit details",
     *   description="Retrieve details of a specific hotel web finder unit.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel web finder unit",
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
     *   tags={"Website Search Generation"},
     *   path="/api/repo/hotel-web-finder-units/{id}",
     *   summary="Update hotel web finder unit details",
     *   description="Update details of a specific hotel web finder unit.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel web finder unit",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"web_finder_id", "field", "value"},
     *       @OA\Property(property="web_finder_id", type="integer", example=1),
     *       @OA\Property(property="field", type="string", example="Field Example"),
     *       @OA\Property(property="value", type="string", example="Value Example")
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
     *   tags={"Website Search Generation"},
     *   path="/api/repo/hotel-web-finder-units/{id}",
     *   summary="Delete a hotel web finder unit",
     *   description="Delete a specific hotel web finder unit.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the hotel web finder unit",
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
            'web_finder_id' => 'required|integer',
            'field' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ];
    }
}
