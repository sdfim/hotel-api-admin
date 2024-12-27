<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class SearchHotelRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Content API"},
     *   path="/api/v1/content/search",
     *   summary="Search Hotels",
     *   description="Content Search for hotels by places/destination or coordinates.<br> The '<b>place</b>' value should be used from the endpoint api/content/destinations",
     *
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *
     *     @OA\JsonContent(
     *       oneOf={
     *
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestGooglePlace"),
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestDestination"),
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestCoordinates"),
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestSupplierHotelName"),
     *         },
     *       examples={
     *           "searchByGooglePlace": @OA\Schema(ref="#/components/examples/ContentSearchRequestGooglePlace", example="ContentSearchRequestGooglePlace"),
     *           "searchByDestination": @OA\Schema(ref="#/components/examples/ContentSearchRequestDestination", example="ContentSearchRequestDestination"),
     *           "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchRequestCoordinates", example="ContentSearchRequestCoordinates"),
     *           "searchBySupplierHotelName": @OA\Schema(ref="#/components/examples/ContentSearchRequestSupplierHotelName", example="ContentSearchRequestSupplierHotelName"),
     *       },
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/ContentSearchResponse",
     *       examples={
     *       "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchResponse", example="ContentSearchResponse"),
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
     * @OA\Post(
     *    tags={"Content API"},
     *    path="/api/content/search",
     *    summary="(Deprecated) Search Hotels",
     *    description="Content Search for hotels by places/destination or coordinates.<br> The '<b>place</b>' value should be used from the endpoint api/content/destinations",
     *
     *    @OA\RequestBody(
     *      description="JSON object containing the details of the reservation.",
     *      required=true,
     *
     *      @OA\JsonContent(
     *        oneOf={
     *             @OA\Schema(ref="#/components/schemas/ContentSearchRequestGooglePlace"),
     *             @OA\Schema(ref="#/components/schemas/ContentSearchRequestPlace"),
     *             @OA\Schema(ref="#/components/schemas/ContentSearchRequestDestination"),
     *             @OA\Schema(ref="#/components/schemas/ContentSearchRequestCoordinates"),
     *             @OA\Schema(ref="#/components/schemas/ContentSearchRequestSupplierHotelName"),
     *          },
     *        examples={
     *            "searchByGooglePlace": @OA\Schema(ref="#/components/examples/ContentSearchRequestGooglePlace", example="ContentSearchRequestGooglePlace"),
     *            "searchByPlace": @OA\Schema(ref="#/components/examples/ContentSearchRequestPlace", example="ContentSearchRequestPlace"),
     *            "searchByDestination": @OA\Schema(ref="#/components/examples/ContentSearchRequestDestination", example="ContentSearchRequestDestination"),
     *            "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchRequestCoordinates", example="ContentSearchRequestCoordinates"),
     *            "searchBySupplierHotelName": @OA\Schema(ref="#/components/examples/ContentSearchRequestSupplierHotelName", example="ContentSearchRequestSupplierHotelName"),
     *        },
     *      ),
     *    ),
     *
     *    @OA\Response(
     *      response=200,
     *      description="OK",
     *
     *      @OA\JsonContent(
     *        ref="#/components/schemas/ContentSearchResponse",
     *        examples={
     *        "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchResponse", example="ContentSearchResponse"),
     *        }
     *      )
     *    ),
     *
     *    @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *
     *      @OA\JsonContent(
     *        ref="#/components/schemas/BadRequestResponse",
     *        examples={
     *        "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *        }
     *      )
     *    ),
     *
     *    @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *
     *      @OA\JsonContent(
     *        ref="#/components/schemas/UnAuthenticatedResponse",
     *        examples={
     *        "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *        }
     *      )
     *    ),
     *    security={{ "apiAuth": {} }}
     *  )
     */

    public function rules(): array
    {
        return [
            'type' => 'required|in:hotel,flight',
            'rating' => 'numeric|between:1,5.5',
            'page' => 'integer|between:1,1000',
            'results_per_page' => 'integer|between:1,1000',
            'place' => 'required_without_all:latitude,longitude,destination|string|max:32',
            'destination' => 'required_without_all:latitude,longitude,place|integer|min:1',
            'latitude' => 'required_without_all:destination,place|decimal:2,8|min:-90|max:90',
            'longitude' => 'required_without_all:destination,place|decimal:2,8|min:-180|max:180',
            'radius' => 'required_without_all:destination|numeric|between:1,100',
            'supplier' => 'string',
            'hotel_name' => 'string',
        ];
    }
}
