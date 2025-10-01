<?php

namespace Modules\API\Requests;

use Illuminate\Validation\Validator;
use Modules\API\Requests\Traits\ValidatesApiClient;
use Modules\API\Validate\ApiRequest;

class BookingListBookingsRequest extends ApiRequest
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
     *      @OA\Schema(type="string", enum={"hotel","flight","combo"}, example="hotel")
     *   ),
     *   @OA\Parameter(
     *      name="api_client_id",
     *      in="query",
     *      required=false,
     *      description="API client user ID. Either api_client_id or api_client_email must be provided.",
     *      @OA\Schema(type="integer", example=123)
     *   ),
     *   @OA\Parameter(
     *      name="api_client_email",
     *      in="query",
     *      required=false,
     *      description="API client email. Either api_client_id or api_client_email must be provided.",
     *      @OA\Schema(type="string", format="email", example="user@example.com")
     *   ),
     *   @OA\Parameter(
     *      name="force",
     *      in="query",
     *      required=false,
     *      description="If set to true, ignores filtering by API user and token.",
     *      @OA\Schema(type="boolean", example=true)
     *   ),
     *   @OA\Parameter(
     *      name="page",
     *      in="query",
     *      required=false,
     *      description="Page number for pagination (default: 1)",
     *      @OA\Schema(type="integer", minimum=1, example=1, default=1)
     *   ),
     *   @OA\Parameter(
     *      name="results_per_page",
     *      in="query",
     *      required=false,
     *      description="Number of results per page (default: 10)",
     *      @OA\Schema(type="integer", minimum=1, example=10, default=10)
     *   ),
     *   @OA\Parameter(
     *      name="booking_date_from",
     *      in="query",
     *      required=false,
     *      description="Filter bookings from this date (YYYY-MM-DD)",
     *      @OA\Schema(type="string", format="date", example="2025-09-01")
     *   ),
     *   @OA\Parameter(
     *      name="booking_date_to",
     *      in="query",
     *      required=false,
     *      description="Filter bookings up to this date (YYYY-MM-DD)",
     *      @OA\Schema(type="string", format="date", example="2025-09-30")
     *   ),
     *   @OA\Parameter(
     *      name="checkin_date_from",
     *      in="query",
     *      required=false,
     *      description="Filter bookings with check-in from this date (YYYY-MM-DD)",
     *      @OA\Schema(type="string", format="date", example="2025-09-01")
     *   ),
     *   @OA\Parameter(
     *      name="checkin_date_to",
     *      in="query",
     *      required=false,
     *      description="Filter bookings with check-in up to this date (YYYY-MM-DD)",
     *      @OA\Schema(type="string", format="date", example="2025-09-30")
     *   ),
     *   @OA\Response(response=200, description="OK",
     *   @OA\JsonContent(
     * example={
     * "success": true,
     * "data": {
     * "count": 1,
     * "result": {
     * {
     * "status": "booked",
     * "booking_id": "c8c3d2b5-4233-4b2a-bdf3-fd233d80a38b",
     * "booking_item": "786430a0-9e2e-4617-ae98-d3aec8505660",
     * "hotel_name": "The Bostonian Boston (69002077)",
     * "rooms": {
     * {
     * "checkin": "2025-09-20",
     * "checkout": "2025-09-25",
     * "number_of_adults": 2,
     * "given_name": "Cicero",
     * "family_name": "Ziemann",
     * "room_name": "Deluxe King",
     * "room_type": null,
     * "passengers": {
     * {
     * "adult": true,
     * "age": 33,
     * "email": "test@gmail.com",
     * "firstName": "Cicero",
     * "lastName": "Ziemann",
     * "phone": "5550077",
     * "primary": true
     * },
     * {
     * "adult": true,
     * "age": 37,
     * "email": "test@gmail.com",
     * "firstName": "Angelita",
     * "lastName": "Kris",
     * "phone": "5550077",
     * "primary": false
     * }
     * }
     * }
     * },
     * "cancellation_terms": {
     * {
     * "currency": "USD",
     * "timeZone": "America/New_York",
     * "timeZoneUTC": "-04:00",
     * "endWindowTime": "2025-09-20 15:00:00",
     * "startWindowTime": "2025-08-21 05:00:00",
     * "cancellationCharge": 1862.51
     * }
     * },
     * "rate": "",
     * "total_price": 1862.51,
     * "total_tax": 263.11,
     * "total_fees": 145.6,
     * "total_net": 1599.4,
     * "markup": 0,
     * "currency": "0",
     * "per_night_breakdown": 0,
     * "confirmation_numbers_list": {
     * {
     * "confirmation_number": "HT-MGRUTLQA2SAN",
     * "type": "HotelTrader",
     * "type_id": "HT"
     * }
     * },
     * "cancellation_number": null,
     * "board_basis": "Free Grab and Go Breakfast",
     * "supplier_book_id": "HT-MGRUTLQA2SAN",
     * "billing_contact": {
     * "city": "Sabinaborough",
     * "line_1": "682 Unique Springs",
     * "postal_code": "ymc",
     * "country_code": "CW",
     * "state_province_code": "NM"
     * },
     * "billing_email": "test@gmail.com",
     * "billing_phone": {
     * "number": "5550077",
     * "area_code": "487",
     * "country_code": "1"
     * },
     * "query": {
     * "search_id": "cb6a974a-7ba0-4559-8fe0-bb4f96d95342",
     * "amount_pay": "Deposit",
     * "api_client": {
     * "email": "test-api-user@terramare.com"
     * },
     * "booking_id": "c8c3d2b5-4233-4b2a-bdf3-fd233d80a38b",
     * "booking_item": "786430a0-9e2e-4617-ae98-d3aec8505660",
     * "credit_cards": {
     * {
     * "credit_card": {
     * "cvv": "123",
     * "number": 4001919257537193,
     * "card_type": "VISA",
     * "name_card": "Visa",
     * "expiry_date": "09/2026",
     * "billing_address": null
     * },
     * "booking_item": "786430a0-9e2e-4617-ae98-d3aec8505660"
     * }
     * },
     * "booking_contact": {
     * "email": "test@gmail.com",
     * "phone": {
     * "number": "5550077",
     * "area_code": "487",
     * "country_code": "1"
     * },
     * "address": {
     * "city": "Sabinaborough",
     * "line_1": "682 Unique Springs",
     * "postal_code": "ymc",
     * "country_code": "CW",
     * "state_province_code": "NM"
     * },
     * "last_name": "Stroman",
     * "first_name": "Therese"
     * }
     * }
     * }
     * }
     * },
     * "message": "success"
     * }
     * )
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated",
     *     @OA\JsonContent(ref="#/components/schemas/UnAuthenticatedResponse")
     *   ),
     *   @OA\Response(response=400, description="Bad Request",
     *     @OA\JsonContent(ref="#/components/schemas/BadRequestResponse")
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function rules(): array
    {
        return [
            'supplier' => 'nullable|string',
            'type' => 'required|string|in:hotel,flight,combo',
            'api_client.id' => 'nullable|integer',
            'api_client.email' => 'nullable|email',
            'page' => 'nullable|integer|min:1',
            'results_per_page' => 'nullable|integer|min:1',
            'booking_date_from' => 'nullable|date',
            'booking_date_to' => 'nullable|date',
            'checkin_date_from' => 'nullable|date',
            'checkin_date_to' => 'nullable|date',
            'force' => 'nullable|boolean',
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

            $dateFrom = $this->input('booking_date_from');
            $dateTo = $this->input('booking_date_to');
            if ($dateFrom && $dateTo) {
                try {
                    $from = \Carbon\Carbon::parse($dateFrom);
                    $to = \Carbon\Carbon::parse($dateTo);
                    if ($to->lt($from)) {
                        $v->errors()->add('booking_date_to', 'The booking_date_to must be after or equal to booking_date_from.');
                    }
                } catch (\Exception $e) {
                    $v->errors()->add('booking_date_from', 'Invalid date format.');
                }
            }

            $checkinFrom = $this->input('checkin_date_from');
            $checkinTo = $this->input('checkin_date_to');
            if ($checkinFrom || $checkinTo) {
                try {
                    if ($checkinFrom && $checkinTo) {
                        $from = \Carbon\Carbon::parse($checkinFrom);
                        $to = \Carbon\Carbon::parse($checkinTo);
                        if ($to->lt($from)) {
                            $v->errors()->add('checkin_date_to', 'The checkin_date_to must be after or equal to checkin_date_from.');
                        }
                    } elseif ($checkinFrom) {
                        $from = \Carbon\Carbon::parse($checkinFrom);
                    } elseif ($checkinTo) {
                        $to = \Carbon\Carbon::parse($checkinTo);
                    }
                } catch (\Exception $e) {
                    $v->errors()->add('checkin_date_from', 'Invalid date format.');
                }
            }
        });
    }
}
