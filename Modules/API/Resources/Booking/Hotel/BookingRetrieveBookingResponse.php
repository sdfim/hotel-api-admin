<?php

namespace Modules\API\Resources\Booking\Hotel;

/**
 * @OA\Schema(
 *   schema="BookingRetrieveBookingResponse",
 *   title="Booking Retrieve Booking Response",
 *   description="Schema Booking Retrieve Booking Response",
 *   type="object",
 *     required={"success", "data"},
 *     @OA\Property(
 *     property="success",
 *     type="boolean",
 *     description="Success (e.g., 'true').",
 *     example="true"
 *    ),
 *     @OA\Property(
 *     property="data",
 *     type="object",
 *     required={"result"},
 *     @OA\Property(
 *     property="result",
 *     type="array",
 *     @OA\Items(
 *     type="object",
 *     required={"status", "booking_id", "booking_item", "supplier", "hotel_name", "rooms", "cancellation_terms", "rate", "total_price", "total_tax", "total_fees", "total_net", "markup", "currency", "per_night_breakdown", "board_basis", "supplier_book_id", "billing_contact", "billing_email", "billing_phone", "query"},
 *     @OA\Property(
 *     property="status",
 *     type="string",
 *     description="Status (e.g., 'booked').",
 *     example="booked"
 *   ),
 *     @OA\Property(
 *     property="booking_id",
 *     type="string",
 *     description="Booking ID (e.g., '649b72dd-4b18-46fd-89d7-681b190df32d').",
 *     example="649b72dd-4b18-46fd-89d7-681b190df32d"
 *  ),
 *     @OA\Property(
 *     property="booking_item",
 *     type="string",
 *     description="Booking Item (e.g., '925dc8ff-0456-439a-b5d0-549a3c360156').",
 *     example="925dc8ff-0456-439a-b5d0-549a3c360156"
 * ),
 *     @OA\Property(
 *     property="supplier",
 *     type="string",
 *     description="Supplier (e.g., 'Expedia').",
 *     example="Expedia"
 * ),
 *     @OA\Property(property="hotel_name", type="string",description="Hotel Name (e.g., 'Majestic Hotel-Spa Paris (94423175)').",
 *     example="Majestic Hotel-Spa Paris (94423175)"
 * ),
 *     @OA\Property(
 *     property="rooms",
 *     type="array",
 *     @OA\Items(
 *     type="object",
 *     required={"checkin", "checkout", "number_of_adults", "given_name", "family_name", "room_name", "room_type"},
 *     @OA\Property(
 *     property="checkin",
 *     type="string",
 *     description="Checkin (e.g., '2023-12-14').",
 *     example="2023-12-14"
 * ),
 *     @OA\Property(
 *     property="checkout",
 *     type="string",
 *     description="Checkout (e.g., '2023-12-16').",
 *     example="2023-12-16"
 * ),
 *     @OA\Property(
 *     property="number_of_adults",
 *     type="integer",
 *     description="Number Of Adults (e.g., '3').",
 *     example="3"
 * ),
 *     @OA\Property(
 *     property="given_name",
 *     type="string",
 *     description="Given Name (e.g., 'Laurianne').",
 *     example="Laurianne"
 * ),
 *     @OA\Property(
 *     property="family_name",
 *     type="string",
 *     description="Family Name (e.g., 'Bernier').",
 *     example="Bernier"
 * ),
 *     @OA\Property(
 *     property="room_name",
 *     type="string",
 *     description="Room Name (e.g., 'Suite Prestige').",
 *     example="Suite Prestige"
 * ),
 *     @OA\Property(
 *     property="room_type",
 *     type="string",
 *     description="Room Type (e.g., '').",
 *     example=""
 * )
 * )
 * ),
 *     @OA\Property(
 *     property="cancellation_terms",
 *     type="string",
 *     description="Cancellation Terms (e.g., '').",
 *     example=""
 * ),
 *     @OA\Property(
 *     property="rate",
 *     type="string",
 *     description="Rate (e.g., '240031839').",
 *     example="240031839"
 * ),
 *     @OA\Property(
 *     property="total_price",
 *     type="integer",
 *     description="Total Price (e.g., '632192').",
 *     example="632192"
 * ),
 *     @OA\Property(
 *     property="total_tax",
 *     type="integer",
 *     description="Total Tax (e.g., '57472').",
 *     example="57472"
 * ),
 *     @OA\Property(
 *     property="total_fees",
 *     type="number",
 *     description="Total Fees (e.g., '33.04').",
 *     example="33.04"
 * ),
 *     @OA\Property(
 *     property="total_net",
 *     type="integer",
 *     description="Total Net (e.g., '574720').",
 *     example="574720"
 * ),
 *     @OA\Property(
 *     property="markup",
 *     type="integer",
 *     description="Markup (e.g., '0').",
 *     example="0"
 * ),
 *     @OA\Property(
 *     property="currency",
 *     type="string",
 *     description="Currency (e.g., 'JPY').",
 *     example="JPY"
 * ),
 *     @OA\Property(
 *     property="per_night_breakdown",
 *     type="integer",
 *     description="Per Night Breakdown (e.g., '316096').",
 *     example="316096"
 * ),
 *     @OA\Property(
 *     property="board_basis",
 *     type="string",
 *     description="Board Basis (e.g., '').",
 *     example=""
 * ),
 *     @OA\Property(
 *     property="supplier_book_id",
 *     type="string",
 *     description="Supplier Book ID (e.g., '7588412814912').",
 *     example="7588412814912"
 * ),
 *     @OA\Property(
 *     property="billing_contact",
 *     type="object",
 *     required={"given_name", "family_name", "address"},
 *     @OA\Property(
 *     property="given_name",
 *     type="string",
 *     description="Given Name (e.g., 'Norma').",
 *     example="Norma"
 * ),
 *     @OA\Property(
 *     property="family_name",
 *     type="string",
 *     description="Family Name (e.g., 'McDermott').",
 *     example="McDermott"
 *
 *     ),
 *     @OA\Property(
 *     property="address",
 *     type="object",
 *     required={"line_1", "city", "state_province_code", "postal_code", "country_code"},
 *     @OA\Property(
 *     property="line_1",
 *     type="string",
 *     description="Line 1 (e.g., '96107 Hassie Green').",
 *     example="96107 Hassie Green"
 * ),
 *     @OA\Property(
 *     property="city",
 *     type="string",
 *     description="City (e.g., 'Ceasarchester').",
 *     example="Ceasarchester"
 * ),
 *     @OA\Property(
 *     property="state_province_code",
 *     type="string",
 *     description="State Province Code (e.g., 'IA').",
 *     example="IA"
 * ),
 *     @OA\Property(
 *     property="postal_code",
 *     type="string",
 *     description="Postal Code (e.g., '08042').",
 *     example="08042"
 * ),
 *     @OA\Property(
 *     property="country_code",
 *     type="string",
 *     description="Country Code (e.g., 'AD').",
 *     example="AD"
 * )
 * )
 * ),
 *     @OA\Property(
 *     property="billing_email",
 *     type="string",
 *     description="Billing Email (e.g., '').",
 *     example=""
 * ),
 *     @OA\Property(
 *     property="billing_phone",
 *     type="object",
 *     required={"country_code", "area_code", "number"},
 *     @OA\Property(
 *     property="country_code",
 *     type="string",
 *     description="Country Code (e.g., '1').",
 *     example="1"
 * ),
 *     @OA\Property(
 *     property="area_code",
 *     type="string",
 *     description="Area Code (e.g., '487').",
 *     example="487"
 * ),
 *     @OA\Property(
 *     property="number",
 *     type="string",
 *     description="Number (e.g., '5550077').",
 *     example="5550077"
 * )
 * ),
 *     @OA\Property(
 *
 *     property="query",
 *     type="object",
 *     required={"type", "rating", "checkin", "checkout", "currency", "occupancy", "destination"},
 *     @OA\Property(
 *     property="type",
 *     type="string",
 *     description="Type (e.g., 'hotel').",
 *     example="hotel"
 * ),
 *     @OA\Property(
 *     property="rating",
 *     type="integer",
 *     description="Rating (e.g., '5').",
 *     example="5"
 * ),
 *     @OA\Property(
 *     property="checkin",
 *     type="string",
 *     description="Checkin (e.g., '2023-12-14').",
 *     example="2023-12-14"
 * ),
 *     @OA\Property(
 *     property="checkout",
 *     type="string",
 *     description="Checkout (e.g., '2023-12-16').",
 *     example="2023-12-16"
 * ),
 *     @OA\Property(
 *     property="currency",
 *     type="string",
 *     description="Currency (e.g., 'JPY').",
 *     example="JPY"
 * ),
 *     @OA\Property(
 *     property="occupancy",
 *     type="array",
 *     @OA\Items(
 *     type="object",
 *     required={"adults"},
 *     @OA\Property(
 *     property="adults",
 *     type="integer",
 *     description="Adults (e.g., '3').",
 *     example="3"
 * )
 * )
 * ),
 *     @OA\Property(
 *     property="destination",
 *     type="integer",
 *     description="Destination (e.g., '93').",
 *     example="93"
 * )
 * )
 * )
 * )
 * )
 * ),
 * @OA\Property(
 *     property="message",
 *     type="string",
 *     description="Message (e.g., 'success').",
 *     example="success"
 * )
 * )
 * ),
 * @OA\Examples(
 *     example="BookingRetrieveBookingResponse",
 *     summary="Example Booking Retrieve Booking Response",
 *     value=
 *        {
 * "success": true,
 * "data": {
 * "result": {
 * {
 * "status": "booked",
 * "booking_id": "649b72dd-4b18-46fd-89d7-681b190df32d",
 * "booking_item": "925dc8ff-0456-439a-b5d0-549a3c360156",
 * "supplier": "Expedia",
 * "hotel_name": "Majestic Hotel-Spa Paris (94423175)",
 * "rooms": {
 * {
 * "checkin": "2023-12-14",
 * "checkout": "2023-12-16",
 * "number_of_adults": 3,
 * "given_name": "Laurianne",
 * "family_name": "Bernier",
 * "room_name": "Suite Prestige",
 * "room_type": ""
 * },
 * {
 * "checkin": "2023-12-14",
 * "checkout": "2023-12-16",
 * "number_of_adults": 1,
 * "given_name": "Kacey",
 * "family_name": "Rath",
 * "room_name": "Suite Prestige",
 * "room_type": ""
 * }
 * },
 * "cancellation_terms": "",
 * "rate": "240031839",
 * "total_price": 632192,
 * "total_tax": 57472,
 * "total_fees": 33.04,
 * "total_net": 574720,
 * "markup": 0,
 * "currency": "JPY",
 * "per_night_breakdown": 316096,
 * "board_basis": "",
 * "supplier_book_id": "7588412814912",
 * "billing_contact": {
 * "given_name": "Norma",
 * "family_name": "McDermott",
 * "address": {
 * "line_1": "96107 Hassie Green",
 * "city": "Ceasarchester",
 * "state_province_code": "IA",
 * "postal_code": "08042",
 * "country_code": "AD"
 * }
 * },
 * "billing_email": "torrey.hodkiewicz@gmail.com",
 * "billing_phone": {
 * "country_code": "1",
 * "area_code": "487",
 * "number": "5550077"
 * },
 * "query": {
 * "type": "hotel",
 * "rating": 5,
 * "checkin": "2023-12-14",
 * "checkout": "2023-12-16",
 * "currency": "JPY",
 * "occupancy": {
 * {
 * "adults": 3
 * },
 * {
 * "adults": 1
 * }
 * },
 * "destination": 93
 * }
 * },
 * {
 * "status": "booked",
 * "booking_id": "649b72dd-4b18-46fd-89d7-681b190df32d",
 * "booking_item": "e54b926b-ab8e-4b1f-9a02-65f687de5b8b",
 * "supplier": "Expedia",
 * "hotel_name": "Beverly Wilshire,  A Four Seasons Hotel (63898582)",
 * "rooms": {
 * {
 * "checkin": "2023-12-14",
 * "checkout": "2023-12-17",
 * "number_of_adults": 1,
 * "given_name": "Conrad",
 * "family_name": "Daniel",
 * "room_name": "Studio Suite, Accessible (Bw King)",
 * "room_type": ""
 * }
 * },
 * "cancellation_terms": "",
 * "rate": "206191181",
 * "total_price": 521451,
 * "total_tax": 69567,
 * "total_fees": 0,
 * "total_net": 451884,
 * "markup": 0,
 * "currency": "JPY",
 * "per_night_breakdown": 173817,
 * "board_basis": "",
 * "supplier_book_id": "7992195226011",
 * "billing_contact": {
 * "given_name": "Norma",
 * "family_name": "McDermott",
 * "address": {
 * "line_1": "96107 Hassie Green",
 * "city": "Ceasarchester",
 * "state_province_code": "IA",
 * "postal_code": "08042",
 * "country_code": "AD"
 * }
 * },
 * "billing_email": "torrey.hodkiewicz@gmail.com",
 * "billing_phone": {
 * "country_code": "1",
 * "area_code": "487",
 * "number": "5550077"
 * },
 * "query": {
 * "type": "hotel",
 * "rating": 5,
 * "checkin": "2023-12-14",
 * "checkout": "2023-12-17",
 * "currency": "JPY",
 * "occupancy": {
 * {
 * "adults": 1,
 * "children": 1,
 * "children_ages": {
 * 6
 * }
 * }
 * },
 * "destination": 960
 * }
 * }
 * }
 * },
 * "message": "success"
 * }
 * )
 */
class BookingRetrieveBookingResponse
{

}
