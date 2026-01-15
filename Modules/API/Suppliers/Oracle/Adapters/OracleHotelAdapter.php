<?php

namespace Modules\API\Suppliers\Oracle\Adapters;

use App\Models\Mapping;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\Base\Adapters\BaseHotelAdapter;
use Modules\API\Suppliers\Contracts\Hotel\Booking\HotelServiceSupplierInterface;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelContentV1SupplierInterface;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelPricingSupplierInterface;
use Modules\API\Suppliers\Oracle\Client\OracleClient;
use Modules\API\Suppliers\Oracle\Transformers\OracleHotelPricingTransformer;
use Modules\Enums\SupplierNameEnum;

class OracleHotelAdapter extends BaseHotelAdapter implements HotelContentV1SupplierInterface, HotelPricingSupplierInterface, HotelServiceSupplierInterface
{
    public function __construct(
        private readonly OracleClient $client,
        private readonly OracleHotelPricingTransformer $supplierHotelPricingTransformer,
    ) {}

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::ORACLE;
    }

    // Content V1
    public function getResults(array $giataCodes): array
    {
        return [];
    }

    public function getRoomsData(int $giataCode): array
    {
        return [];
    }

    // Pricing
    public function price(array &$filters, array $searchInspector, array $preSearchData, string $hotelId = ''): ?array
    {
        try {
            $hotelIds = array_keys($preSearchData);

            if (! $hotelId && empty($hotelIds)) {
                return [
                    'original' => [
                        'request' => [],
                        'response' => [],
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            /** get PriceData from Oracle */
            if (! empty($hotelIds) && ! $hotelId) {
                // async call for multiple hotels
                $clientPriceData = $this->client->getPriceByPropertyIds($hotelIds, $filters, $searchInspector);
            } else {
                // sync call for single hotel
                $clientPriceData = $this->client->getSyncPriceByPropertyIds([$hotelId], $filters, $searchInspector);
                $giata_id = Mapping::oracle()->where('supplier_id', $hotelId)->first()->giata_id;
                $preSearchData = [$hotelId => $giata_id];
            }

            if (isset($clientPriceData['error'])) {
                return [
                    'error' => $clientPriceData['error'],
                    'original' => [
                        'request' => '',
                        'response' => '',
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            $priceData = $clientPriceData['response'];

            $groupedPriceData = [];

            foreach ($priceData as $hotelId => $hotelData) {
                // 1. Initialize the hotel structure
                $groupedPriceData[$hotelId] = [
                    'property_id' => $hotelId,
                    'hotel_name' => Arr::get($preSearchData, 'hotel_name'),
                    'hotel_name_giata' => Arr::get($preSearchData, $hotelId),
                    'giata_id' => Arr::get($preSearchData, $hotelId),
                    'rooms' => [],
                ];

                foreach ($hotelData as $roomKey => $hotelDataInRoom) {
                    $i = 0;
                    foreach (Arr::get($hotelDataInRoom, 'hotelAvailability', []) as $availability) {

                        $masterRoomTypes = Arr::get($availability, 'masterInfo.roomTypes.roomType', []);
                        foreach ($masterRoomTypes as $room) {
                            $roomsByType[$room['roomType']] = $room;
                        }

                        // --- Step 2: Parse Room Rates (Prices) ---
                        foreach (Arr::get($availability, 'roomStays', []) as $roomStay) {
                            foreach (Arr::get($roomStay, 'roomRates', []) as $roomRate) {
                                $roomType = Arr::get($roomRate, 'roomType');
                                $ratePlanCode = Arr::get($roomRate, 'ratePlanCode');

                                if (! isset($groupedPriceData[$hotelId]['rooms'][$roomType])) {
                                    $groupedPriceData[$hotelId]['rooms'][$roomType] = [
                                        'room_code' => $roomType,
                                        'room_name' => Arr::get($roomsByType, "$roomType.description", ''),
                                        'room_class' => Arr::get($roomsByType, "$roomType.roomClass", ''),
                                        'suite' => Arr::get($roomsByType, "$roomType.suite", ''),
                                        'component' => Arr::get($roomsByType, "$roomType.component", ''),
                                        'room_features' => Arr::get($roomsByType, "$roomType.roomFeatures", []),
                                        'rates' => [],
                                    ];
                                }

                                // Initialize room entry if it doesn't exist
                                $groupedPriceData[$hotelId]['rooms'][$roomType]['rates'][] = [
                                    'rate_ordinal' => $i++,
                                    'room_key' => $roomKey,
                                    'room_code' => $roomType,
                                    'room_name' => Arr::get($roomsByType, "$roomType.description", ''),
                                    'rate_plan_code' => $ratePlanCode,
                                    'total' => Arr::get($roomRate, 'total', []),
                                    'rates' => Arr::get($roomRate, 'rates', []),
                                    'packages' => Arr::get($roomRate, 'packages', []),
                                    'start' => Arr::get($roomRate, 'start', ''),
                                    'end' => Arr::get($roomRate, 'end', ''),
                                    'numberOfUnits' => Arr::get($roomRate, 'numberOfUnits', 0),
                                ];
                            }
                        }

                        // Ensure that roomType is always treated as an array, even if API returns a single object
                        if (! is_array($masterRoomTypes) || empty($masterRoomTypes) || array_keys($masterRoomTypes)[0] === 0) {
                            // It's a standard array of rooms or an empty array, proceed
                        } else {
                            // The API returned a single room object instead of an array (common issue in old APIs)
                            $masterRoomTypes = [$masterRoomTypes];
                        }

                        foreach ($masterRoomTypes as $masterRoom) {
                            $roomType = Arr::get($masterRoom, 'roomType');

                            $roomDetails = [
                                'room_code' => $roomType,
                                'room_name' => Arr::get($masterRoom, 'description', ''),
                                'room_class' => Arr::get($masterRoom, 'roomClass', ''),
                                'suite' => Arr::get($masterRoom, 'suite', false),
                                'component' => Arr::get($masterRoom, 'component', false),
                                // Safe access to nested attributes
                                'maximum_occupancy' => Arr::get($masterRoom, 'roomTypeAttributes.maximumOccupancy', 0),
                                'long_description' => Arr::get($masterRoom, 'longDescription', ''),
                            ];

                            if (! isset($groupedPriceData[$hotelId]['rooms'][$roomType])) {
                                // Initialize room with roomDetails and an empty rates array if not set by roomRates parsing
                                $groupedPriceData[$hotelId]['rooms'][$roomType] = array_merge($roomDetails, ['rates' => []]);
                            } else {
                                // Update existing room type with additional details from masterInfo
                                $groupedPriceData[$hotelId]['rooms'][$roomType] = array_merge(
                                    $groupedPriceData[$hotelId]['rooms'][$roomType],
                                    $roomDetails
                                );
                            }
                        }

                        // --- Step 4: Parse Master Rate Plan Details (for later enrichment) ---
                        $masterRatePlans = Arr::get($availability, 'masterInfo.ratePlans.ratePlan', []);

                        // Handle single object vs array of rate plans
                        if (! is_array($masterRatePlans) || empty($masterRatePlans) || array_keys($masterRatePlans)[0] === 0) {
                            // It's a standard array of rate plans or an empty array, proceed
                        } else {
                            // The API returned a single rate plan object instead of an array
                            $masterRatePlans = [$masterRatePlans];
                        }

                        foreach ($masterRatePlans as $ratePlan) {
                            $ratePlanCode = Arr::get($ratePlan, 'ratePlanCode');
                            $guarantees = [];

                            // Parse resGuarantees safely
                            foreach (Arr::get($ratePlan, 'resGuarantees', []) as $guarantee) {
                                $guarantees[] = [
                                    'guarantee_code' => Arr::get($guarantee, 'guaranteeCode', ''),
                                    // Safe access to nested attributes
                                    'short_description' => Arr::get($guarantee, 'shortDescription.defaultText', ''),
                                    'policy_requirements' => Arr::get($guarantee, 'policyRequirements', []),
                                    'on_hold' => Arr::get($guarantee, 'onHold', false),
                                    'reserve_inventory' => Arr::get($guarantee, 'reserveInventory', false),
                                    'order_sequence' => Arr::get($guarantee, 'orderSequence', 0),
                                    'default_guarantee' => Arr::get($guarantee, 'defaultGuarantee', false),
                                ];
                            }

                            $ratePlanDetails = [
                                'rate_plan_code' => $ratePlanCode,
                                'description' => Arr::get($ratePlan, 'description', ''),
                                'rate_plan_category' => Arr::get($ratePlan, 'ratePlanCategory', ''),
                                'rate_commission' => Arr::get($ratePlan, 'rateCommission', []),
                                'res_guarantees' => $guarantees,
                            ];

                            // Store the rate plan details in a lookup structure
                            if (! isset($groupedPriceData[$hotelId]['ratePlans'][$ratePlanCode])) {
                                $groupedPriceData[$hotelId]['ratePlans'][$ratePlanCode] = $ratePlanDetails;
                            } else {
                                // Update existing ratePlan with additional fields
                                $groupedPriceData[$hotelId]['ratePlans'][$ratePlanCode] = array_merge(
                                    Arr::get($groupedPriceData[$hotelId], "ratePlans.{$ratePlanCode}", []), // Get existing data safely
                                    $ratePlanDetails
                                );
                            }
                        }
                    }
                }
            }

            return [
                'original' => [
                    'request' => $clientPriceData['request'],
                    'response' => $clientPriceData['response'],
                ],
                'array' => $groupedPriceData,
                'total_pages' => $hotelData['total_pages'] ?? 1,
            ];

        } catch (Exception $e) {
            Log::error('OracleHotelApiHandler Exception '.$e);
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => [
                    'request' => $clientPriceData['request'] ?? '',
                    'response' => $clientPriceData['response'] ?? '',
                ],
                'array' => [],
                'total_pages' => 0,
            ];
        } catch (GuzzleException $e) {
            Log::error('OracleHotelApiHandler GuzzleException '.$e);
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => [
                    'request' => $clientPriceData['request'] ?? '',
                    'response' => $clientPriceData['response'] ?? '',
                ],
                'array' => [],
                'total_pages' => 0,
            ];
        }
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
        $supplierName = SupplierNameEnum::ORACLE->value;

        // Инициализация переменных, которые были в HotelApiHandler
        $dataResponse = [];
        $clientResponse = [];
        $totalPages = [];
        $bookingItems = [];
        $countResponse = 0;
        $countClientResponse = 0;
        $dataOriginal = [];

        // $supplierResponse в HotelApiHandler теперь $rawResponse здесь
        $supplierResponse = $rawResponse;

        $dataResponse[$supplierName] = $supplierResponse['array'];
        $dataOriginal[$supplierName] = $supplierResponse['original'];

        $st = microtime(true);
        // Вызов трансформера через инжектированную зависимость
        $hotelGenerator = $this->supplierHotelPricingTransformer->OracleToHotelResponse(
            $supplierResponse['array'],
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
            // Инстанцирование сервиса, как это было в handlePriceSupplier
            $clientResponse[$supplierName] = $this->enrichmentRoomCombinations($hotels, $filters, SupplierNameEnum::ORACLE);
        } else {
            $clientResponse[$supplierName] = $hotels;
        }

        $bookingItems[$supplierName] = $this->supplierHotelPricingTransformer->bookingItems ?? ($hotelGenerator['bookingItems'] ?? []);

        // Подсчет и логирование
        $countResponse += count($supplierResponse['array']);
        $totalPages[$supplierName] = $supplierResponse['total_pages'] ?? 0;
        $countClientResponse += count($clientResponse[$supplierName]);
        Log::info('OracleHotelAdapter _ price _ Transformer OracleToHotelResponse '.(microtime(true) - $st).' seconds');

        unset($supplierResponse, $hotelGenerator, $hotels);

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
}
