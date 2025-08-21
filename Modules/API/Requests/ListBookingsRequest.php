<?php

namespace Modules\API\Requests;

use Illuminate\Validation\Validator;
use Modules\API\Requests\Traits\ValidatesApiClient;
use Modules\API\Validate\ApiRequest;

class ListBookingsRequest extends ApiRequest
{
    use ValidatesApiClient;

    /**
     * @OA\Get(
     *   tags={"Booking API | Booking"},
     *   path="/api/booking/list-bookings",
     *   summary="Retrieve a list of all your booking reservations.",
     *   description="Retrieve a list of all your booking reservations. This endpoint provides an overview of your booking history and their current statuses.",
     *
     *   @OA\Parameter(
     *      name="type",
     *      in="query",
     *      required=true,
     *      description="Type of booking",
     *
     *      @OA\Schema(type="string", enum={"hotel","flight","combo"}, example="hotel")
     *   ),
     *
     *   @OA\Parameter(
     *      name="supplier",
     *      in="query",
     *      required=true,
     *      description="Supplier",
     *
     *      @OA\Schema(type="string", example="Expedia")
     *   ),
     *
     *   @OA\Parameter(
     *      name="api_client[id]",
     *      in="query",
     *      required=false,
     *      description="API client user ID",
     *
     *      @OA\Schema(type="integer", example=123)
     *   ),
     *
     *   @OA\Parameter(
     *      name="api_client[email]",
     *      in="query",
     *      required=false,
     *      description="API client email",
     *
     *      @OA\Schema(type="string", format="email", example="user@example.com")
     *   ),
     *
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthenticated",
     *
     *     @OA\JsonContent(ref="#/components/schemas/UnAuthenticatedResponse")
     *   ),
     *
     *   @OA\Response(response=400, description="Bad Request",
     *
     *     @OA\JsonContent(ref="#/components/schemas/BadRequestResponse")
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function rules(): array
    {
        return [
            'supplier' => 'required|string',
            'type' => 'required|string|in:hotel,flight,combo',
            'api_client.id' => 'nullable|integer',
            'api_client.email' => 'nullable|email',
        ];
    }

    /**
     * We bring aliases to a single form:
     * - client_id / api_client_id -> api_client.id
     * - client_email              -> api_client.email
     */
    protected function prepareForValidation(): void
    {
        $apiClient = (array) ($this->input('api_client') ?? []);

        // Support for flat aliases in queries: ?client_id=...&client_email=...
        if ($this->filled('client_id') && empty($apiClient['id'])) {
            $apiClient['id'] = $this->input('client_id');
        }

        if ($this->filled('client_email') && empty($apiClient['email'])) {
            $apiClient['email'] = $this->input('client_email');
        }

        $this->merge(['api_client' => $apiClient]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $id = data_get($this->all(), 'api_client.id');
            $email = data_get($this->all(), 'api_client.email');

            $this->validateApiClient($v, $id, $email);
        });
    }
}
