<?php

namespace Modules\API\Controllers\ApiHandlers\PricingSuppliers;

use App\Repositories\GiataPropertyRepository;
use App\Repositories\HbsiRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\API\Tools\Geography;

class HbsiHotelController
{
    /**
     *
     */
    private const RESULT_PER_PAGE = 1000;

    /**
     *
     */
    private const PAGE = 1;

    /**
     * @param HbsiClient $hbsiClient
     * @param Geography $geography
     * @param GiataPropertyRepository $giataRepo
     */
    public function __construct(
        private readonly HbsiClient $hbsiClient = new HbsiClient(),
        private readonly Geography  $geography = new Geography(),
        private readonly GiataPropertyRepository $giataRepo = new GiataPropertyRepository(),
    )
    {
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function preSearchData(array $filters): ?array
    {
        $timeStart = microtime(true);

        $limit = $filters['results_per_page'] ?? self::RESULT_PER_PAGE;
        $offset = $filters['page'] ?? self::PAGE;

        if (isset($filters['destination'])) {
            $ids = HbsiRepository::getIdsByDestinationGiata($filters['destination'], $limit, $offset);
        } else {
            $minMaxCoordinate = $this->geography->calculateBoundingBox($filters['latitude'], $filters['longitude'], $filters['radius']);
            $cityId = $this->giataRepo->getCityIdByCoordinate($minMaxCoordinate);
            $ids = HbsiRepository::getIdsByDestinationGiata($cityId, $limit, $offset);
        }

        $endTime = microtime(true) - $timeStart;
        Log::info('HbsiHotelController | preSearchData | mysql query ' . $endTime . ' seconds');

        return $ids;
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function price(array $filters): ?array
    {
        try {
            $hotelData = $this->preSearchData($filters);
            $hotelIds = array_keys($hotelData);

            // get PriceData from HBSI
            $xmlPriceData = $this->hbsiClient->getHbsiPriceByPropertyIds($hotelIds, $filters);

            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $priceData = $this->object2array($response->RoomStays)['RoomStay'];


            $i = 1;
            $groupedPriceData = array_reduce($priceData, function ($result, $item) use ($hotelData, &$i) {
                $hotelCode = $item['BasicPropertyInfo']['@attributes']['HotelCode'];
                $roomCode = $item['RoomTypes']['RoomType']['@attributes']['RoomTypeCode'];
                $item['rate_ordinal'] = $i;
                $result[$hotelCode] = [
                    'property_id' => $hotelCode,
                    'hotel_name' => $item['BasicPropertyInfo']['@attributes']['HotelName'],
                    'hotel_name_giata' => $hotelData[$hotelCode]['name'] ?? '',
                    'giata_id' => $hotelData[$hotelCode]['giata'] ?? 0,
                    'rooms' => $result[$hotelCode]['rooms'] ?? [],
                ];
                if (!isset($result[$hotelCode]['rooms'][$roomCode])) {
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
            ];

        } catch (Exception $e) {
            Log::error('ExpediaHotelApiHandler Exception ' . $e->getMessage());
            return [
                'original' => [
                    'request' => $xmlPriceData['request'] ?? '',
                    'response' => isset($xmlPriceData['response']) ? $xmlPriceData['response']->asXML() : '',
                ],
                'array' => [],
            ];
        } catch (GuzzleException $e) {
            Log::error('ExpediaHotelApiHandler GuzzleException ' . $e->getMessage());
            return [
                'original' => [
                    'request' => $xmlPriceData['request'] ?? '',
                    'response' => isset($xmlPriceData['response']) ? $xmlPriceData['response']->asXML() : '',
                ],
                'array' => [],
            ];
        }
    }

    /**
     * @param $object
     * @return mixed
     */
    public function object2array($object): mixed
    {
        return json_decode(json_encode($object), 1);
    }
}
