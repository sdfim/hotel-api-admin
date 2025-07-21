<?php

namespace Modules\HotelContentRepository\API\Swagger;

/**
 * @OA\Schema(
 *   schema="NotFoundResponse",
 *   type="object",
 *   required={"message"},
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     example="Resource not found"
 *   )
 * )
 *
 * @OA\Examples(
 *   example="NotFoundResponse",
 *   summary="Example of NotFoundResponse",
 *   value={
 *     "message": "Resource not found"
 *   }
 * )
 */
class NotFoundResponse
{
}
