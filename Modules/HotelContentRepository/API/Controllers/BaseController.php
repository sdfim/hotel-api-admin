<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\API\BaseController as MainController;

/**
 * @OA\Info(
 *    title="Supplier Repository API Documentation",
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
 *      name="Hotel | Hotels",
 *      description="API Endpoints for Hotels"
 * ),
 * @OA\Tag(
 *      name="Hotel | Rooms",
 *      description="API Endpoints for Rooms"
 *  ),
 * @OA\Tag(
 *      name="Hotel | Website Search Generation",
 *      description="API Endpoints for Website Search Generation"
 *  ),
 *
 * @OA\Tag(
 *        name="Vendor | Vendors",
 *        description="API Endpoints for Vendors"
 *   ),
 *
 * @OA\Tag(
 *       name="Product | Products",
 *       description="API Endpoints for Products"
 *  ),
 * @OA\Tag(
 *       name="Product | Key Mappings",
 *       description="API Endpoints for Key Mappings"
 *   ),
 * @OA\Tag(
 *       name="Product | Attributes",
 *       description="API Endpoints for Attributes"
 *   ),
 * @OA\Tag(
 *       name="Product | Affiliations",
 *       description="API Endpoints for Affiliations"
 *   ),
 * @OA\Tag(
 *        name="Product | Fees and Taxes",
 *        description="API Endpoints for Fees and Taxes"
 *    ),
 * @OA\Tag(
 *        name="Product | Descriptive Content",
 *        description="API Endpoints for Descriptive Content"
 *    ),
 * @OA\Tag(
 *         name="Product | Informational Service",
 *         description="API Endpoints for Informational Service"
 *     ),
 * @OA\Tag(
 *          name="Product | Promotions",
 *          description="API Endpoints for Promotions"
 *      ),
 * @OA\Tag(
 *          name="Product | Deposit Information",
 *          description="API Endpoints for Deposit Information"
 *      ),
 * @OA\Tag(
 *          name="Product | Cancellation Policy",
 *          description="API Endpoints for Cancellation Policy"
 *      ),
 * @OA\Tag(
 *          name="Product | Contact Information",
 *          description="API Endpoints for Contact Information"
 *      ),
 * @OA\Tag(
 *     name="Product | Travel Agency Commissions",
 *     description="API Endpoints for Travel Agency Commissions"
 *      ),
 * @OA\Tag(
 *     name="Images",
 *     description="API Endpoints for Images"
 *      ),
 * @OA\Tag(
 *     name="Image Galleries",
 *     description="API Endpoints for Image Galleries"
 *      ),
 */
class BaseController extends MainController
{}
