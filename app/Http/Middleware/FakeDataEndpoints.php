<?php

namespace App\Http\Middleware;

use App\Models\ApiBookingInspector;
use App\Models\ApiSearchInspector;
use App\Repositories\ChannelRenository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FakeDataEndpoints
{
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();
        $channelName = ChannelRenository::getTokenName($token);

        if ($channelName === 'FakeChannel') {
            $path = $request->path();

            $searchInspector = ApiSearchInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])->inRandomOrder()->first();

            $add_item = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'add_item')->inRandomOrder()->first();
            $add_passengers = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'add_passengers')->inRandomOrder()->first();
            $retrieve_items = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'retrieve_booking')->inRandomOrder()->first();
            $remove_item = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'remove_item')->inRandomOrder()->first();
            $book = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'book')->where('sub_type', 'retrieve')->inRandomOrder()->first();
            //            dd($add_passengers);

            switch ($path) {
                case 'api/pricing/search':
                    return response()->json(json_decode(Storage::get($searchInspector->client_response_path), true));

                    //                case 'api/booking/add-item': return response()->json(json_decode(Storage::get($add_item->response_path), true));
                    //                case 'api/booking/retrieve-items': return response()->json(json_decode(Storage::get($retrieve_items->client_response_path), true));
                    //                case 'api/booking/remove-item': return response()->json(json_decode(Storage::get($remove_item->client_response_path), true));
                    //                case 'api/booking/add-passengers': return response()->json(json_decode(Storage::get($add_passengers->client_response_path), true));

                    //                case 'api/booking/book': return response()->json(json_decode(Storage::get($book->client_response_path), true));

                case 'api/booking/add-item':
                    return response($this->addItem(), 200, ['Content-Type' => 'application/json']);

                case 'api/booking/retrieve-items':
                    return response($this->retrieveItems(), 200, ['Content-Type' => 'application/json']);
                case 'api/booking/remove-item':
                    return response($this->removeItem(), 200, ['Content-Type' => 'application/json']);
                case 'api/booking/add-passengers':
                    return response($this->addPassengers(), 200, ['Content-Type' => 'application/json']);

                case 'api/booking/book':
                    return response($this->book(), 200, ['Content-Type' => 'application/json']);
            }
        }

        return $next($request);
    }

    private function addItem(): string
    {
        return '{
          "success": true,
          "data": {
            "booking_id": "c0e509c2-09cd-4555-8937-73135c2c9b09"
          },
          "message": "success"
        }';
    }

    private function retrieveItems(): string
    {
        return '{
            "status": "booked",
            "booking_id": "feab8500-96ae-4cbb-ba8c-ad26a8d99e26",
            "booking_item": "bf19e485-103e-4b50-a360-5446bdc259c7",
            "supplier": "Expedia",
            "hotel_name": "Waldorf Astoria Chicago (85653324)",
            "rooms": [
                {
                    "checkin": "2023-12-16",
                    "checkout": "2023-12-18",
                    "number_of_adults": 1,
                    "given_name": "Wendy",
                    "family_name": "Lebsack",
                    "room_name": "Waldorf Suite, Junior Suite, 1 King Bed",
                    "room_type": ""
                },
                {
                    "checkin": "2023-12-16",
                    "checkout": "2023-12-18",
                    "number_of_adults": 3,
                    "given_name": "Maximillian",
                    "family_name": "Borer",
                    "room_name": "Waldorf Suite, Junior Suite, 1 King Bed",
                    "room_type": ""
                }
            ],
            "cancellation_terms": "",
            "rate": "222049470",
            "total_price": 4170.48,
            "total_tax": 618.14,
            "total_fees": 0,
            "total_net": 3552.34,
            "markup": 0,
            "currency": "CAD",
            "per_night_breakdown": 2085.24,
            "board_basis": "",
            "supplier_book_id": "7871270622551",
            "billing_contact": {
                "given_name": "Sim",
                "family_name": "Prosacco",
                "address": {
                    "line_1": "92298 Kihn Courts Apt. 439",
                    "city": "Port Alaina",
                    "state_province_code": "MD",
                    "postal_code": "gcpczw",
                    "country_code": "GU"
                }
            },
            "billing_email": "vhickle@vandervort.com",
            "billing_phone": {
                "country_code": "1",
                "area_code": "487",
                "number": "5550077"
            },
            "query": {
                "type": "hotel",
                "rating": 5,
                "checkin": "2023-12-16",
                "checkout": "2023-12-18",
                "currency": "CAD",
                "occupancy": [
                    {
                        "adults": 1
                    },
                    {
                        "adults": 3
                    }
                ],
                "destination": 1102
            }
        }';
    }

    private function removeItem(): string
    {
        return '{
            "success": {
                "booking_id": "6d5f65b2-19a6-4506-9e99-62b7dea96db0",
                "booking_item": "24aa92bd-3a56-421d-a519-71b8b4ecf48b",
                "status": "Item removed from cart."
            }
        }';
    }

    private function addPassengers(): string
    {
        return '{
            "booking_id": "21f654e6-d5ca-406e-afe4-8159e0fdeb8e",
            "booking_item": "d785174a-02e3-4005-82d9-fc3a59a861e2",
            "status": "Passengers added to booking."
        }';
    }

    private function book(): string
    {
        return '{
            "status": "booked",
            "booking_id": "5e0c17b3-f686-42b8-a0b8-c503ae8fb798",
            "booking_item": "bd93e6cb-9ff0-46b6-95fa-5829916808ca",
            "supplier": "Expedia",
            "hotel_name": "Trump International Hotel & Tower Chicago (69801520)",
            "rooms": {
                "room_name": "Deluxe Suite, 1 Bedroom, City View (1 King Bed)",
                "meal_plan": ""
            },
            "cancellation_terms": "",
            "rate": "204801137",
            "total_price": 352530,
            "total_tax": 52248,
            "total_fees": 0,
            "total_net": 300282,
            "markup": 0,
            "currency": "JPY",
            "per_night_breakdown": 88132.5,
            "links": {
                "remove": {
                    "method": "DELETE",
                    "href": "/api/booking/cancel-booking?booking_id=5e0c17b3-f686-42b8-a0b8-c503ae8fb798&booking_item=bd93e6cb-9ff0-46b6-95fa-5829916808ca"
                },
                "change": {
                    "method": "PUT",
                    "href": "/api/booking/change-booking?booking_id=5e0c17b3-f686-42b8-a0b8-c503ae8fb798&booking_item=bd93e6cb-9ff0-46b6-95fa-5829916808ca"
                },
                "retrieve": {
                    "method": "GET",
                    "href": "/api/booking/retrieve-booking?booking_id=5e0c17b3-f686-42b8-a0b8-c503ae8fb798"
                }
            }
        }';
    }
}
