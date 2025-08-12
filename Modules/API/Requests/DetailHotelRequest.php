<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

class DetailHotelRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Content API"},
     *   path="/api/content/detail",
     *   summary="(Deprecated) Detail Hotels",
     *   description="Get detailed information about a hotel.",
     *
     *   @OA\Parameter(
     *      name="type",
     *      in="query",
     *      required=true,
     *      description="Type of content to search (e.g., 'hotel').",
     *
     *      @OA\Schema(
     *        type="string",
     *        example="hotel"
     *      )
     *    ),
     *
     *   @OA\Parameter(
     *      name="property_id",
     *      in="query",
     *      required=true,
     *      description="Giata ID of the property to get details for (e.g., 98736411).",
     *
     *      @OA\Schema(
     *        type="integer",
     *        example=98736411
     *      )
     *    ),
     *
     *   @OA\Parameter(
     *       name="supplier_data",
     *       in="query",
     *       required=false,
     *       description="Get Supplier Data",
     *
     *       @OA\Schema(
     *         type="boolean",
     *         example="true"
     *       )
     *     ),
     *
     *   @OA\Parameter(
     *      name="room_type_codes",
     *      in="query",
     *      required=false,
     *      description="String with codes delimitted by coma. (e.g., ODK,Single)",
     *
     *       @OA\Schema(
     *          type="string",
     *          example="ODK,Single"
     *        )
     *      ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/ContentDetailResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/ContentDetailResponse", example="ContentDetailResponse"),
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
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     *
     * @OA\Get(
     *   tags={"Content API"},
     *   path="/api/v1/content/detail",
     *   summary="Detail Hotels",
     *   description="Get detailed information about a hotel.",
     *
     *   @OA\Parameter(
     *      name="type",
     *      in="query",
     *      required=true,
     *      description="Type of content to search (e.g., 'hotel').",
     *
     *      @OA\Schema(
     *        type="string",
     *        example="hotel"
     *      )
     *    ),
     *
     *   @OA\Parameter(
     *       name="property_ids",
     *       in="query",
     *       required=true,
     *       description="Giata IDs of the properties to get details for (e.g., 98736411, 12345678).",
     *
     *       @OA\Schema(
     *         type="string",
     *         example="26319691,21569211"
     *       )
     *     ),
     *
     *   @OA\Parameter(
     *       name="supplier_data",
     *       in="query",
     *       required=false,
     *       description="Get Supplier Data",
     *
     *       @OA\Schema(
     *         type="boolean",
     *         example="true"
     *       )
     *     ),
     *   @OA\Parameter(
     *      name="room_type_codes",
     *      in="query",
     *      required=false,
     *      description="String with codes delimitted by coma. (e.g., ODK,Single)",
     *
     *       @OA\Schema(
     *          type="string",
     *          example="ODK,Single"
     *        )
     *      ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/ContentDetailV1Response",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/ContentDetailV1Response", example="ContentDetailV1Response"),
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
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function rules(): array
    {
        return [
            'property_id' => 'required_without_all:property_ids,giata_ids|int|digits_between:4,12',
            'property_ids' => 'required_without_all:property_id,giata_ids|string',
            'giata_ids' => 'required_without_all:property_id,property_ids|string',

            'consortia_affiliation' => 'string|nullable',

            'supplier_data' => 'string|in:true,false',

            'type' => 'required|in:hotel,flight,combo',
            'supplier' => 'string',
            'room_type_codes' => 'string',
        ];
    }
}
