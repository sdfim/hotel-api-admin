<?php

namespace Modules\API\Tools;

use App\Models\GiataGeography;
use App\Models\GiataPlace;
use App\Models\Property;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PricingDtoTools {

    public function getGiataProperties(array $query, array $giataIds): array
    {
        $latitude = Arr::get($query, 'latitude', 0);
        $longitude = Arr::get($query, 'longitude', 0);

        if ($latitude == 0 && $longitude == 0) {
            return Property::whereIn('code', $giataIds)
                ->select('code', 'city')
                ->get()
                ->keyBy('code')
                ->map(function($item) {
                    return [
                        'city' => $item->city,
                    ];
                })
                ->toArray();
        } else {
            return Property::whereIn('code', $giataIds)
                ->selectRaw('code, rating, name, city, 6371 * 2 * ASIN(SQRT(POWER(SIN((latitude - abs(?)) * pi()/180 / 2), 2) + COS(latitude * pi()/180 ) * COS(abs(?) * pi()/180) * POWER(SIN((longitude - ?) *  pi()/180 / 2), 2))) as distance', [$latitude, $latitude, $longitude])
                ->get()
                ->keyBy('code')
                ->map(function($item) {
                    return [
                        'city' => $item->city,
                        'distance' => $item->distance,
                        'rating' => $item->rating,
                        'hotel_name' => $item->name,
                    ];
                })
                ->toArray();
        }
    }

    public function getDestinationData(array $query): string
    {
        if (isset($query['destination'])) {
            $destinationData = GiataGeography::where('city_id', $query['destination'])
                ->select([
                    DB::raw("CONCAT(city_name, ', ', locale_name, ', ', country_name) as full_location"),
                ])
                ->first()->full_location ?? '';
        } elseif (isset($query['place'])) {
            $destinationData = GiataPlace::where('key', $query['place'])
                ->select([
                    DB::raw("CONCAT(name_primary, ', ', type, ', ', country_code) as full_location"),
                ])
                ->first()->full_location ?? '';
        } else {
            $destinationData = '';
        }

        return $destinationData;
    }

    public function mergeHotelData($standalone, $package)
    {
        $result = [];

        // Объединяем оба массива
        $allHotels = array_merge($standalone, $package);

        foreach ($allHotels as $hotel) {
            $giataHotelId = $hotel['giata_hotel_id'];

            if (!isset($result[$giataHotelId])) {
                $result[$giataHotelId] = $hotel;
                $result[$giataHotelId]['room_groups'] = [];
            } else {
                // Объединяем room_combinations, сохраняя структуру
                foreach ($hotel['room_combinations'] as $key => $combination) {
                    if (!isset($result[$giataHotelId]['room_combinations'][$key])) {
                        $result[$giataHotelId]['room_combinations'][$key] = $combination;
                    } else {
                        $result[$giataHotelId]['room_combinations'][$key] = array_merge(
                            $result[$giataHotelId]['room_combinations'][$key],
                            $combination
                        );
                    }
                }

                // Объединяем refundable_rates и non_refundable_rates
                $result[$giataHotelId]['refundable_rates'] = $this->mergeRates($result[$giataHotelId]['refundable_rates'], $hotel['refundable_rates']);
                $result[$giataHotelId]['non_refundable_rates'] = $this->mergeRates($result[$giataHotelId]['non_refundable_rates'], $hotel['non_refundable_rates']);

                // Обновляем другие поля, если необходимо
                $fieldsToUpdate = ['distance', 'rating', 'hotel_name', 'board_basis', 'supplier', 'supplier_hotel_id', 'destination', 'meal_plans_available', 'pay_at_hotel_available', 'pay_now_available'];
                foreach ($fieldsToUpdate as $field) {
                    if (isset($hotel[$field]) && (!isset($result[$giataHotelId][$field]) || $hotel[$field] < $result[$giataHotelId][$field])) {
                        $result[$giataHotelId][$field] = $hotel[$field];
                    }
                }
            }

            // Обновляем lowest_priced_room_group
            if (!isset($result[$giataHotelId]['lowest_priced_room_group']) || $hotel['lowest_priced_room_group'] < $result[$giataHotelId]['lowest_priced_room_group']) {
                $result[$giataHotelId]['lowest_priced_room_group'] = $hotel['lowest_priced_room_group'];
            }

            // Обрабатываем room_groups
            foreach ($hotel['room_groups'] as $roomGroup) {
                $existingGroup = $this->findExistingRoomGroup($result[$giataHotelId]['room_groups'], $roomGroup);

                if ($existingGroup !== null) {
                    // Обновляем существующую группу, если новая цена ниже
                    if ($roomGroup['total_net'] < $existingGroup['total_net']) {
                        $existingGroupIndex = array_search($existingGroup, $result[$giataHotelId]['room_groups']);
                        $result[$giataHotelId]['room_groups'][$existingGroupIndex] = $roomGroup;
                        $result[$giataHotelId]['room_groups'][$existingGroupIndex]['rooms'] = array_merge(
                            $existingGroup['rooms'],
                            $roomGroup['rooms']
                        );
                    }
                } else {
                    // Добавляем новую группу
                    $result[$giataHotelId]['room_groups'][] = $roomGroup;
                }
            }
        }

        // Финальная обработка
        foreach ($result as &$hotel) {
            // Сортируем room_groups по total_net
            usort($hotel['room_groups'], function($a, $b) {
                return $a['total_net'] <=> $b['total_net'];
            });
        }

        return array_values($result);
    }

    private function findExistingRoomGroup($roomGroups, $newGroup)
    {
        foreach ($roomGroups as $group) {
            if ($group['rate_id'] == $newGroup['rate_id']) {
                return $group;
            }
        }
        return null;
    }

    private function mergeRates($existingRates, $newRates)
    {
        if (empty($existingRates)) {
            return $newRates;
        }
        if (empty($newRates)) {
            return $existingRates;
        }
        $mergedRates = array_unique(array_merge(
            explode(',', $existingRates),
            explode(',', $newRates)
        ));
        return implode(',', $mergedRates);
    }
}
