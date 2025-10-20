<?php

namespace Modules\API\Requests;

use Illuminate\Validation\Validator;
use Modules\API\Requests\Traits\ValidatesApiClient;
use Modules\API\Validate\ApiRequest;

class BookingBookRequest extends ApiRequest
{
    use ValidatesApiClient;

    /**
     * @OA\Post(
     *   tags={"Booking API | Booking"},
     *   path="/api/booking/book",
     *   summary="Create a new booking for a service or event",
     *   description="Create a new booking for a service or event. Use this endpoint to make reservations.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *     description="Request payload. At least one of api_client.id or api_client.email is required.",
     *
     *     @OA\MediaType(
     *       mediaType="application/json",
     *
     *       @OA\Schema(
     *         type="object",
     *         required={"amount_pay","booking_contact"},
     *
     *         @OA\Property(
     *           property="api_client",
     *           type="object",
     *           description="Optional identification of API client (id or email or both)",
     *           anyOf={
     *
     *             @OA\Schema(required={"id"}),
     *             @OA\Schema(required={"email"})
     *           },
     *
     *           @OA\Property(property="id", type="integer", example=3),
     *           @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         ),
     *         @OA\Property(property="amount_pay", type="string", enum={"Deposit","Full Payment"}, example="Deposit"),
     *         @OA\Property(property="travel_agency_identifier", type="string", description="IATA/ARC or internal 3-letter code", example="ABC"),
     *         @OA\Property(
     *           property="booking_contact",
     *           type="object",
     *           required={"first_name","last_name","email","phone","address"},
     *           @OA\Property(property="first_name", type="string", example="John"),
     *           @OA\Property(property="last_name", type="string", example="Doe"),
     *           @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *           @OA\Property(
     *             property="phone",
     *             type="object",
     *             required={"country_code","area_code","number"},
     *             @OA\Property(property="country_code", type="integer", example=380, description="E.164 country code"),
     *             @OA\Property(property="area_code", type="integer", example=44),
     *             @OA\Property(property="number", type="string", example="1234567")
     *           ),
     *           @OA\Property(
     *             property="address",
     *             type="object",
     *             required={"line_1","city","state_province_code","postal_code","country_code"},
     *             @OA\Property(property="line_1", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="Kyiv"),
     *             @OA\Property(property="state_province_code", type="string", example="30"),
     *             @OA\Property(property="postal_code", type="string", example="01001"),
     *             @OA\Property(property="country_code", type="string", example="UA")
     *           )
     *         ),
     *         @OA\Property(
     *           property="credit_cards",
     *           type="array",
     *
     *           @OA\Items(
     *             type="object",
     *             required={"booking_item","credit_card"},
     *
     *             @OA\Property(property="booking_item", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(
     *               property="credit_card",
     *               type="object",
     *               required={"name_card","number","card_type","expiry_date","cvv"},
     *               @OA\Property(property="name_card", type="string", example="John Doe"),
     *               @OA\Property(property="number", type="string", example="4111111111111111"),
     *               @OA\Property(property="card_type", type="string", enum={"MSC","VISA","AMEX","DIS"}, example="VISA"),
     *               @OA\Property(property="expiry_date", type="string", example="12/2030", description="Format m/Y"),
     *               @OA\Property(property="cvv", type="string", example="123"),
     *               @OA\Property(property="billing_address", type="string", nullable=true, example="123 Main St, Kyiv")
     *             )
     *           )
     *         ),
     *         @OA\Property(
     *           property="special_requests",
     *           type="array",
     *
     *           @OA\Items(
     *             type="object",
     *
     *             @OA\Property(property="booking_item", type="string", nullable=true, example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="room", type="integer", nullable=true, example=1, minimum=1, maximum=5),
     *             @OA\Property(property="special_request", type="string", nullable=true, example="Late check-in")
     *           )
     *         ),
     *         @OA\Property(
     *           property="comments",
     *           type="array",
     *
     *           @OA\Items(
     *             type="object",
     *             required={"booking_item","room","comment"},
     *
     *             @OA\Property(property="booking_item", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="room", type="integer", example=1, minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string", example="Customer prefers quiet room")
     *           )
     *         )
     *       ),
     *
     *       @OA\Examples(
     *         example="Example Booking Book with ID",
     *         summary="Example Booking Book Request with Client ID",
     *         value={
     *           "api_client"={"id"=5},
     *           "booking_id": "c9fb96cb-39d1-4ace-8d53-54cee94cfae3",
     *           "amount_pay"="Deposit",
     *           "booking_contact"={
     *             "first_name"="John",
     *             "last_name"="Doe",
     *             "email"="john.doe@example.com",
     *             "phone"={"country_code"=380,"area_code"=44,"number"="1234567"},
     *             "address"={"line_1"="123 Main St","city"="Kyiv","state_province_code"="30","postal_code"="01001","country_code"="UA"}
     *           }
     *         }
     *       ),
     *       @OA\Examples(
     *         example="Example Booking Book Request special_requests and comments",
     *         summary="Example Booking Book Request with special_requests and comments",
     *         value={
     *           "api_client"={"email"="user@example.com"},
     *           "amount_pay": "Deposit",
     *           "booking_id": "c9fb96cb-39d1-4ace-8d53-54cee94cfae3",
     *           "booking_contact"={
     *             "first_name"="Jane",
     *             "last_name"="Roe",
     *             "email"="jane.roe@example.com",
     *             "phone"={"country_code"=380,"area_code"=44,"number"="9876543"},
     *             "address"={"line_1"="Velyka Vasylkivska 1","city"="Kyiv","state_province_code"="30","postal_code"="01004","country_code"="UA"}
     *           },
     *           "special_requests"={
     *             {
     *               "booking_item"="550e8400-e29b-41d4-a716-446655440000",
     *               "room"=1,
     *               "special_request"="High floor"
     *             }
     *           },
     *           "comments"={
     *             {
     *               "booking_item"="550e8400-e29b-41d4-a716-446655440000",
     *               "room"=1,
     *               "comment"="Customer prefers quiet room"
     *             }
     *           }
     *         }
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/BookingBookResponse")),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request - Validation errors",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="errors",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="field", type="string", example="booking_contact.email"),
     *           @OA\Property(property="message", type="string", example="The booking_contact.email field is required.")
     *         )
     *       ),
     *       @OA\Property(property="message", type="string", example="Validation failed."),
     *       @OA\Examples(
     *         example="missing_email",
     *         summary="Missing email",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.email", "message": "The booking_contact.email field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="invalid_phone",
     *         summary="Invalid phone",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.phone", "message": "The booking_contact.phone field must be a valid phone number."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_date",
     *         summary="Missing booking date",
     *         value={
     *           "errors": {
     *             {"field": "booking_date", "message": "The booking_date field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_id",
     *         summary="Missing booking_id",
     *         value={
     *           "errors": {
     *             {"field": "booking_id", "message": "The booking_id field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_amount_pay",
     *         summary="Missing amount_pay",
     *         value={
     *           "errors": {
     *             {"field": "amount_pay", "message": "The amount_pay field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_first_name",
     *         summary="Missing booking_contact.first_name",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.first_name", "message": "The booking_contact.first_name field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_last_name",
     *         summary="Missing booking_contact.last_name",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.last_name", "message": "The booking_contact.last_name field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_email",
     *         summary="Missing booking_contact.email",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.email", "message": "The booking_contact.email field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_phone_country_code",
     *         summary="Missing booking_contact.phone.country_code",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.phone.country_code", "message": "The booking_contact.phone.country_code field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_phone_area_code",
     *         summary="Missing booking_contact.phone.area_code",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.phone.area_code", "message": "The booking_contact.phone.area_code field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_phone_number",
     *         summary="Missing booking_contact.phone.number",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.phone.number", "message": "The booking_contact.phone.number field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_address_line_1",
     *         summary="Missing booking_contact.address.line_1",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.address.line_1", "message": "The booking_contact.address.line_1 field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_address_city",
     *         summary="Missing booking_contact.address.city",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.address.city", "message": "The booking_contact.address.city field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_address_state_province_code",
     *         summary="Missing booking_contact.address.state_province_code",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.address.state_province_code", "message": "The booking_contact.address.state_province_code field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_address_postal_code",
     *         summary="Missing booking_contact.address.postal_code",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.address.postal_code", "message": "The booking_contact.address.postal_code field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="missing_booking_contact_address_country_code",
     *         summary="Missing booking_contact.address.country_code",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.address.country_code", "message": "The booking_contact.address.country_code field is required."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="invalid_booking_contact_phone_country_code",
     *         summary="Invalid booking_contact.phone.country_code",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.phone.country_code", "message": "The booking_contact.phone.country_code field must be one of the allowed country codes (e.g., 1 for USA, 44 for UK). Valid values are integers between 1 and 999."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="invalid_booking_contact_phone_area_code",
     *         summary="Invalid booking_contact.phone.area_code",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.phone.area_code", "message": "The booking_contact.phone.area_code field must be exactly 3 digits (e.g., 212 for New York)."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="invalid_booking_contact_phone_number",
     *         summary="Invalid booking_contact.phone.number",
     *         value={
     *           "errors": {
     *             {"field": "booking_contact.phone.number", "message": "The booking_contact.phone.number field must be between 3 and 10 digits (e.g., 1234567)."}
     *           },
     *           "message": "Validation failed."
     *         }
     *       ),
     *       @OA\Examples(
     *         example="valid_booking_contact_phone",
     *         summary="Valid booking_contact.phone example",
     *         value={
     *           "booking_contact": {
     *             "phone": {
     *               "country_code": 1,
     *               "area_code": 212,
     *               "number": "1234567"
     *             }
     *           }
     *         }
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/UnAuthenticatedResponse")),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function rules(): array
    {
        // List of country telephone codes
        $phoneCountryCodes = [1, 7, 20, 27, 30, 31, 32, 33, 34, 36, 39, 40, 41, 43, 44, 45, 46, 47, 48, 49, 51, 52, 53,
            54, 55, 56, 57, 58, 60, 61, 62, 63, 64, 65, 66, 81, 82, 84, 86, 90, 91, 92, 93, 94, 95, 98, 211, 212, 213,
            216, 218, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239,
            240, 241, 242, 243, 244, 245, 246, 248, 249, 250, 251, 252, 253, 254, 255, 256, 257, 258, 260, 261, 262, 263,
            264, 265, 266, 267, 268, 269, 290, 291, 297, 298, 299, 350, 351, 352, 353, 354, 355, 356, 357, 358, 359, 370,
            371, 372, 373, 374, 375, 376, 377, 378, 379, 380, 381, 382, 383, 385, 386, 387, 389, 420, 421, 423, 500, 501,
            502, 503, 504, 505, 506, 507, 508, 509, 590, 591, 592, 593, 594, 595, 596, 597, 598, 599, 670, 672, 673, 674,
            675, 676, 677, 678, 679, 680, 681, 682, 683, 685, 686, 687, 688, 689, 690, 691, 692, 800, 808, 850, 852, 853,
            855, 856, 870, 878, 880, 881, 882, 883, 886, 888, 960, 961, 962, 963, 964, 965, 966, 967, 968, 970, 971, 972,
            973, 974, 975, 976, 977, 979, 992, 993, 994, 995, 996, 998];

        $rules = [
            'booking_id' => 'required|size:36',
            // Optional API client identification (either id or email or both)
            'api_client.id' => 'nullable|integer',
            'api_client.email' => 'nullable|email:rfc,dns',
            'amount_pay' => 'required|string|in:Deposit,Full Payment',
            'travel_agency_identifier' => 'string|size:3',
            'booking_contact.first_name' => 'required|string',
            'booking_contact.last_name' => 'required|string',
            'booking_contact.email' => 'required|email:rfc,dns',
            'booking_contact.phone.country_code' => 'required|int|in:'.implode(',', $phoneCountryCodes),
            'booking_contact.phone.area_code' => 'required|int|digits:3',
            'booking_contact.phone.number' => 'required|numeric|digits_between:3,10',
            'booking_contact.address.line_1' => 'required|string|min:1|max:255',
            'booking_contact.address.city' => 'required|string|min:1|max:100',
            'booking_contact.address.state_province_code' => 'required|string',
            'booking_contact.address.postal_code' => 'required|string',
            'booking_contact.address.country_code' => 'required|string',
        ];

        if (request()->has('credit_cards')) {
            $rules['credit_cards'] = 'array';
            $rules['credit_cards.*.booking_item'] = 'required|size:36';
            $rules['credit_cards.*.credit_card.name_card'] = 'required|string|between:2,255';
            $rules['credit_cards.*.credit_card.number'] = 'required|int|digits_between:13,19';
            $rules['credit_cards.*.credit_card.card_type'] = 'required|string|in:MSC,VISA,AMEX,DIS';
            $rules['credit_cards.*.credit_card.expiry_date'] = 'required|date_format:m/Y|after_or_equal:today';
            $rules['credit_cards.*.credit_card.cvv'] = 'required|string|digits_between:3,4';
            $rules['credit_cards.*.credit_card.billing_address'] = 'nullable|string';
        }

        if (request()->has('special_requests')) {
            $rules['special_requests'] = 'array';
            $rules['special_requests.*.booking_item'] = 'nullable|size:36';
            $rules['special_requests.*.room'] = 'nullable|integer|between:1,5';
            $rules['special_requests.*.special_request'] = 'nullable|string|between:1,255';
        }

        if (request()->has('comments')) {
            $rules['comments'] = 'array';
            $rules['comments.*.booking_item'] = 'required|size:36';
            $rules['comments.*.room'] = 'required|integer|between:1,5';
            $rules['comments.*.comment'] = 'required|string|between:1,255';
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Read nested inputs safely
            $id = data_get($this->all(), 'api_client.id');
            $email = data_get($this->all(), 'api_client.email');

            $this->validateApiClient($v, $id, $email);
        });
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
