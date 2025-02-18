<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;

class ContactInformationRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Product | Contact Information"},
     *   path="/api/repo/contact-information",
     *   summary="Get all contact information",
     *   description="Retrieve all contact information records with optional filters.",
     *
     *   @OA\Parameter(
     *     name="contactable_id",
     *     in="query",
     *     required=false,
     *     description="Filter by contactable ID",
     *
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="contactable_type",
     *     in="query",
     *     required=false,
     *     description="Filter by contactable type",
     *
     *     @OA\Schema(
     *       type="string",
     *       example="App\\Models\\Product"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="first_name",
     *     in="query",
     *     required=false,
     *     description="Filter by first name",
     *
     *     @OA\Schema(
     *       type="string",
     *       example="John"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="last_name",
     *     in="query",
     *     required=false,
     *     description="Filter by last name",
     *
     *     @OA\Schema(
     *       type="string",
     *       example="Doe"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="email",
     *     in="query",
     *     required=false,
     *     description="Filter by email",
     *
     *     @OA\Schema(
     *       type="string",
     *       example="john.doe@example.com"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="phone",
     *     in="query",
     *     required=false,
     *     description="Filter by phone",
     *
     *     @OA\Schema(
     *       type="string",
     *       example="+1234567890"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="contactInformations",
     *     in="query",
     *     required=false,
     *     description="Filter by contact information IDs",
     *
     *     @OA\Schema(
     *       type="array",
     *
     *       @OA\Items(type="integer", example=1)
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )

     *
     * @OA\Post(
     *   tags={"Product | Contact Information"},
     *   path="/api/repo/contact-information",
     *   summary="Create a new contact information",
     *   description="Create a new contact information entry.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"contactable_id", "contactable_type", "first_name", "last_name", "email", "phone", "job_title"},
     *
     *       @OA\Property(property="contactable_id", type="integer", example=1),
     *       @OA\Property(property="contactable_type", type="string", example="App\\Models\\Product"),
     *       @OA\Property(property="first_name", type="string", example="John"),
     *       @OA\Property(property="last_name", type="string", example="Doe"),
     *       @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *       @OA\Property(property="phone", type="string", example="+1234567890"),
     *       @OA\Property(property="job_title", type="string", example="Manager")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Created"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )

     *
     * @OA\Get(
     *   tags={"Product | Contact Information"},
     *   path="/api/repo/contact-information/{id}",
     *   summary="Get contact information details",
     *   description="Retrieve details of a specific contact information.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the contact information",
     *
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )

     *
     * @OA\Put(
     *   tags={"Product | Contact Information"},
     *   path="/api/repo/contact-information/{id}",
     *   summary="Update contact information details",
     *   description="Update details of a specific contact information.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the contact information",
     *
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"contactable_id", "contactable_type", "first_name", "last_name", "email", "phone", "job_title"},
     *
     *       @OA\Property(property="contactable_id", type="integer", example=1),
     *       @OA\Property(property="contactable_type", type="string", example="App\\Models\\Product"),
     *       @OA\Property(property="first_name", type="string", example="John"),
     *       @OA\Property(property="last_name", type="string", example="Doe"),
     *       @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *       @OA\Property(property="phone", type="string", example="+1234567890"),
     *       @OA\Property(property="job_title", type="string", example="Manager")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/NotFoundResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )

     *
     * @OA\Delete(
     *   tags={"Product | Contact Information"},
     *   path="/api/repo/contact-information/{id}",
     *   summary="Delete a contact information",
     *   description="Delete a specific contact information.",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of the contact information",
     *
     *     @OA\Schema(
     *       type="integer",
     *       example=1
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=204,
     *     description="No Content"
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *
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
            'contactable_id' => 'required|integer',
            'contactable_type' => 'required|string|max:191',
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'job_title' => 'required|string|max:191',
            'emails' => 'array',
            'emails.*.email' => 'required|string|max:191',
            'emails.*.contactInformations' => 'array',
            'emails.*.contactInformations.*.name' => 'required|string|max:20',
            'phones' => 'array',
            'phones.*.country_code' => 'required|string|max:20',
            'phones.*.area_code' => 'nullable|string|max:20',
            'phones.*.phone' => 'required|string|max:20',
            'phones.*.extension' => 'nullable|string|max:20',
            'phones.*.description' => 'nullable|string|max:255',
        ];
    }
}
