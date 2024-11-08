<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\API\BaseController as MainController;

/**
 * @OA\Info(
 *    title="UJVAPI Documentation",
 *    version="1.0.0"
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="authentication token",
 *     name="Token based Based",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="apiAuth",
 * )
 *
 * @OA\Tag(
 *      name="Hotels",
 *      description="API Endpoints forHotels"
 * ),
 * @OA\Tag(
 *      name="Rooms",
 *      description="API Endpoints forRooms"
 *  ),
 * @OA\Tag(
 *       name="Key Mapping Owners",
 *       description="API Endpoints forKey Mapping Owners"
 *   ),
 * @OA\Tag(
 *       name="Key Mappings",
 *       description="API Endpoints forKey Mappings"
 *   ),
 * @OA\Tag(
 *       name="Affiliations",
 *       description="API Endpoints forAffiliations"
 *   ),
 * @OA\Tag(
 *        name="Attributes",
 *        description="API Endpoints forAttributes"
 *    ),
 * @OA\Tag(
 *     name="Insurance API",
 *     description="API Endpoints for Insurance"
 * )
 */
class BaseController extends MainController
{}
