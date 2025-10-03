<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingCheckQuoteRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * @OA\Post(
     *   tags={"Booking API | Quote"},
     *   path="/api/booking/check-quote",
     *   summary="Retrieve a specific booking quote by booking_item.",
     *   description="This endpoint provides information about actual availability and pricing for a specific booking quote (unbooked cart item) for the agent.",
     *   security={{ "apiAuth": {} }},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"booking_item"},
     *       @OA\Property(
     *         property="booking_item",
     *         type="string",
     *         format="uuid",
     *         example="123e4567-e89b-12d3-a456-426614174000"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful response with available endpoints for modifying the booking.",
     *     @OA\JsonContent(
     *       example={
     * "success": true,
     * "data": {
     * "check_quote_search_id": "5efc961e-1fde-4595-81de-db8858340a2a",
     * "comparison_of_amounts": {
     * "current_search_sums": {
     * "total_net": 9565.36,
     * "total_tax": 1454.84,
     * "total_fees": 367.2,
     * "total_price": 11020.2,
     * "markup": 0
     * },
     * "first_search_sums": {
     * "total_net": 9565.36,
     * "total_tax": 1454.84,
     * "total_fees": 367.2,
     * "total_price": 11020.2,
     * "markup": 0
     * },
     * "differences": {
     * "total_net": false,
     * "total_tax": false,
     * "total_fees": false,
     * "total_price": false,
     * "markup": false
     * },
     * "conclusion": "match"
     * },
     * "hotel_image": "http://localhost:8007/storage/products/Park Central Hotel New York.jpg",
     * "attributes": {
     * {
     * "name": "Elevator",
     * "category": "Elevator"
     * },
     * {
     * "name": "ATM/banking",
     * "category": "general"
     * },
     * {
     * "name": "Conference space",
     * "category": "general"
     * },
     * {
     * "name": "Bicycle rentals nearby",
     * "category": "general"
     * },
     * {
     * "name": "Hiking/biking trails nearby",
     * "category": "general"
     * },
     * {
     * "name": "Laundry facilities",
     * "category": "general"
     * },
     * {
     * "name": "Safe-deposit box at front desk",
     * "category": "general"
     * },
     * {
     * "name": "Free WiFi",
     * "category": "WiFi Included"
     * },
     * {
     * "name": "Multilingual staff",
     * "category": "general"
     * },
     * {
     * "name": "24-hour front desk",
     * "category": "general"
     * },
     * {
     * "name": "Business center",
     * "category": "Business"
     * },
     * {
     * "name": "Express check-out",
     * "category": "general"
     * },
     * {
     * "name": "Dry cleaning/laundry service",
     * "category": "general"
     * },
     * {
     * "name": "Smoke-free property",
     * "category": "general"
     * },
     * {
     * "name": "Number of restaurants - 1",
     * "category": "Restaurant"
     * },
     * {
     * "name": "24-hour business center",
     * "category": "general"
     * },
     * {
     * "name": "Snack bar/deli",
     * "category": "Restaurant"
     * },
     * {
     * "name": "Self parking (surcharge)",
     * "category": "Parking"
     * },
     * {
     * "name": "Computer station",
     * "category": "general"
     * },
     * {
     * "name": "Eco-friendly toiletries",
     * "category": "general"
     * },
     * {
     * "name": "Eco-friendly toiletries",
     * "category": "general"
     * },
     * {
     * "name": "Eco-friendly toiletries",
     * "category": "general"
     * }
     * },
     * "email_verification": "kslndr@gmail.com",
     * "check_quote_search_query": {
     * "type": "hotel",
     * "rating": 4,
     * "checkin": "2025-12-03",
     * "checkout": "2025-12-07",
     * "supplier": {
     * "HotelTrader"
     * },
     * "token_id": "Rx1rkXk0S9MomMpssi5D9T4kfUI5MwHxRZDSwYoHecbb4ace",
     * "giata_ids": {
     * "37305666"
     * },
     * "occupancy": {
     * {
     * "adults": 2
     * },
     * {
     * "adults": 1
     * }
     * },
     * "booking_item": "994059e0-c126-434d-b52d-70dd4103bc37",
     * "blueprint_exist": false
     * },
     * "giata_id": "37305666",
     * "booking_item": "a1a6065c-edca-48bf-9cf3-f27f19e20309",
     * "booking_id": "f0b388d5-8b0f-42f2-8569-122329388813",
     * "current_search": {
     * {
     * "unified_room_code": "external_PKS",
     * "giata_room_code": "277",
     * "giata_room_name": "",
     * "supplier_room_name": "Premier One Bedroom Suite",
     * "per_day_rate_breakdown": "",
     * "supplier_room_id": 1,
     * "distribution": false,
     * "query_package": "",
     * "room_type": "PKS",
     * "room_description": "",
     * "amenities": {},
     * "capacity": {},
     * "rate_id": "0",
     * "rate_plan_code": "HTPKGN",
     * "rate_name": "HTPKGN",
     * "rate_description": "With a separate bedroom and living area in approximately 540 square feet, you?ll enjoy privacy as well as space to socialize and relax. Park Central?s Premier suites feature a king-size bed, pull-out sofa, two HD TVs, and our notable mahogany-finish furnishings. These suites are located on higher floors and offer impressive city views. Max. 4 guests (extra-person charges apply).",
     * "total_price": 5510.1,
     * "total_tax": 727.42,
     * "total_fees": 183.6,
     * "total_net": 4782.68,
     * "markup": 0,
     * "pricing_rules_applier": {
     * "count": 0,
     * "list": {}
     * },
     * "currency": "USD",
     * "booking_item": "2f52145b-df9c-469d-a802-658a9f4bd1e4",
     * "cancellation_policies": {
     * {
     * "description": "General Cancellation Policy",
     * "type": "General",
     * "penalty_start_date": "2025-10-02",
     * "percentage": "100",
     * "amount": 5510.1,
     * "nights": "1",
     * "currency": "USD",
     * "level": "rate"
     * }
     * },
     * "non_refundable": true,
     * "meal_plan": "Room Only",
     * "bed_configurations": {},
     * "breakdown": {
     * "nightly": {
     * {
     * {
     * "type": "base_rate",
     * "value": 1099.31,
     * "currency": "USD"
     * },
     * {
     * "type": "tax",
     * "value": 167.64,
     * "currency": "USD"
     * }
     * },
     * {
     * {
     * "type": "base_rate",
     * "value": 1131.43,
     * "currency": "USD"
     * },
     * {
     * "type": "tax",
     * "value": 172.38,
     * "currency": "USD"
     * }
     * },
     * {
     * {
     * "type": "base_rate",
     * "value": 1275.97,
     * "currency": "USD"
     * },
     * {
     * "type": "tax",
     * "value": 193.7,
     * "currency": "USD"
     * }
     * },
     * {
     * {
     * "type": "base_rate",
     * "value": 1275.97,
     * "currency": "USD"
     * },
     * {
     * "type": "tax",
     * "value": 193.7,
     * "currency": "USD"
     * }
     * }
     * },
     * "stay": {
     * {
     * "type": "tax",
     * "title": "NY City Sales Tax",
     * "amount": 64.58,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "New York State Sales Tax",
     * "amount": 97.56,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Javits Center Fee",
     * "amount": 1.5,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Occupancy Tax",
     * "amount": 4,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "NY City Sales Tax",
     * "amount": 66.47,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "New York State Sales Tax",
     * "amount": 100.41,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Javits Center Fee",
     * "amount": 1.5,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Occupancy Tax",
     * "amount": 4,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "NY City Sales Tax",
     * "amount": 74.96,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "New York State Sales Tax",
     * "amount": 113.24,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Javits Center Fee",
     * "amount": 1.5,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Occupancy Tax",
     * "amount": 4,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "NY City Sales Tax",
     * "amount": 74.96,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay",
     * "date": "2025-12-06"
     * },
     * {
     * "type": "tax",
     * "title": "New York State Sales Tax",
     * "amount": 113.24,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay",
     * "date": "2025-12-06"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Javits Center Fee",
     * "amount": 1.5,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge",
     * "date": "2025-12-06"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Occupancy Tax",
     * "amount": 4,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge",
     * "date": "2025-12-06"
     * },
     * {
     * "type": "tax",
     * "title": "Destination Fee",
     * "amount": 45.9,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": "The Park Central Hotel New York mandatory Destination Fee priced at $40.00 plus tax per room, per night which provides guests with the following services, amenities and added value offerings:\n\n\r\n* Complimentary Wi-Fi\n\r\n* Unlimited Local and Domestic Long Distance calls\n\r\n* iMac Apple workstations with printing, faxing and scanning offered through our Guest Relations Team\n\r\n* Free Luggage Storage\n\r\n* 10% discount at in-house food and beverage outlets\n\r\n* 20% discount at Serafina Restaurant, 10% at Red Eye Grill,  10% Mulberry & Grand, 10% Fuji sushi, 15% Cucina 8 1/2\n\r\n* 1 hour complimentary bike rental for 2 at Fancy Apple Bike Rental with a purchase of one hour\n\n\r\n\r\nThe facilities fee is not inclusive of service gratuities.\n\r\nRestaurant opening times and menus might change, due to government regulations. ",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "Destination Fee",
     * "amount": 45.9,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": "The Park Central Hotel New York mandatory Destination Fee priced at $40.00 plus tax per room, per night which provides guests with the following services, amenities and added value offerings:\n\n\r\n* Complimentary Wi-Fi\n\r\n* Unlimited Local and Domestic Long Distance calls\n\r\n* iMac Apple workstations with printing, faxing and scanning offered through our Guest Relations Team\n\r\n* Free Luggage Storage\n\r\n* 10% discount at in-house food and beverage outlets\n\r\n* 20% discount at Serafina Restaurant, 10% at Red Eye Grill,  10% Mulberry & Grand, 10% Fuji sushi, 15% Cucina 8 1/2\n\r\n* 1 hour complimentary bike rental for 2 at Fancy Apple Bike Rental with a purchase of one hour\n\n\r\n\r\nThe facilities fee is not inclusive of service gratuities.\n\r\nRestaurant opening times and menus might change, due to government regulations. ",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "Destination Fee",
     * "amount": 45.9,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": "The Park Central Hotel New York mandatory Destination Fee priced at $40.00 plus tax per room, per night which provides guests with the following services, amenities and added value offerings:\n\n\r\n* Complimentary Wi-Fi\n\r\n* Unlimited Local and Domestic Long Distance calls\n\r\n* iMac Apple workstations with printing, faxing and scanning offered through our Guest Relations Team\n\r\n* Free Luggage Storage\n\r\n* 10% discount at in-house food and beverage outlets\n\r\n* 20% discount at Serafina Restaurant, 10% at Red Eye Grill,  10% Mulberry & Grand, 10% Fuji sushi, 15% Cucina 8 1/2\n\r\n* 1 hour complimentary bike rental for 2 at Fancy Apple Bike Rental with a purchase of one hour\n\n\r\n\r\nThe facilities fee is not inclusive of service gratuities.\n\r\nRestaurant opening times and menus might change, due to government regulations. ",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "Destination Fee",
     * "amount": 45.9,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": "The Park Central Hotel New York mandatory Destination Fee priced at $40.00 plus tax per room, per night which provides guests with the following services, amenities and added value offerings:\n\n\r\n* Complimentary Wi-Fi\n\r\n* Unlimited Local and Domestic Long Distance calls\n\r\n* iMac Apple workstations with printing, faxing and scanning offered through our Guest Relations Team\n\r\n* Free Luggage Storage\n\r\n* 10% discount at in-house food and beverage outlets\n\r\n* 20% discount at Serafina Restaurant, 10% at Red Eye Grill,  10% Mulberry & Grand, 10% Fuji sushi, 15% Cucina 8 1/2\n\r\n* 1 hour complimentary bike rental for 2 at Fancy Apple Bike Rental with a purchase of one hour\n\n\r\n\r\nThe facilities fee is not inclusive of service gratuities.\n\r\nRestaurant opening times and menus might change, due to government regulations. ",
     * "date": "2025-12-06"
     * }
     * },
     * "fees": {
     * {
     * "type": "fee",
     * "title": "NY City Sales Tax",
     * "amount": 280.97,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay"
     * },
     * {
     * "type": "fee",
     * "title": "NYC Javits Center Fee",
     * "amount": 6,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge"
     * },
     * {
     * "type": "fee",
     * "title": "New York State Sales Tax",
     * "amount": 424.45,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay"
     * },
     * {
     * "type": "fee",
     * "title": "NYC Occupancy Tax",
     * "amount": 16,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge"
     * },
     * {
     * "type": "fee",
     * "title": "Destination Fee",
     * "amount": 183.6,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": ""
     * }
     * }
     * },
     * "package_deal": false,
     * "penalty_date": null,
     * "promotions": {},
     * "deposits": {},
     * "descriptive_content": {},
     * "commissionable_amount": 4782.68,
     * "commission_amount": 0,
     * "parent_booking_item": "a1a6065c-edca-48bf-9cf3-f27f19e20309"
     * },
     * {
     * "unified_room_code": "external_PKS",
     * "giata_room_code": "277",
     * "giata_room_name": "",
     * "supplier_room_name": "Premier One Bedroom Suite",
     * "per_day_rate_breakdown": "",
     * "supplier_room_id": 2,
     * "distribution": false,
     * "query_package": "",
     * "room_type": "PKS",
     * "room_description": "",
     * "amenities": {},
     * "capacity": {},
     * "rate_id": "0",
     * "rate_plan_code": "HTPKGN",
     * "rate_name": "HTPKGN",
     * "rate_description": "With a separate bedroom and living area in approximately 540 square feet, you?ll enjoy privacy as well as space to socialize and relax. Park Central?s Premier suites feature a king-size bed, pull-out sofa, two HD TVs, and our notable mahogany-finish furnishings. These suites are located on higher floors and offer impressive city views. Max. 4 guests (extra-person charges apply).",
     * "total_price": 5510.1,
     * "total_tax": 727.42,
     * "total_fees": 183.6,
     * "total_net": 4782.68,
     * "markup": 0,
     * "pricing_rules_applier": {
     * "count": 0,
     * "list": {}
     * },
     * "currency": "USD",
     * "booking_item": "a11c559b-72b5-44bc-ae6f-a81b2b56fb77",
     * "cancellation_policies": {
     * {
     * "description": "General Cancellation Policy",
     * "type": "General",
     * "penalty_start_date": "2025-10-02",
     * "percentage": "100",
     * "amount": 5510.1,
     * "nights": "1",
     * "currency": "USD",
     * "level": "rate"
     * }
     * },
     * "non_refundable": true,
     * "meal_plan": "Room Only",
     * "bed_configurations": {},
     * "breakdown": {
     * "nightly": {
     * {
     * {
     * "type": "base_rate",
     * "value": 1099.31,
     * "currency": "USD"
     * },
     * {
     * "type": "tax",
     * "value": 167.64,
     * "currency": "USD"
     * }
     * },
     * {
     * {
     * "type": "base_rate",
     * "value": 1131.43,
     * "currency": "USD"
     * },
     * {
     * "type": "tax",
     * "value": 172.38,
     * "currency": "USD"
     * }
     * },
     * {
     * {
     * "type": "base_rate",
     * "value": 1275.97,
     * "currency": "USD"
     * },
     * {
     * "type": "tax",
     * "value": 193.7,
     * "currency": "USD"
     * }
     * },
     * {
     * {
     * "type": "base_rate",
     * "value": 1275.97,
     * "currency": "USD"
     * },
     * {
     * "type": "tax",
     * "value": 193.7,
     * "currency": "USD"
     * }
     * }
     * },
     * "stay": {
     * {
     * "type": "tax",
     * "title": "NY City Sales Tax",
     * "amount": 64.58,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "New York State Sales Tax",
     * "amount": 97.56,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Javits Center Fee",
     * "amount": 1.5,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Occupancy Tax",
     * "amount": 4,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "NY City Sales Tax",
     * "amount": 66.47,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "New York State Sales Tax",
     * "amount": 100.41,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Javits Center Fee",
     * "amount": 1.5,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Occupancy Tax",
     * "amount": 4,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "NY City Sales Tax",
     * "amount": 74.96,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "New York State Sales Tax",
     * "amount": 113.24,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Javits Center Fee",
     * "amount": 1.5,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Occupancy Tax",
     * "amount": 4,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "NY City Sales Tax",
     * "amount": 74.96,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay",
     * "date": "2025-12-06"
     * },
     * {
     * "type": "tax",
     * "title": "New York State Sales Tax",
     * "amount": 113.24,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay",
     * "date": "2025-12-06"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Javits Center Fee",
     * "amount": 1.5,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge",
     * "date": "2025-12-06"
     * },
     * {
     * "type": "tax",
     * "title": "NYC Occupancy Tax",
     * "amount": 4,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge",
     * "date": "2025-12-06"
     * },
     * {
     * "type": "tax",
     * "title": "Destination Fee",
     * "amount": 45.9,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": "The Park Central Hotel New York mandatory Destination Fee priced at $40.00 plus tax per room, per night which provides guests with the following services, amenities and added value offerings:\n\n\r\n* Complimentary Wi-Fi\n\r\n* Unlimited Local and Domestic Long Distance calls\n\r\n* iMac Apple workstations with printing, faxing and scanning offered through our Guest Relations Team\n\r\n* Free Luggage Storage\n\r\n* 10% discount at in-house food and beverage outlets\n\r\n* 20% discount at Serafina Restaurant, 10% at Red Eye Grill,  10% Mulberry & Grand, 10% Fuji sushi, 15% Cucina 8 1/2\n\r\n* 1 hour complimentary bike rental for 2 at Fancy Apple Bike Rental with a purchase of one hour\n\n\r\n\r\nThe facilities fee is not inclusive of service gratuities.\n\r\nRestaurant opening times and menus might change, due to government regulations. ",
     * "date": "2025-12-03"
     * },
     * {
     * "type": "tax",
     * "title": "Destination Fee",
     * "amount": 45.9,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": "The Park Central Hotel New York mandatory Destination Fee priced at $40.00 plus tax per room, per night which provides guests with the following services, amenities and added value offerings:\n\n\r\n* Complimentary Wi-Fi\n\r\n* Unlimited Local and Domestic Long Distance calls\n\r\n* iMac Apple workstations with printing, faxing and scanning offered through our Guest Relations Team\n\r\n* Free Luggage Storage\n\r\n* 10% discount at in-house food and beverage outlets\n\r\n* 20% discount at Serafina Restaurant, 10% at Red Eye Grill,  10% Mulberry & Grand, 10% Fuji sushi, 15% Cucina 8 1/2\n\r\n* 1 hour complimentary bike rental for 2 at Fancy Apple Bike Rental with a purchase of one hour\n\n\r\n\r\nThe facilities fee is not inclusive of service gratuities.\n\r\nRestaurant opening times and menus might change, due to government regulations. ",
     * "date": "2025-12-04"
     * },
     * {
     * "type": "tax",
     * "title": "Destination Fee",
     * "amount": 45.9,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": "The Park Central Hotel New York mandatory Destination Fee priced at $40.00 plus tax per room, per night which provides guests with the following services, amenities and added value offerings:\n\n\r\n* Complimentary Wi-Fi\n\r\n* Unlimited Local and Domestic Long Distance calls\n\r\n* iMac Apple workstations with printing, faxing and scanning offered through our Guest Relations Team\n\r\n* Free Luggage Storage\n\r\n* 10% discount at in-house food and beverage outlets\n\r\n* 20% discount at Serafina Restaurant, 10% at Red Eye Grill,  10% Mulberry & Grand, 10% Fuji sushi, 15% Cucina 8 1/2\n\r\n* 1 hour complimentary bike rental for 2 at Fancy Apple Bike Rental with a purchase of one hour\n\n\r\n\r\nThe facilities fee is not inclusive of service gratuities.\n\r\nRestaurant opening times and menus might change, due to government regulations. ",
     * "date": "2025-12-05"
     * },
     * {
     * "type": "tax",
     * "title": "Destination Fee",
     * "amount": 45.9,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": "The Park Central Hotel New York mandatory Destination Fee priced at $40.00 plus tax per room, per night which provides guests with the following services, amenities and added value offerings:\n\n\r\n* Complimentary Wi-Fi\n\r\n* Unlimited Local and Domestic Long Distance calls\n\r\n* iMac Apple workstations with printing, faxing and scanning offered through our Guest Relations Team\n\r\n* Free Luggage Storage\n\r\n* 10% discount at in-house food and beverage outlets\n\r\n* 20% discount at Serafina Restaurant, 10% at Red Eye Grill,  10% Mulberry & Grand, 10% Fuji sushi, 15% Cucina 8 1/2\n\r\n* 1 hour complimentary bike rental for 2 at Fancy Apple Bike Rental with a purchase of one hour\n\n\r\n\r\nThe facilities fee is not inclusive of service gratuities.\n\r\nRestaurant opening times and menus might change, due to government regulations. ",
     * "date": "2025-12-06"
     * }
     * },
     * "fees": {
     * {
     * "type": "fee",
     * "title": "NY City Sales Tax",
     * "amount": 280.97,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "5.875% per stay"
     * },
     * {
     * "type": "fee",
     * "title": "NYC Javits Center Fee",
     * "amount": 6,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$1.50 Daily Charge"
     * },
     * {
     * "type": "fee",
     * "title": "New York State Sales Tax",
     * "amount": 424.45,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "8.875% per stay"
     * },
     * {
     * "type": "fee",
     * "title": "NYC Occupancy Tax",
     * "amount": 16,
     * "currency": "USD",
     * "collected_by": "booking",
     * "description": "$2.00 Daily Charge"
     * },
     * {
     * "type": "fee",
     * "title": "Destination Fee",
     * "amount": 183.6,
     * "currency": "USD",
     * "collected_by": "property",
     * "description": ""
     * }
     * }
     * },
     * "package_deal": false,
     * "penalty_date": null,
     * "promotions": {},
     * "deposits": {},
     * "descriptive_content": {},
     * "commissionable_amount": 4782.68,
     * "commission_amount": 0,
     * "parent_booking_item": "a1a6065c-edca-48bf-9cf3-f27f19e20309"
     * }
     * },
     * "first_search": {
     * {
     * "giata_code": "37305666",
     * "room_code": "PKS",
     * "room_name": "Premier One Bedroom Suite",
     * "rate_code": "HTPKGN",
     * "booking_item": "ffba33ab-0a3a-488a-a0b0-e2aab2797ce2",
     * "parent_booking_item": "994059e0-c126-434d-b52d-70dd4103bc37",
     * "total_net": 4782.68,
     * "total_tax": 727.42,
     * "total_fees": 183.6,
     * "total_price": 5510.1,
     * "markup": 0,
     * "currency": "USD",
     * "supplier_room_id": 1
     * },
     * {
     * "giata_code": "37305666",
     * "room_code": "PKS",
     * "room_name": "Premier One Bedroom Suite",
     * "rate_code": "HTPKGN",
     * "booking_item": "65cadf6f-d73d-4096-9c50-1054dcba8380",
     * "parent_booking_item": "994059e0-c126-434d-b52d-70dd4103bc37",
     * "total_net": 4782.68,
     * "total_tax": 727.42,
     * "total_fees": 183.6,
     * "total_price": 5510.1,
     * "markup": 0,
     * "currency": "USD",
     * "supplier_room_id": 2
     * }
     * }
     * },
     * "message": "success"
     * }
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse")
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *       }
     *     )
     *   )
     * )
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_item' => 'required|size:36',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
