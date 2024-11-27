<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingBookRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Booking API | Booking"},
     *   path="/api/booking/book",
     *   summary="Create a new booking for a service or event",
     *   description="Create a new booking for a service or event. Use this endpoint to make reservations.",
     *
     *    @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="To retrieve the **booking_id**, you need to execute a **'/api/booking/add-item'** request. <br>
     *      In the response object for each rate is a **booking_id** property.",
     *   ),
     *
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingBookRequest",
     *       examples={
     *           "example1": @OA\Schema(ref="#/components/examples/BookingBookRequest", example="BookingBookRequest"),
     *           "example2": @OA\Schema(ref="#/components/examples/BookingBookRequestExpedia", example="BookingBookRequestExpedia"),
     *       },
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingBookResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BookingBookResponse", example="BookingBookResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingBookResponseErrorItem",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BookingBookResponseErrorItem", example="BookingBookResponseErrorItem"),
     *       "example2": @OA\Schema(ref="#/components/examples/BookingBookResponseErrorBooked", example="BookingBookResponseErrorBooked"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
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

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
