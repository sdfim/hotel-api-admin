<?php

namespace App\Repositories;

use App\Models\Property;
use App\Models\Mapping;
use App\Traits\Timer;
use Illuminate\Support\Facades\Log;
use Modules\API\Tools\PropertySearch;
use Modules\Enums\SupplierNameEnum;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class PropertyRepository
{
    use Timer;

    private const BATCH_SIZE = 200;

    private const MIN_PERC = 65;

    private bool $availableElasticSearch;

    private PropertySearch $propertySearch;

    private array $batchIcePortal = [];

    private array $listBatchHbsi = [];

    public function __construct()
    {
    }

    public function search(string $hotelName, float $latitude, string $city): array
    {
        $hotelName = explode(', ', $hotelName)[0];
        $hotelNameSearch = str_replace(['&', '~', '(', ')', '@', '*', '+', ',', '-', 'The', 'Hotel', '  '], '', $hotelName);

        $latitude = bcdiv(strval($latitude), '1', 1);

        if ($this->availableElasticSearch) {
            return $this->propertySearch->search($hotelName, $latitude, $city);
        }

        return Property::where('latitude', 'like', $latitude.'%')
            ->whereRaw('MATCH(name) AGAINST(? IN BOOLEAN MODE)', $hotelNameSearch)
            ->get()
            ->toArray();
    }

    public function associateByGiata(array $supplierData, string $supplier): array
    {
        $this->propertySearch = new PropertySearch();
        $this->availableElasticSearch = $this->propertySearch->available();

        if ($supplier == SupplierNameEnum::ICE_PORTAL->value) {

            $hotelsIds = array_column($supplierData, 'listingID');

            $mapperIcePortalGiataTable = Mapping::icePortal()->whereIn('supplier_id', $hotelsIds)->get();
            $mapperIcePortalGiata = [];
            foreach ($mapperIcePortalGiataTable as $item) {
                $mapperIcePortalGiata[$item->getRawOriginal('supplier_id')] = [
                    'giata_code' => $item->getRawOriginal('giata_id'),
                    'perc' => $item->getRawOriginal('match_percentage'),
                ];
            }

            foreach ($supplierData as &$hotel) {
                if (isset($mapperIcePortalGiata[$hotel['listingID']])) {
                    $hotel['giata_id'] = $mapperIcePortalGiata[$hotel['listingID']]['giata_code'];
                    $hotel['perc'] = $mapperIcePortalGiata[$hotel['listingID']]['perc'];
                } else {
                    $res = $this->getGiataCode(
                        'ICE_PORTAL',
                        (int) $hotel['listingID'],
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

    public function getGiataCode(string $supplier, int $id, string $hotelName, float $latitude, string $city): array
    {
        $this->start();
        $hotelName = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $hotelName);
        $giata = $this->search($hotelName, $latitude, $city);
        if (empty($giata)) {
            $giata = $this->search($hotelName, 0, $city);
        }

        $perc = 0;
        $code = '';
        $resName = '';
        foreach ($giata as $item) {
            similar_text(strtolower($item['name']), strtolower($hotelName), $perc1);
            if ($perc1 > $perc) {
                $perc = $perc1;
                $code = $item['code'];
                $resName = $item['name'];
            }
        }

        if ($supplier == 'ICE_PORTAL' && ! in_array($id.'_'.$code, $this->listBatchHbsi) && $perc !== 0) {
            $this->batchIcePortal[] = [
                'supplier_id' => $id,
                'supplier' => MappingSuppliersEnum::IcePortal->value,
                'giata_id' => $code,
                'match_percentage' => $perc,
            ];
            $this->listBatchHotels[] = $id.'_'.$code;
        }

        Log::debug('PropertyRepository | getGiataCode | runtime '.$this->duration(), [
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

    private function insertBatch(bool $insertAnyway = false): void
    {
        if ($insertAnyway || count($this->batchIcePortal) > self::BATCH_SIZE) {
            // InsertBatchData::dispatch($this->batchBooking, $this->batchHotels);
            Mapping::insert($this->batchIcePortal);
            $this->batchIcePortal = [];
        }
    }

    public function getCityIdByCoordinate(array $minMaxCoordinate): ?int
    {
        return Property::where('latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where('latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where('longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where('longitude', '<', $minMaxCoordinate['max_longitude'])
            ->first()
            ->city_id;
    }
}
