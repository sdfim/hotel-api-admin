<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

class DestinationRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Content API"},
     *   path="/api/content/destinations",
     *   summary="Search for hotels, points of interest, attractions, airports, cities and countries by name",
     *   description="Search is performed by hotel names, points of interest, attractions, airport names, city names, and country names. The 'hotel' parameter is required and must contain at least 3 characters. Pagination is supported via 'page' and 'per_page' parameters. The response returns an array of giata_ids, which can be used in the endpoints POST /api/v1/content/search and POST /api/pricing/search.",
     *
     *   @OA\Parameter(
     *     name="hotel",
     *     in="query",
     *     required=true,
     *     description="Search string for hotel names, points of interest, attractions, airports, city names, and country names. Minimum 3 characters.",
     *     @OA\Schema(type="string", minLength=3, example="Velas")
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     required=false,
     *     description="Page number for pagination.",
     *     @OA\Schema(type="integer", default=1)
     *   ),
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     required=false,
     *     description="Number of results per page.",
     *     @OA\Schema(type="integer", default=10)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "data": {
     *           "hotels": {
     *             {
     *               "name": "Grand Velas Riviera Maya",
     *               "giata_ids": {21569211},
     *               "type": "hotel",
     *               "source": "hotel"
     *             },
     *             {
     *               "name": "Grand Velas Los Cabos",
     *               "giata_ids": {75193953},
     *               "type": "hotel",
     *               "source": "hotel"
     *             },
     *             {
     *               "name": "Casa Velas",
     *               "giata_ids": {79837037},
     *               "type": "hotel",
     *               "source": "hotel"
     *             },
     *             {
     *               "name": "Grand Velas Riviera Nayarit",
     *               "giata_ids": {91606022},
     *               "type": "hotel",
     *               "source": "hotel"
     *             },
     *             {
     *               "name": "Mar del Cabo by Velas Resorts",
     *               "giata_ids": {19941851},
     *               "type": "hotel",
     *               "source": "hotel"
     *             },
     *             {
     *               "name": "San Jose Del Cabo",
     *               "giata_ids": {13350571, 19941851, 75193953},
     *               "type": "Resort",
     *               "source": "place"
     *             },
     *             {
     *               "name": "Velas Vallarta",
     *               "giata_ids": {81286080},
     *               "type": "hotel",
     *               "source": "hotel"
     *             },
     *             {
     *               "name": "Grand Velas Boutique Hotel Los Cabos",
     *               "giata_ids": {13350571},
     *               "type": "hotel",
     *               "source": "hotel"
     *             }
     *           },
     *           "counts": {
     *             "hotels": 7,
     *             "places": 1,
     *             "pois": 0
     *           },
     *           "page": 1,
     *           "per_page": 10
     *         }
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated"
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function rules(): array
    {
        return [
            'hotel' => 'required|string|min:3',

            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
