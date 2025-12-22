<?php

namespace Modules\API\Controllers\ApiHandlers\HotelSuppliers;

use App\Repositories\HbsiRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\API\Services\HotelCombinationService;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\API\Suppliers\Transformers\HBSI\HbsiHotelPricingTransformer;
use Modules\API\Tools\Geography;
use Modules\Enums\SupplierNameEnum;
use Throwable;

class HbsiHotelController implements HotelSupplierInterface
{
    private const RESULT_PER_PAGE = 1000;

    private const PAGE = 1;

    public function __construct(
        private readonly HbsiClient $hbsiClient,
        private readonly Geography $geography,
        private readonly HbsiHotelPricingTransformer $HbsiHotelPricingTransformer,
    ) {}

    public function preSearchData(array &$filters, string $initiator = 'price'): ?array
    {
        $timeStart = microtime(true);

        $limit = $filters['results_per_page'] ?? self::RESULT_PER_PAGE;
        $offset = $filters['page'] ?? self::PAGE;

        if (isset($filters['giata_ids'])) {
            $ids = HbsiRepository::getIdsByGiataIds($filters['giata_ids'], $limit, $offset);
        } elseif (isset($filters['place']) && ! isset($filters['session'])) {
            $ids = HbsiRepository::getIdsByGiataPlace($filters['place'], $limit, $offset);
        } elseif (isset($filters['destination'])) {
            $ids = HbsiRepository::getIdsByDestinationGiata($filters['destination'], $limit, $offset);
        } elseif (isset($filters['session'])) {
            $geoLocationTime = microtime(true);
            $geoLocation = $this->geography->getPlaceDetailById($filters['place'], $filters['session']);
            $endTime = microtime(true) - $geoLocationTime;
            Log::info('HbsiHotelController | preSearchData | geoLocation '.$endTime.' seconds');

            $coordinateTime = microtime(true);
            $minMaxCoordinate = $this->geography->calculateBoundingBox($geoLocation['latitude'], $geoLocation['longitude'], $filters['radius']);
            $endTime = microtime(true) - $coordinateTime;
            Log::info('HbsiHotelController | preSearchData | minMaxCoordinate '.$endTime.' seconds');

            $filters['latitude'] = $geoLocation['latitude'];
            $filters['longitude'] = $geoLocation['longitude'];

            $idsTime = microtime(true);
            $ids = HbsiRepository::getIdsByCoordinate($minMaxCoordinate, $limit, $offset, $filters);
            $endTime = microtime(true) - $idsTime;
            Log::info('HbsiHotelController | preSearchData | ids '.$endTime.' seconds');
        } else {
            $minMaxCoordinate = $this->geography->calculateBoundingBox($filters['latitude'], $filters['longitude'], $filters['radius']);
            $ids = HbsiRepository::getIdsByCoordinate($minMaxCoordinate, $limit, $offset, $filters);
        }

        $endTime = microtime(true) - $timeStart;
        Log::info('HbsiHotelController | preSearchData | mysql query '.$endTime.' seconds');

        return array_column($ids['data'], 'giata', 'hbsi');
    }

    /**
     * @throws Throwable
     */
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

    public function search(array $filters): array
    {
        return [];
    }

    public function detail(Request $request): array|object
    {
        return [];
    }

    /**
     * Обрабатывает сырой ответ от поставщика (полученный из price),
     * трансформирует его в DTO и применяет логику комбинаций и подсчета.
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
            // Инстанцирование сервиса, как это было в handlePriceSupplier
            $hotelService = new HotelCombinationService($supplierName);
            $clientResponse[$supplierName] = $hotelService->enrichmentRoomCombinations($hotels, $filters);
        } else {
            $clientResponse[$supplierName] = $hotels;
        }

        $bookingItems[$supplierName] = $this->HbsiHotelPricingTransformer->bookingItems ?? ($hotelGenerator['bookingItems'] ?? []);

        // Подсчет и логирование
        $countResponse += count($hbsiResponse['array']);
        $totalPages[$supplierName] = $hbsiResponse['total_pages'] ?? 0;
        $countClientResponse += count($clientResponse[$supplierName]);
        Log::info('HbsiHotelController _ price _ Transformer HbsiToHotelResponse '.(microtime(true) - $st).' seconds');

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
}
