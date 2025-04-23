<?php

namespace Modules\API\Resources\Content\Hotel;

/**
 * @OA\Schema(
 *     schema="ContentSearchRequestPlace",
 *     title="Content Search Request Place",
 *     description="Schema Content Search Request GIATA Place",
 *     type="object",
 *     required={"query"},
 *
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
 *       property="consortia_affiliation",
 *       type="string",
 *       description="Consortium affiliation name. Valid values depend on system configuration.",
 *       example="Virtuoso"
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
 *
 * @OA\Examples(
 *       example="ContentSearchRequestPlace",
 *       summary="Content Search by Place Eiffel Tower (Paris)",
 *       value=
 *          {
 *              "type": "hotel",
 *              "place": "9bb07983384eb956ae88e154b99b51a1",
 *              "rating": 4,
 *              "page": 1,
 *              "consortia_affiliation": "Virtuoso",
 *              "results_per_page": 20
 *          }
 *   ),
 *
 * @OA\Schema(
 *      schema="ContentSearchRequestGooglePlace",
 *      title="Content Search Request Google Place",
 *      description="Schema Content Search Request Google Place",
 *      type="object",
 *      required={"query"},
 *
 *      @OA\Property(
 *        property="type",
 *        type="string",
 *        description="Type of content to search (e.g., 'hotel', 'flight', 'combo').",
 *        example="hotel"
 *      ),
 *      @OA\Property(
 *        property="place",
 *        type="string",
 *        description="Google Place key.",
 *        example="ChIJ21P2rgUrTI8Ris1fYjy3Ms4"
 *      ),
 *     @OA\Property(
 *         property="session",
 *         type="string",
 *         description="session",
 *         example="123"
 *      ),
 *     @OA\Property(
 *         property="radius",
 *         type="integer",
 *         description="radius",
 *         example=10
 *       ),
 *      @OA\Property(
 *        property="rating",
 *        type="integer",
 *        description="Rating of the hotel.",
 *        example=4
 *      ),
 *     @OA\Property(
 *       property="consortia_affiliation",
 *       type="string",
 *       description="Consortium affiliation name. Valid values depend on system configuration.",
 *       example="Virtuoso"
 *     ),
 *      @OA\Property(
 *        property="page",
 *        type="integer",
 *        description="Page number.",
 *        example=1
 *      ),
 *      @OA\Property(
 *        property="results_per_page",
 *        type="integer",
 *        description="Number of results per page.",
 *        example=250
 *      )
 *    ),
 *
 * @OA\Examples(
 *        example="ContentSearchRequestGooglePlace",
 *        summary="Content Search by Google Place (Cancun)",
 *        value=
 *           {
 *               "type": "hotel",
 *               "place": "ChIJ21P2rgUrTI8Ris1fYjy3Ms4",
 *               "session": "123",
 *               "radius": 10,
 *               "rating": 4,
 *                "consortia_affiliation": "Virtuoso",
 *               "page": 1,
 *               "results_per_page": 20
 *           }
 *    ),
 *
 * @OA\Schema(
 *   schema="ContentSearchRequestDestination",
 *   title="Content Search Request Destination",
 *   description="Schema Content Search Request Destination",
 *   type="object",
 *   required={"query"},
 *
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
 *     example=508
 *   ),
 *   @OA\Property(
 *     property="rating",
 *     type="integer",
 *     description="Rating of the hotel.",
 *     example=2
 *   ),
*     @OA\Property(
 *       property="consortia_affiliation",
 *       type="string",
 *       description="Consortium affiliation name. Valid values depend on system configuration.",
 *       example="Virtuoso"
 *     ),
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
 *
 * @OA\Examples(
 *     example="ContentSearchRequestDestination",
 *     summary="Content Search by Destination",
 *     value=
 *        {
 *            "type": "hotel",
 *            "destination": 508,
 *            "rating": 2,
 *            "consortia_affiliation": "Virtuoso",
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
 *
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
 *    @OA\Property(
 *       property="consortia_affiliation",
 *       type="string",
 *       description="Consortium affiliation name. Valid values depend on system configuration.",
 *       example="Virtuoso"
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
 *
 * @OA\Examples(
 *     example="ContentSearchRequestSupplierHotelName",
 *     summary="Content Search by Supplier and Hotel Name",
 *     value=
 *        {
 *            "type": "hotel",
 *            "supplier": "Expedia",
 *            "destination": 508,
 *            "rating": 3.5,
 *            "consortia_affiliation": "Virtuoso",
 *            "page": 1,
 *            "results_per_page": 20
 * }
 * ),
 *
 * @OA\Schema(
 *   schema="ContentSearchRequestCoordinates",
 *   title="Content Search Request Coordinates",
 *   description="Schema Content Search Request Coordinates",
 *   type="object",
 *   required={"type", "latitude", "longitude", "radius"},
 *
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
 *      example=21.13
 *    ),
 *    @OA\Property(
 *      property="longitude",
 *      type="number",
 *      format="float",
 *      description="Longitude of the center point.",
 *      example=-86.81
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
 *     @OA\Property(
 *       property="consortia_affiliation",
 *       type="string",
 *       description="Consortium affiliation name. Valid values depend on system configuration.",
 *       example="Virtuoso"
 *     ),
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
 *
 * @OA\Examples(
 *     example="ContentSearchRequestCoordinates",
 *     summary="Content Search by Coordinates",
 *     value=
 *        {
 *            "type": "hotel",
 *            "latitude": 21.13,
 *            "longitude": -86.81,
 *            "radius": 20,
 *            "rating": 1,
 *            "consortia_affiliation": "Virtuoso",
 *            "page": 1,
 *            "results_per_page": 20
 *        }
 * )
 * @OA\Examples(
 *      example="ContentSearchWithoutFilterAmenities",
 *      summary="Content Search without Filter Amenities",
 *      value=
 *         {
 *             "type": "hotel",
 *             "supplier":"HBSI",
 *             "giata_ids": {
 *                  26319691
 *              },
 *         }
 *  )
 *
 * @OA\Examples(
 *       example="ContentSearchWithFilterAmenities",
 *       summary="Content Search with Filter Amenities",
 *       value=
 *          {
 *              "type": "hotel",
 *              "supplier":"HBSI",
 *              "giata_ids": {
 *                   26319691
 *               },
 *              "consortia_affiliation": "Virtuoso",
 *          }
 *   )
 */
class ContentSearchRequest {}
