<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\API\Validate\ApiRequest;

class VendorRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Vendor | Vendors"},
     *   path="/api/repo/vendors",
     *   summary="Get all vendors",
     *   description="Retrieve all vendor records with optional filters.",
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     *
     * @OA\Post(
     *   tags={"Vendor | Vendors"},
     *   path="/api/repo/vendors",
     *   summary="Create a new vendor",
     *   description="Create a new vendor entry.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"name", "address", "lat", "lng", "verified", "independent_flag"},
     *       @OA\Property(property="name", type="string", example="Example Vendor"),
     *       @OA\Property(property="address", type="string", example="123 Main St"),
     *       @OA\Property(property="lat", type="number", format="float", example=12.345678),
     *       @OA\Property(property="lng", type="number", format="float", example=98.765432),
     *       @OA\Property(property="verified", type="boolean", example=true),
     *       @OA\Property(property="independent_flag", type="boolean", example=false),
     *       @OA\Property(property="website", type="string", example="https://examplevendor.com")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     *
     * @OA\Get(
     *   tags={"Vendor | Vendors"},
     *   path="/api/repo/vendors/{id}",
     *   summary="Get vendor details",
     *   description="Retrieve details of a specific vendor.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the vendor",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
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
     *
     * @OA\Put(
     *   tags={"Vendor | Vendors"},
     *   path="/api/repo/vendors/{id}",
     *   summary="Update vendor details",
     *   description="Update details of a specific vendor.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the vendor",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"name", "address", "lat", "lng", "verified", "independent_flag"},
     *       @OA\Property(property="name", type="string", example="Example Vendor"),
     *       @OA\Property(property="address", type="string", example="123 Main St"),
     *       @OA\Property(property="lat", type="number", format="float", example=12.345678),
     *       @OA\Property(property="lng", type="number", format="float", example=98.765432),
     *       @OA\Property(property="verified", type="boolean", example=true),
     *       @OA\Property(property="independent_flag", type="boolean", example=false),
     *       @OA\Property(property="website", type="string", example="https://examplevendor.com")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
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
     *
     * @OA\Delete(
     *   tags={"Vendor | Vendors"},
     *   path="/api/repo/vendors/{id}",
     *   summary="Delete a vendor",
     *   description="Delete a specific vendor.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the vendor",
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *   @OA\Response(
     *     response=204,
     *     description="No Content"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
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
            'name' => 'required|string|max:255',
            'verified' => 'required|boolean',
            'address' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'website' => 'nullable|string|max:255',
            'independent_flag' => 'required|boolean',
        ];
    }
}
