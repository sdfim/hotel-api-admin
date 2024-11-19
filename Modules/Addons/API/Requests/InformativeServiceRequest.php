<?php

namespace Modules\Addons\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class InformativeServiceRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Booking API | Insurance and Informational Services"},
     *   path="/api/informative-service/attach",
     *   summary="Attach informative services",
     *   description="Attach informative services to a booking item.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"booking_item", "services"},
     *       @OA\Property(property="booking_item", type="string", example="123e4567-e89b-12d3-a456-426614174001"),
     *       @OA\Property(
     *         property="services",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           required={"service_id", "cost"},
     *           @OA\Property(property="service_id", type="integer", example=1),
     *           @OA\Property(property="cost", type="number", format="float", example=100.0)
     *         )
     *       ),
     *       @OA\Examples(
     *          example="example1",
     *          summary="Example with service_id",
     *          value={
     *              "booking_item": "123e4567-e89b-12d3-a456-426614174001",
     *              "services": {
     *                  {"service_id": 1, "cost": 100.0}
     *              }
     *          }
     *       ),
     *       @OA\Examples(
     *          example="example2",
     *          summary="Example with service_name",
     *          value={
     *              "booking_item": "123e4567-e89b-12d3-a456-426614174001",
     *              "services": {
     *                  {"service_name": "ServiceName", "cost": 100.0}
     *              }
     *          }
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Services attached successfully"
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
     *   tags={"Booking API | Insurance and Informational Services"},
     *   path="/api/informative-service/detach",
     *   summary="Detach informative services",
     *   description="Detach informative services from a booking item.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"booking_item", "services"},
     *       @OA\Property(property="booking_item", type="string", example="123e4567-e89b-12d3-a456-426614174001"),
     *       @OA\Property(
     *         property="services",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           required={"service_id"},
     *           @OA\Property(property="service_id", type="integer", example=1)
     *         )
     *       ),
     *       @OA\Examples(
     *          example="example1",
     *          summary="Example with service_id",
     *          value={
     *              "booking_item": "123e4567-e89b-12d3-a456-426614174001",
     *              "services": {
     *                  {"service_id": 1}
     *              }
     *          }
     *       ),
     *       @OA\Examples(
     *          example="example2",
     *          summary="Example with service_name",
     *          value={
     *              "booking_item": "123e4567-e89b-12d3-a456-426614174001",
     *              "services": {
     *                  {"service_name": "ServiceName"}
     *              }
     *          }
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Services detached successfully"
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
     * @OA\Get(
     *   tags={"Booking API | Insurance and Informational Services"},
     *   path="/api/informative-service/retrieve",
     *   summary="Retrieve informative services",
     *   description="Retrieve informative services for a booking item.",
     *   @OA\Parameter(
     *     name="booking_item",
     *     in="query",
     *     required=true,
     *     description="Booking Item",
     *     @OA\Schema(
     *       type="string",
     *       example="123e4567-e89b-12d3-a456-426614174001"
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Services retrieved successfully"
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
     */



    public function rules(): array
    {
        return [
            'booking_item' => 'required|string|exists:api_booking_items,booking_item',
            'services' => 'required|array',
            'services.*.service_id' => 'required_without:services.*.service_name|integer|exists:config_service_types,id',
            'services.*.service_name' => 'required_without:services.*.service_id|string|exists:config_service_types,name',
            'services.*.cost' => 'required|numeric',
        ];
    }
}
