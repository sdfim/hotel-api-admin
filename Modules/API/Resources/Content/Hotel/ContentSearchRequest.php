<?php

namespace Modules\API\Resources\Content\Hotel;

/**
 * @OA\Schema(
 *     schema="ContentSearchRequestPlace",
 *     title="Content Search Request Place",
 *     description="Schema Content Search Request GIATA Place",
 *     type="object",
 *     required={"query"},
 *     @OA\Property(
 *       property="type",
 *       type="string",
 *       description="Type of content to search (e.g., 'hotel', 'flight', 'combo').",
 *       example="hotel"
 *     ),
 *     @OA\Property(
 *       property="place",
 *       type="string",
 *       description="GIATA Place key.",
 *       example="9bb07983384eb956ae88e154b99b51a1"
 *     ),
 *     @OA\Property(
 *       property="rating",
 *       type="integer",
 *       description="Rating of the hotel.",
 *       example=4
 *     ),
 *     @OA\Property(
 *       property="page",
 *       type="integer",
 *       description="Page number.",
 *       example=1
 *     ),
 *     @OA\Property(
 *       property="results_per_page",
 *       type="integer",
 *       description="Number of results per page.",
 *       example=250
 *     )
 *   ),
 * @OA\Examples(
 *       example="ContentSearchRequestPlace",
 *       summary="Content Search by Place Eiffel Tower (Paris)",
 *       value=
 *          {
 *              "type": "hotel",
 *              "place": "9bb07983384eb956ae88e154b99b51a1",
 *              "rating": 4,
 *              "page": 1,
 *              "results_per_page": 20
 *          }
 *   ),
 * @OA\Schema(
 *   schema="ContentSearchRequestDestination",
 *   title="Content Search Request Destination",
 *   description="Schema Content Search Request Destination",
 *   type="object",
 *   required={"query"},
 *   @OA\Property(
 *     property="type",
 *     type="string",
 *     description="Type of content to search (e.g., 'hotel', 'flight', 'combo').",
 *     example="hotel"
 *   ),
 *   @OA\Property(
 *     property="destination",
 *     type="integer",
 *     description="Destination ID.",
 *     example=1175
 *   ),
 *   @OA\Property(
 *     property="rating",
 *     type="integer",
 *     description="Rating of the hotel.",
 *     example=2
 *   ),
 *   @OA\Property(
 *     property="page",
 *     type="integer",
 *     description="Page number.",
 *     example=1
 *   ),
 *   @OA\Property(
 *     property="results_per_page",
 *     type="integer",
 *     description="Number of results per page.",
 *     example=250
 *   )
 * ),
 * @OA\Examples(
 *     example="ContentSearchRequestDestination",
 *     summary="Content Search by Destination",
 *     value=
 *        {
 *            "type": "hotel",
 *            "destination": 1175,
 *            "rating": 2,
 *            "page": 1,
 *            "results_per_page": 20
 *        }
 * ),
 *
 * @OA\Schema(
 *   schema="ContentSearchRequestSupplierHotelName",
 *   title="Content Search Request Supplier and Hotel Name",
 *   description="Schema Content Search Request Supplier and Hotel Name",
 *   type="object",
 *   required={"query"},
 *   @OA\Property(
 *     property="type",
 *     type="string",
 *     description="Type of content to search (e.g., 'hotel', 'flight', 'combo').",
 *     example="hotel"
 *   ),
 *   @OA\Property(
 *     property="hotel_name",
 *     type="string",
 *     description="Hotel name.",
 *     example="Sheraton"
 *   ),
 *   @OA\Property(
 *     property="supplier",
 *     type="string",
 *     description="Supplier (e.g., 'Expedia').",
 *     example="Expedia"
 *   ),
 *   @OA\Property(
 *     property="destination",
 *     type="integer",
 *     description="Destination ID.",
 *     example=961
 *   ),
 *   @OA\Property(
 *     property="rating",
 *     type="integer",
 *     description="Rating of the hotel.",
 *     example=3.5
 *   ),
 *   @OA\Property(
 *     property="page",
 *     type="integer",
 *     description="Page number.",
 *     example=1
 *   ),
 *   @OA\Property(
 *     property="results_per_page",
 *     type="integer",
 *     description="Number of results per page.",
 *     example=250
 *   )
 * ),
 * @OA\Examples(
 *     example="ContentSearchRequestSupplierHotelName",
 *     summary="Content Search by Supplier and Hotel Name",
 *     value=
 *        {
 *            "type": "hotel",
 *            "hotel_name": "Sheraton",
 *            "supplier": "Expedia",
 *            "destination": 961,
 *            "rating": 3.5,
 *            "page": 1,
 *            "results_per_page": 20
 * }
 * ),
 * @OA\Schema(
 *   schema="ContentSearchRequestCoordinates",
 *   title="Content Search Request Coordinates",
 *   description="Schema Content Search Request Coordinates",
 *   type="object",
 *   required={"type", "latitude", "longitude", "radius"},
 *   @OA\Property(
 *     property="type",
 *     type="string",
 *     description="Type of content to search (e.g., 'hotel', 'flight', 'combo').",
 *     example="hotel"
 *    ),
 *    @OA\Property(
 *      property="latitude",
 *      type="number",
 *      format="float",
 *      description="Latitude of the center point.",
 *      example=40.7480
 *    ),
 *    @OA\Property(
 *      property="longitude",
 *      type="number",
 *      format="float",
 *      description="Longitude of the center point.",
 *      example=-73.991
 *    ),
 *    @OA\Property(
 *      property="radius",
 *      type="integer",
 *      description="Radius of the search area in kilometers.",
 *      example=20
 *    ),
 *    @OA\Property(
 *      property="rating",
 *      type="integer",
 *      description="Rating of the hotel.",
 *      example=1
 *    ),
 *    @OA\Property(
 *      property="page",
 *      type="integer",
 *      description="Page number.",
 *      example=1
 *    ),
 *    @OA\Property(
 *      property="results_per_page",
 *      type="integer",
 *      description="Number of results per page.",
 *      example=20
 *     )
 *   )
 * ),
 * @OA\Examples(
 *     example="ContentSearchRequestCoordinates",
 *     summary="Content Search by Coordinates",
 *     value=
 *        {
 *            "type": "hotel",
 *            "latitude": 40.7480,
 *            "longitude": -73.991,
 *            "radius": 20,
 *            "rating": 1,
 *            "page": 1,
 *            "results_per_page": 20
 *        }
 * )
 */
class ContentSearchRequest
{
}
