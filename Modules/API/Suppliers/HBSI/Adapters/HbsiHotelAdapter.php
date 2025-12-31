<?php

namespace Modules\API\Suppliers\HBSI\Adapters;

use App\Models\HbsiProperty;
use App\Models\Mapping;
use App\Repositories\HbsiRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\Services\HotelCombinationService;
use Modules\API\Suppliers\Base\Adapters\BaseHotelAdapter;
use Modules\API\Suppliers\Contracts\Hotel\Booking\HotelServiceSupplierInterface;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelContentV1SupplierInterface;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelPricingSupplierInterface;
use Modules\API\Suppliers\HBSI\Client\HbsiClient;
use Modules\API\Suppliers\HBSI\Transformers\HbsiHotelPricingTransformer;
use Modules\Enums\SupplierNameEnum;

class HbsiHotelAdapter extends BaseHotelAdapter implements HotelContentV1SupplierInterface, HotelPricingSupplierInterface, HotelServiceSupplierInterface
{
    const TTL_CACHE_COMBINATION_ITEMS = 60 * 24;

    public function __construct(
        private readonly HbsiClient $hbsiClient,
        private readonly HbsiHotelPricingTransformer $HbsiHotelPricingTransformer,
    ) {}

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::HBSI;
    }

    // Content V1
    public function getResults(array $giataCodes): array {}

    public function getRoomsData(int $giataCode): array
    {
        $roomsData = [];
        $hbsiCode = Mapping::where('giata_id', $giataCode)
            ->where('supplier', SupplierNameEnum::HBSI->value)
            ->first()?->supplier_id;

        $hbsiData = HbsiProperty::where('hotel_code', $hbsiCode)->first();
        $hbsiData = $hbsiData ? $hbsiData->toArray() : [];

        $mappingRooms = Arr::get($hbsiData, 'tpa_extensions.InterfaceSetup', []);
        $mapping = [];
        foreach ($mappingRooms as $room) {
            if (isset($room['key']) && $room['key'] === 'Mapping_Roomtype') {
                $mapping[$room['value']] = $room['text'];
            }
        }
        $roomTypes = Arr::get($hbsiData, 'roomtypes', []);

        foreach ($roomTypes as $room) {
            $description = '';
            if (isset($room['details']) && is_array($room['details'])) {
                foreach ($room['details'] as $detail) {
                    if (isset($detail['key']) && $detail['key'] === 'Description_ENG') {
                        $description = $detail['value'];
                        break;
                    }
                }
            }
            $roomsData[] = [
                'id' => $mapping[$room['key']] ?? '',
                'name' => $description,
                'descriptions' => $description,
            ];
        }

        return $roomsData;
    }

    public function getTaxOptions(int $giataCode): array
    {
        $hbsiCode = Mapping::where('giata_id', $giataCode)
            ->where('supplier', SupplierNameEnum::HBSI->value)
            ->first()?->supplier_id;

        $hbsiData = HbsiProperty::where('hotel_code', $hbsiCode)->first();
        $hbsiData = $hbsiData ? $hbsiData->toArray() : [];

        $taxOptions = Arr::get($hbsiData, 'tpa_extensions.Taxes', []);

        return array_values(Arr::pluck($taxOptions, 'key'));
    }

    // Pricing
    public function price(array &$filters, array $searchInspector, array $hotelData): ?array
    {
        try {
            $hotelIds = array_keys($hotelData);

            if (empty($hotelIds)) {
                return [
                    'original' => [
                        'request' => [],
                        'response' => [],
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            /** get PriceData from HBSI */
            $xmlPriceData = $this->hbsiClient->getHbsiPriceByPropertyIds($hotelIds, $filters, $searchInspector);

            if (isset($xmlPriceData['error'])) {
                return [
                    'error' => $xmlPriceData['error'],
                    'original' => [
                        'request' => '',
                        'response' => '',
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $arrayResponse = $this->object2array($response);
            if (isset($arrayResponse['Errors'])) {
                Log::error('HBSIHotelApiHandler | price ', ['supplier response' => $arrayResponse['Errors']['Error']]);
            }
            if (! isset($arrayResponse['RoomStays']['RoomStay'])) {
                return [
                    'original' => [
                        'request' => [],
                        'response' => [],
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            /**
             * Normally RoomStay is an array when several rates come from the same hotel, if only one rate comes, the
             * array becomes assoc instead of sequential, so we force it to be sequential so the foreach below does not
             * fail
             */
            $priceData = Arr::isAssoc($arrayResponse['RoomStays']['RoomStay'])
                ? [$arrayResponse['RoomStays']['RoomStay']]
                : $arrayResponse['RoomStays']['RoomStay'];

            $i = 1;
            $groupedPriceData = array_reduce($priceData, function ($result, $item) use ($hotelData, &$i) {
                $hotelCode = $item['BasicPropertyInfo']['@attributes']['HotelCode'];
                $roomCode = $item['RoomTypes']['RoomType']['@attributes']['RoomTypeCode'];
                $item['rate_ordinal'] = $i;
                $result[$hotelCode] = [
                    'property_id' => $hotelCode,
                    'hotel_name' => Arr::get($item, 'BasicPropertyInfo.@attributes.HotelName'),
                    'hotel_name_giata' => $hotelData[$hotelCode] ?? '',
                    'giata_id' => $hotelData[$hotelCode] ?? 0,
                    'rooms' => $result[$hotelCode]['rooms'] ?? [],
                ];
                if (! isset($result[$hotelCode]['rooms'][$roomCode])) {
                    $result[$hotelCode]['rooms'][$roomCode] = [
                        'room_code' => $roomCode,
                        'room_name' => $item['RoomTypes']['RoomType']['RoomDescription']['@attributes']['Name'] ?? '',
                    ];
                }
                $result[$hotelCode]['rooms'][$roomCode]['rates'][] = $item;
                $i++;

                return $result;
            }, []);

            return [
                'original' => [
                    'request' => $xmlPriceData['request'],
                    'response' => $xmlPriceData['response']->asXML(),
                ],
                'array' => $groupedPriceData,
                'total_pages' => $hotelData['total_pages'] ?? 1,
            ];

        } catch (Exception $e) {
            Log::error('HBSIHotelApiHandler Exception '.$e);
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => [
                    'request' => $xmlPriceData['request'] ?? '',
                    'response' => isset($xmlPriceData['response']) ? $xmlPriceData['response']->asXML() : '',
                ],
                'array' => [],
                'total_pages' => 0,
            ];
        } catch (GuzzleException $e) {
            Log::error('HBSIHotelApiHandler GuzzleException '.$e);
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => [
                    'request' => $xmlPriceData['request'] ?? '',
                    'response' => isset($xmlPriceData['response']) ? $xmlPriceData['response']->asXML() : '',
                ],
                'array' => [],
                'total_pages' => 0,
            ];
        }
    }

    public function object2array($object): mixed
    {
        return json_decode(json_encode($object), 1);
    }

    /**
     * Processes the raw response from the supplier (received from price),
     * transforms it into a DTO, and applies calculation logic.
     */
    public function processPriceResponse(
        array $rawResponse,
        array $filters,
        string $searchId,
        array $pricingRules,
        array $pricingExclusionRules,
        array $giataIds
    ): array {
        $supplierName = SupplierNameEnum::HBSI->value;

        // Инициализация переменных, которые были в HotelApiHandler
        $dataResponse = [];
        $clientResponse = [];
        $totalPages = [];
        $bookingItems = [];
        $countResponse = 0;
        $countClientResponse = 0;
        $dataOriginal = [];

        // $supplierResponse в HotelApiHandler теперь $rawResponse здесь
        $hbsiResponse = $rawResponse;

        $dataResponse[$supplierName] = $hbsiResponse['array'];
        $dataOriginal[$supplierName] = $hbsiResponse['original'];

        $st = microtime(true);
        // Вызов трансформера через инжектированную зависимость
        $hotelGenerator = $this->HbsiHotelPricingTransformer->HbsiToHotelResponse(
            $hbsiResponse['array'],
            $filters,
            $searchId,
            $pricingRules,
            $pricingExclusionRules,
            $giataIds
        );

        $clientResponse[$supplierName] = [];
        $count = 0;
        $hotels = [];
        foreach ($hotelGenerator as $count => $hotel) {
            $hotels[] = $hotel;
        }

        /** Enrichment Room Combinations (Логика Room Combination Service) */
        $countRooms = count($filters['occupancy']);
        if ($countRooms > 1) {
            $clientResponse[$supplierName] = $this->enrichmentRoomCombinations($hotels, $filters);
        } else {
            $clientResponse[$supplierName] = $hotels;
        }

        $bookingItems[$supplierName] = $this->HbsiHotelPricingTransformer->bookingItems ?? ($hotelGenerator['bookingItems'] ?? []);

        // Подсчет и логирование
        $countResponse += count($hbsiResponse['array']);
        $totalPages[$supplierName] = $hbsiResponse['total_pages'] ?? 0;
        $countClientResponse += count($clientResponse[$supplierName]);
        Log::info('HbsiHotelAdapter _ price _ Transformer HbsiToHotelResponse '.(microtime(true) - $st).' seconds');

        unset($hbsiResponse, $hotelGenerator, $hotels);

        // Формат возвращаемых данных соответствует тому, что ожидает HotelApiHandler
        return [
            'error' => Arr::get($rawResponse, 'error'),
            'dataResponse' => $dataResponse,
            'clientResponse' => $clientResponse,
            'countResponse' => $countResponse,
            'totalPages' => $totalPages,
            'countClientResponse' => $countClientResponse,
            'bookingItems' => $bookingItems,
            'dataOriginal' => $dataOriginal,
        ];
    }

    public function enrichmentRoomCombinations(array $input, array $filters): array
    {
        $arrayOccupancy = $this->getArrOccupancy($filters);
        foreach ($input as $hk => $hotel) {
            $result = $arr2combine = [];
            /** loop room type  (Suite, Double, etc)*/
            foreach ($hotel['room_groups'] as $rgk => $room_groups) {
                /** loop rate type  (Promo, BAR, etc)*/
                foreach ($room_groups['rooms'] as $rk => $room) {
                    if (in_array($room['supplier_room_id'], $arrayOccupancy)) {
                        $result[$room['supplier_room_id']][] = $room['booking_item'];
                    }
                }
            }
            foreach ($arrayOccupancy as $occupancy) {
                if (isset($result[$occupancy])) {
                    $arr2combine[] = $result[$occupancy];
                }
            }
            if (count($arr2combine) === count($arrayOccupancy)) {
                $sets = $this->generateCombinations(array_values($arr2combine));
                $finalResult = [];
                foreach ($sets as $set) {
                    $uuid = (string) Str::uuid();
                    $finalResult[$uuid] = $set;
                }
                $input[$hk]['room_combinations'] = $finalResult;
                foreach ($finalResult as $key => $value) {
                    $keyCache = 'room_combinations:'.$key;
                    Cache::put($keyCache, $value, now()->addMinutes(self::TTL_CACHE_COMBINATION_ITEMS));
                    Cache::put('supplier:'.$key, SupplierNameEnum::HBSI->value, now()->addMinutes(self::TTL_CACHE_COMBINATION_ITEMS));
                }
            }
        }

        return $input;
    }

    private function generateCombinations($arrays, $i = 0)
    {
        if (! isset($arrays[$i])) {
            return [];
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }
        $tmp = $this->generateCombinations($arrays, $i + 1);
        $result = [];
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge([$v], $t) :
                    [$v, $t];
            }
        }

        return $result;
    }

    public function getArrOccupancy(array $filters): array
    {
        $arrayOccupancy = [];
        foreach ($filters['occupancy'] as $key => $value) {
            $adults = $value['adults'];
            $child = 0;
            $infant = 0;
            if (isset($value['children_ages']) && ! empty($value['children_ages'])) {
                foreach ($value['children_ages'] as $kid => $childAge) {
                    if ($childAge <= 2) {
                        $infant++;
                    } else {
                        $child++;
                    }
                }
            }
            $arrayOccupancy[] = "$adults-$child-$infant";
        }

        return $arrayOccupancy;
    }
}
