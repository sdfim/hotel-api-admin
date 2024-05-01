<?php

namespace App\Repositories;

use App\Models\GiataProperty;
use App\Models\MapperIcePortalGiata;
use App\Traits\Timer;
use Illuminate\Support\Facades\Log;
use Modules\API\Tools\GiataPropertySearch;
use Modules\Enums\SupplierNameEnum;

class GiataPropertyRepository
{
    use Timer;

    /**
     *
     */
    private const BATCH_SIZE = 200;

    /**
     *
     */
    private const MIN_PERC = 65;

    /**
     * @var bool
     */
    private bool $availableElasticSearch;

    /**
     * @var GiataPropertySearch
     */
    private GiataPropertySearch $giataPropertySearch;

    /**
     * @var array
     */
    private array $batchIcePortal = [];

    /**
     * @var array
     */
    private array $listBatchHbsi = [];

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param string $hotelName
     * @param float $latitude
     * @param string $city
     * @return array
     */
    public function search(string $hotelName, float $latitude, string $city): array
    {
        $hotelName = explode(', ', $hotelName)[0];
        $hotelNameSearch = str_replace(['&', '~', '(', ')', '@', '*', '+', ',', '-', 'The', 'Hotel', '  '], '', $hotelName);

        $latitude = bcdiv(strval($latitude), '1', 1);

        if ($this->availableElasticSearch) {
            return $this->giataPropertySearch->search($hotelName, $latitude, $city);
        }

        return GiataProperty::where('latitude', 'like', $latitude . '%')
            ->whereRaw('MATCH(name) AGAINST(? IN BOOLEAN MODE)', $hotelNameSearch)
            ->get()
            ->toArray();
    }

    /**
     * @param array $supplierData
     * @param string $supplier
     * @return array
     */
    public function associateByGiata(array $supplierData, string $supplier): array
    {
        $this->giataPropertySearch = new GiataPropertySearch();
        $this->availableElasticSearch = $this->giataPropertySearch->available();

        if ($supplier == SupplierNameEnum::ICE_PORTAL->value) {

            $hotelsIds = array_column($supplierData, 'listingID');

            $mapperIcePortalGiataTable = MapperIcePortalGiata::whereIn('ice_portal_id', $hotelsIds)->get();
            $mapperIcePortalGiata = [];
            foreach ($mapperIcePortalGiataTable as $item) {
                $mapperIcePortalGiata[$item->getRawOriginal('ice_portal_id')] = [
                    'giata_code' => $item->getRawOriginal('giata_id'),
                    'perc' => $item->getRawOriginal('perc'),
                ];
            }

            foreach ($supplierData as &$hotel) {
                if (isset($mapperIcePortalGiata[$hotel['listingID']])) {
                    $hotel['giata_id'] = $mapperIcePortalGiata[$hotel['listingID']]['giata_code'];
                    $hotel['perc'] = $mapperIcePortalGiata[$hotel['listingID']]['perc'];
                } else {
                    $res = $this->getGiataCode(
                        'ICE_PORTAL',
                        (int)$hotel['listingID'],
                        $hotel['name'],
                        floatval($hotel['address']['latitude'] ?? 0),
                        $hotel['address']['city']
                    );
                    $this->insertBatch();
                    $hotel['giata_id'] = $res['code'];
                    $hotel['perc'] = $res['perc'];
                }
            }
        }

        $this->insertBatch(true);

        return $supplierData;
    }

    /**
     * @param string $supplier
     * @param int $id
     * @param string $hotelName
     * @param float $latitude
     * @param string $city
     * @return array
     */
    public function getGiataCode(string $supplier, int $id, string $hotelName, float $latitude, string $city): array
    {
        $this->start();
        $hotelName = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $hotelName);
        $giata = $this->search($hotelName, $latitude, $city);
        if (empty($giata)) $giata = $this->search($hotelName, 0, $city);

        $perc = 0;
        $code = 0;
        $resName = '';
        foreach ($giata as $item) {
            similar_text(strtolower($item['name']), strtolower($hotelName), $perc1);
            if ($perc1 > $perc) {
                $perc = $perc1;
                $code = $item['code'];
                $resName = $item['name'];
            }
        }

        if ($supplier == 'ICE_PORTAL' && !in_array($id . '_' . $code, $this->listBatchHbsi) && $perc !== 0) {
            $this->batchIcePortal[] = [
                'ice_portal_id' => $id,
                'giata_id' => $code,
                'perc' => $perc,
            ];
            $this->listBatchHotels[] = $id . '_' . $code;
        }

        Log::debug('GiataPropertyRepository | getGiataCode | runtime ' . $this->duration(), [
            'supplier' => $supplier,
            'count' => count($giata),
            'id' => $id,
            'hotelName' => $hotelName,
            'resName' => $resName,
            'latitude' => $latitude,
            'result' => [
                'code' => $code,
                'perc' => $perc,
            ],
        ]);

        return ['code' => $code, 'perc' => $perc];
    }

    /**
     * @param bool $insertAnyway
     * @return void
     */
    private function insertBatch(bool $insertAnyway = false): void
    {
        if ($insertAnyway || count($this->batchIcePortal) > self::BATCH_SIZE) {
            // InsertBatchData::dispatch($this->batchBooking, $this->batchHotels);
            MapperIcePortalGiata::insert($this->batchIcePortal);
            $this->batchIcePortal = [];
        }
    }

    /**
     * @return int
     */
    public function getCityIdByCoordinate(array $minMaxCoordinate): ?int
    {
        return GiataProperty::where('latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where('latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where('longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where('longitude', '<', $minMaxCoordinate['max_longitude'])
            ->first()
            ->city_id;
    }
}
