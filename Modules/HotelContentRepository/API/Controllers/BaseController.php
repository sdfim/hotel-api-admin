<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\API\BaseController as MainController;

/**
 * @OA\Info(
 *    title="Contenr Repository API Documentation",
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
 *      description="API Endpoints for Hotels"
 * ),
 * @OA\Tag(
 *      name="Rooms",
 *      description="API Endpoints for Rooms"
 *  ),
 * @OA\Tag(
 *       name="Key Mapping Owners",
 *       description="API Endpoints for Key Mapping Owners"
 *   ),
 * @OA\Tag(
 *       name="Key Mappings",
 *       description="API Endpoints for Key Mappings"
 *   ),
 * @OA\Tag(
 *       name="Attributes",
 *       description="API Endpoints for Attributes"
 *   ),
 * @OA\Tag(
 *       name="Affiliations",
 *       description="API Endpoints for Affiliations"
 *   ),
 * @OA\Tag(
 *        name="Fee and Tax",
 *        description="API Endpoints for Fee and Tax"
 *    ),
 * @OA\Tag(
 *        name="Descriptive Content Section",
 *        description="API Endpoints for Descriptive Content Section"
 *    ),
 * @OA\Tag(
 *        name="Descriptive Content",
 *        description="API Endpoints for Descriptive Content"
 *    ),
 * @OA\Tag(
 *     name="Insurance API",
 *     description="API Endpoints for Insurance"
 * )
 */
class BaseController extends MainController
{}
