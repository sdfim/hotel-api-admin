<?php

namespace Modules\API\Services;

use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Repositories\ApiBookingItemRepository;
use Carbon\Carbon;
use Modules\Enums\TypeRequestEnum;

class HotelBookingAddPassengersService
{
    private const AGE_ADULT = 18;

    public function checkCountGuestsChildrenAges(array $filtersOutput): array
    {
        foreach ($filtersOutput as $bookingItem => $booking) {
            $search = ApiBookingItem::where('booking_item', $bookingItem)->first();

            if (! $search) {
                return ['booking_item' => 'Invalid booking_item'];
            }

            $type = ApiSearchInspector::where('search_id', $search->search_id)->first()->search_type;

            if (TypeRequestEnum::from($type) === TypeRequestEnum::FLIGHT) {
                continue;
            }
            if (TypeRequestEnum::from($type) === TypeRequestEnum::COMBO) {
                continue;
            }
            if (TypeRequestEnum::from($type) === TypeRequestEnum::HOTEL) {
                return $this->checkCountGuestsChildrenAgesHotel($bookingItem, $booking, $search->search_id);
            }
        }

        return [];
    }

    public function checkCountGuestsChildrenAgesHotel($bookingItem, $booking, $searchId): array
    {
        $searchData = json_decode(ApiSearchInspector::where('search_id', $searchId)->first()->request, true);

        foreach ($booking['rooms'] as $room => $roomData) {

            $ages = [];
            foreach ($roomData['passengers'] as $passenger) {
                $dob = Carbon::parse($passenger['date_of_birth']);
                $now = Carbon::now();
                $ages[] = floor($now->diffInYears($dob, true));
            }

            $childrenCount = 0;
            $adultsCount = 0;
            foreach ($ages as $age) {
                if ($age < self::AGE_ADULT) {
                    $childrenCount++;
                } else {
                    $adultsCount++;
                }
            }

            if ($adultsCount != $searchData['occupancy'][$room - 1]['adults']) {
                return [
                    'type' => 'The number of adults not match.',
                    'booking_item' => $bookingItem,
                    'search_id' => $searchId,
                    'room' => $room,
                    'number_of_adults_in_search' => $searchData['occupancy'][$room - 1]['adults'],
                    'number_of_adults_in_query' => $adultsCount,
                ];
            }
            if (! isset($searchData['occupancy'][$room - 1]['children_ages']) && $childrenCount != 0) {
                return [
                    'type' => 'The number of children not match.',
                    'booking_item' => $bookingItem,
                    'search_id' => $searchId,
                    'room' => $room,
                    'number_of_children_in_search' => 0,
                    'number_of_children_in_query' => $childrenCount,
                ];
            }

            if (! isset($searchData['occupancy'][$room - 1]['children_ages'])) {
                continue;
            }

            if ($childrenCount != count($searchData['occupancy'][$room - 1]['children_ages'])) {
                return [
                    'type' => 'The number of children not match.',
                    'booking_item' => $bookingItem,
                    'search_id' => $searchId,
                    'room' => $room,
                    'number_of_children_in_search' => count($searchData['occupancy'][$room - 1]['children_ages']),
                    'number_of_children_in_query' => $childrenCount,
                ];
            }

            $childrenAges = $searchData['occupancy'][$room - 1]['children_ages'];
            sort($childrenAges);
            $childrenAgesInQuery = [];
            foreach ($roomData['passengers'] as $passenger) {
                $givenDate = Carbon::create($passenger['date_of_birth']);
                $currentDate = Carbon::now();
                $years = floor($givenDate->diffInYears($currentDate, true));
                if ($years >= self::AGE_ADULT) {
                    continue;
                }
                $childrenAgesInQuery[] = $years;
            }
            sort($childrenAgesInQuery);
            if ($childrenAges != $childrenAgesInQuery) {
                return [
                    'type' => 'Children ages not match.',
                    'booking_item' => $bookingItem,
                    'search_id' => $searchId,
                    'room' => $room,
                    'children_ages_in_search' => implode(',', $childrenAges),
                    'children_ages_in_query' => implode(',', $childrenAgesInQuery),
                ];
            }
        }

        return [];
    }

    public function dtoAddPassengers(array $input): array
    {
        $output = [];
        foreach ($input['passengers'] as $passenger) {
            foreach ($passenger['booking_items'] as $booking) {
                $bookingItem = $booking['booking_item'];

                $bookingItem = ApiBookingItemRepository::checkBookingItem($bookingItem) ?? $bookingItem;

                $age = null;
                if (! empty($passenger['date_of_birth'])) {
                    try {
                        $age = Carbon::parse($passenger['date_of_birth'])->age;
                    } catch (\Exception $e) {
                        $age = null;
                    }
                }

                // type hotel
                if (isset($booking['room'])) {
                    $room = $booking['room'];
                    if (isset($output[$bookingItem])) {
                        $output[$bookingItem]['rooms'][$room]['passengers'][] = [
                            'title' => $passenger['title'],
                            'given_name' => $passenger['given_name'],
                            'family_name' => $passenger['family_name'],
                            'date_of_birth' => $passenger['date_of_birth'],
                            'age' => $age,
                        ];
                    } else {
                        $output[$bookingItem] = [
                            'booking_item' => $bookingItem,
                            'rooms' => [
                                $room => [
                                    'passengers' => [
                                        [
                                            'title' => $passenger['title'],
                                            'given_name' => $passenger['given_name'],
                                            'family_name' => $passenger['family_name'],
                                            'date_of_birth' => $passenger['date_of_birth'],
                                            'age' => $age,
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    }
                }
                // type flight
                if (! isset($booking['room'])) {
                    if (isset($output[$bookingItem])) {
                        $output[$bookingItem]['passengers'][] = [
                            'title' => $passenger['title'],
                            'given_name' => $passenger['given_name'],
                            'family_name' => $passenger['family_name'],
                            'date_of_birth' => $passenger['date_of_birth'],
                            'age' => $age,
                        ];
                    } else {
                        $output[$bookingItem] = [
                            'booking_item' => $bookingItem,
                            'passengers' => [
                                [
                                    'title' => $passenger['title'],
                                    'given_name' => $passenger['given_name'],
                                    'family_name' => $passenger['family_name'],
                                    'date_of_birth' => $passenger['date_of_birth'],
                                    'age' => $age,
                                ],
                            ],
                        ];
                    }
                }

            }
        }

        return $output;
    }

}
