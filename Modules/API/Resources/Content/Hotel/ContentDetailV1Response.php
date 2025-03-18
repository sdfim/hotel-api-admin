<?php

namespace Modules\API\Resources\Content\Hotel;

/**
 * @OA\Schema(
 *   schema="ContentDetailV1Response",
 *   title="Content Detail Response",
 *   description="Schema for Content Detail Response",
 *   type="object",
 *   required={"success", "data"},
 *
 *   @OA\Property(
 *     property="success",
 *     type="boolean",
 *     description="Indicates whether the request was successful.",
 *     example=true
 *   ),
 *   @OA\Property(
 *     property="data",
 *     type="object",
 *     description="Contains hotel details.",
 *     @OA\Property(
 *       property="results",
 *       type="array",
 *       description="List of hotels from Expedia.",
 *       @OA\Items(
 *         type="object",
 *         description="Details of a specific hotel.",
 *
 *         @OA\Property(
 *           property="giata_hotel_code",
 *           type="integer",
 *           description="Unique identifier for the hotel.",
 *           example=98736411
 *         ),
 *         @OA\Property(
 *           property="images",
 *           type="array",
 *           description="Array of image URLs for the hotel.",
 *           @OA\Items(type="string", format="url", example="https://example.com/hotel-image.jpg")
 *         ),
 *         @OA\Property(
 *           property="description",
 *           type="string",
 *           description="Description of the hotel.",
 *           example="A luxurious beach resort with modern amenities."
 *         ),
 *         @OA\Property(
 *           property="hotel_name",
 *           type="string",
 *           description="Name of the hotel.",
 *           example="Grace Bay Club"
 *         ),
 *         @OA\Property(
 *           property="latitude",
 *           type="number",
 *           format="float",
 *           description="Latitude of the hotel location.",
 *           example=21.7996604
 *         ),
 *         @OA\Property(
 *           property="longitude",
 *           type="number",
 *           format="float",
 *           description="Longitude of the hotel location.",
 *           example=-72.1732285
 *         ),
 *         @OA\Property(
 *           property="rating",
 *           type="number",
 *           format="float",
 *           description="Hotel rating.",
 *           example=4.5
 *         ),
 *         @OA\Property(
 *           property="address",
 *           type="object",
 *           description="Hotel address details.",
 *           @OA\Property(
 *             property="city",
 *             type="string",
 *             description="City where the hotel is located.",
 *             example="Grace Bay"
 *           ),
 *           @OA\Property(
 *             property="line_1",
 *             type="string",
 *             description="Street address of the hotel.",
 *             example="321 West 35th Street, New York"
 *           ),
 *           @OA\Property(
 *             property="country_code",
 *             type="string",
 *             description="Country code.",
 *             example="TC"
 *           ),
 *           @OA\Property(
 *             property="state_province_name",
 *             type="string",
 *             description="State or province name.",
 *             example="Caicos Islands"
 *           )
 *         ),
 *         @OA\Property(
 *           property="rooms",
 *           type="array",
 *           description="Available rooms in the hotel.",
 *           @OA\Items(
 *             type="object",
 *             description="Details of a specific room.",
 *
 *             @OA\Property(
 *               property="supplier_room_id",
 *               type="integer",
 *               description="Unique room identifier.",
 *               example=12345
 *             ),
 *             @OA\Property(
 *               property="supplier_room_name",
 *               type="string",
 *               description="Room name.",
 *               example="Deluxe Suite"
 *             ),
 *             @OA\Property(
 *               property="amenities",
 *               type="object",
 *               description="Room amenities.",
 *               @OA\AdditionalProperties(
 *                 type="string",
 *                 example="Free Wi-Fi"
 *               )
 *             ),
 *             @OA\Property(
 *               property="images",
 *               type="array",
 *               description="Image URLs for the room.",
 *               @OA\Items(type="string", format="url", example="https://example.com/room-image.jpg")
 *             )
 *           )
 *         )
 *       )
 *     )
 *   )
 * ),
 *
 * @OA\Examples(
 *   example="ContentDetailV1Response",
 *   summary="Content Detail Example",
 *   description="An example response for the Content Detail API.",
 *   value={
 *     "success": true,
 *     "data": {
 *       "results": {
 *         {
 *           "giata_hotel_code": 45422295,
 *           "images": {
 *             "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/becdfd2f_z.jpg",
 *             "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/d3c8f6fc_z.jpg",
 *             "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/e38f15a9_z.jpg",
 *             "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/887d65fd_z.jpg",
 *             "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/9b9b6cd8_z.jpg",
 *             "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/4a061a60_z.jpg",
 *             "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/09c07d78_z.jpg",
 *             "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/15a01327_z.jpg"
 *           },
 *           "hotel_name": "Grace Bay Club",
 *           "distance": "",
 *           "latitude": 21.7996604,
 *           "longitude": -72.1732285,
 *           "rating": 5,
 *           "amenities": {},
 *           "giata_destination": "Grace Bay",
 *           "user_rating": 5,
 *           "special_instructions": {
 *             "checkin": {
 *               "min_age": 18,
 *               "end_time": "anytime",
 *               "begin_time": "3:00 PM",
 *               "instructions": "<ul>  <li>Extra-person charges may apply and vary depending on property policy</li><li>Government-issued photo identification and a credit card, debit card, or cash deposit may be required at check-in for incidental charges</li><li>Special requests are subject to availability upon check-in and may incur additional charges; special requests cannot be guaranteed</li><li>The name on the credit card used at check-in to pay for incidentals must be the primary name on the guestroom reservation</li><li>This property accepts credit cards and cash</li>  </ul>",
 *               "special_instructions": "The front desk is open daily from 8:00 AM - 8:00 PM. The front desk is staffed during limited hours."
 *             },
 *             "checkout": {
 *               "time": "11:00 AM"
 *             }
 *           },
 *           "check_in_time": "3:00 PM",
 *           "check_out_time": "11:00 AM",
 *           "hotel_fees": {
 *             "mandatory": {
 *               {
 *                 "name": "Tax",
 *                 "type": "Tax",
 *                 "net_value": 27,
 *                 "rack_value": 27,
 *                 "apply_type": "per_night",
 *                 "commissionable": false
 *               }
 *             }
 *           },
 *           "policies": {
 *             "know_before_you_go": "<ul>  <li>Children may not be eligible for complimentary breakfast.</li><li>No pets and no service animals are allowed at this property. </li><li>Contactless check-in and contactless check-out are available.</li> </ul>"
 *           },
 *           "descriptions": {
 *             "Note": {
 *               "value": "Screenshot for price match if needed.",
 *               "start_date": null,
 *               "end_date": null
 *             },
 *             "Cancellation Policy": {
 *               "value": "Resort Policy Updates (excludes Private Villa Collection)\n•72 hour cancellation policy from Jan 3, 2021 to Mar 31, 2021\n• 7 day cancellation policy from April 1 through June 30, 2021\n• 14 days cancellation policy from Jul 1 – Dec 22, 2021\n• Festive Dec 23-Jan 2: 50% deposit on booking, Sep 1 full deposit required, non-refundable as of Sep 1\n• 30 days cancellation policy from Jan 2 – Feb 15 , 2022\n• 60 days cancellation policy from Feb 16 – Apr 16, 2022\n• 30 days cancellation policy from Apr 17 – Dec 21, 2022\n• Festive Dec 22 -Jan 1: 50% deposit on booking, Sep 1 full deposit required, non-refundable as of Sep 1\n\nPrivate Villa Collections Cancellation Policy Updates\n• 50% deposit on booking, 14 day cancellation policy from Jan 3 to Jun 30, 2021 (14 day cxl extended thru Q2)\n• 50% deposit on booking, 60 day cancellation policy from July 1 to December 22, 2021\n• Festive Dec 23-Jan 2: 50% deposit on booking, Sep 1 full deposit required, non-refundable as of Sep 1\n• 50% deposit on booking, 60 day cancellation policy from Jan 2 to December 21, 2022\n• Festive Dec 22 -Jan 1: 50% deposit on booking, Sep 1 full deposit required, non-refundable as of Sep 1",
 *               "start_date": null,
 *               "end_date": null
 *             },
 *             "Payment Terms": {
 *               "value": "Credit Except for Festive. Full payment due by September 1.",
 *               "start_date": null,
 *               "end_date": null
 *             },
 *             "rooms": "Make yourself at home in one of the 25 air-conditioned guestrooms. Complimentary wireless internet access is available to keep you connected. Bathrooms have showers and hair dryers. Conveniences include safes and desks, and housekeeping is provided daily.",
 *             "dining": "Enjoy a meal at the restaurant, or stay in and take advantage of the hotel's room service (during limited hours). A complimentary full breakfast is served daily from 8:00 AM to noon.",
 *             "headline": "Near Grace Bay Beach",
 *             "location": "When you stay at The All New Grace Bay Suites in Providenciales, you'll be near the beach, a 1-minute drive from Grace Bay Beach and 7 minutes from Long Bay Beach.  This hotel is 0.1 mi (0.1 km) from Lucayan Archipelago and 0.1 mi (0.1 km) from The Regent Village Shopping Mall.",
 *             "amenities": "Enjoy recreational amenities such as an outdoor pool and bicycles to rent. Additional features at this hotel include complimentary wireless internet access, concierge services, and a picnic area.",
 *             "attractions": "Distances are displayed to the nearest 0.1 mile and kilometer. <br /> <p>Lucayan Archipelago - 0.1 km / 0.1 mi <br /> The Regent Village Shopping Mall - 0.1 km / 0.1 mi <br /> Providenciales Beaches - 0.1 km / 0.1 mi <br /> Salt Mills Plaza - 0.2 km / 0.1 mi <br /> Princess Alexandra National Park - 0.3 km / 0.2 mi <br /> Grace Bay Beach - 0.6 km / 0.4 mi <br /> Coral Gardens Reef - 3.7 km / 2.3 mi <br /> Long Bay Beach - 4 km / 2.5 mi <br /> Leeward Beach - 4.1 km / 2.5 mi <br /> The Hole - 4.3 km / 2.6 mi <br /> Royal Flush Gaming Parlor - 4.4 km / 2.7 mi <br /> Pelican Beach - 4.4 km / 2.7 mi <br /> Provo Conch Farm - 5.7 km / 3.5 mi <br /> Turtle Cove Marina - 6.7 km / 4.2 mi <br /> Turtle Lake - 7.2 km / 4.5 mi <br /> </p><p>The nearest major airport is Providenciales Intl. Airport (PLS) - 11.7 km / 7.3 mi</p>",
 *             "business_amenities": "The front desk is staffed during limited hours. Free self parking is available onsite."
 *           },
 *           "deposit_information": {
 *             {
 *               "name": "Festive Dec 23-Jan 2: 50% deposit on booking",
 *               "start_date": "2024-12-24 00:00:00",
 *               "expiration_date": "2112-02-02 00:00:00",
 *               "manipulable_price_type": "total_price",
 *               "price_value": "50.00",
 *               "price_value_type": "percentage",
 *               "price_value_target": "per_person",
 *               "conditions": "booking_date < 2021-09-01, travel_date between 2021-12-23 2022-01-02, room_type!= Private Villa"
 *             },
 *             {
 *               "name": "Festive Dec 23-Jan 2: Sep 1 full deposit required,",
 *               "start_date": "2024-12-24 00:00:00",
 *               "expiration_date": "2112-02-02 00:00:00",
 *               "manipulable_price_type": "total_price",
 *               "price_value": "100.00",
 *               "price_value_type": "percentage",
 *               "price_value_target": "per_person",
 *               "conditions": "booking_date > 2021-09-01, room_type!= Private Villa, travel_date between 2021-12-23 2022-01-02"
 *             }
 *           },
 *           "cancellation_policies": {
 *             {
 *               "name": "72 hour cancellation policy from Jan 3, 2021 to Mar 31, 2021",
 *               "start_date": "2024-12-24 00:00:00",
 *               "expiration_date": null,
 *               "manipulable_price_type": "total_price",
 *               "price_value": "100.00",
 *               "price_value_type": "percentage",
 *               "price_value_target": "per_person",
 *               "conditions": "travel_date between 2021-01-03 2021-03-31, room_type!= Private Villa, days_until_departure < 3"
 *             },
 *             {
 *               "name": "7 day cancellation policy from April 1 through June 30, 2021",
 *               "start_date": "2024-12-24 00:00:00",
 *               "expiration_date": null,
 *               "manipulable_price_type": "total_price",
 *               "price_value": "100.00",
 *               "price_value_type": "percentage",
 *               "price_value_target": "per_person",
 *               "conditions": "travel_date between 2021-04-01 2021-06-30, room_type!= Private Villa, days_until_departure < 7"
 *             }
 *           },
 *           "address": {
 *             "city": "Grace Bay",
 *             "line_1": ", TKCA 1ZZ",
 *             "country_code": "TC",
 *             "state_province_name": "Caicos Islands"
 *           },
 *           "rooms": {
 *             {
 *               "content_supplier": "Internal Repository",
 *               "supplier_room_id": null,
 *               "supplier_room_name": " Jr – 2 BD",
 *               "supplier_room_code": null,
 *               "amenities": {},
 *               "images": {},
 *               "descriptions": " Jr – 2 BD",
 *               "supplier_codes": {
 *                 "IBS": null
 *               }
 *             },
 *             {
 *               "content_supplier": "Internal Repository",
 *               "supplier_room_id": null,
 *               "supplier_room_name": " 3-5 BD",
 *               "supplier_room_code": null,
 *               "amenities": {},
 *               "images": {},
 *               "descriptions": " 3-5 BD",
 *               "supplier_codes": {
 *                 "IBS": null
 *               }
 *             },
 *             {
 *               "content_supplier": "Internal Repository",
 *               "supplier_room_id": null,
 *               "supplier_room_name": "Villa",
 *               "supplier_room_code": null,
 *               "amenities": {},
 *               "images": {},
 *               "descriptions": "Villa",
 *               "supplier_codes": {
 *                 "IBS": null
 *               }
 *             },
 *             {
 *               "content_supplier": "Expedia",
 *               "supplier_room_id": 213622819,
 *               "supplier_room_name": "Classic Room, 1 King Bed, Balcony, Pool View",
 *               "supplier_room_code": "",
 *               "amenities": {
 *                 "1": "Air conditioning",
 *                 "20": "Room service (limited hours)",
 *                 "26": "Television",
 *                 "132": "Coffee/tea maker",
 *                 "133": "Daily housekeeping"
 *               },
 *               "images": {
 *                 "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/8680fbbd_b.jpg",
 *                 "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/a5c33e05_b.jpg",
 *                 "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/2ba241ad_b.jpg",
 *                 "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/b9c187b6_b.jpg",
 *                 "https://i.travelapi.com/lodging/39000000/38340000/38338100/38338087/68566291_b.jpg"
 *               },
 *               "descriptions": "<p><strong>1 King Bed</strong></p><p>420-sq-foot soundproofed room, balcony/patio with pool views </p><br/><p><b>Internet</b> - Free WiFi </p><p><b>Food & Drink</b> - Coffee/tea maker and room service (limited hours) </p><p><b>Sleep</b> - Blackout drapes/curtains and bed sheets </p><p><b>Bathroom</b> - Private bathroom, shower, a hair dryer, and towels</p><p><b>Practical</b> - Safe, iron/ironing board, and desk; rollaway/extra beds and cribs/infant beds available on request</p><p><b>Comfort</b> - Air conditioning and daily housekeeping</p><p>Non-Smoking</p>"
 *             }
 *           },
 *           "weight": null,
 *           "structure": {
 *             "content_source": "Expedia",
 *             "room_images": "Expedia",
 *             "property_images": "Expedia"
 *           }
 *         }
 *       }
 *     },
 *     "message": "success"
 *   }
 * )
 */

class ContentDetailV1Response
{
}
