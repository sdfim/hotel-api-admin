<?php

namespace App\Repositories;

use App\Models\GiataProperty;
use App\Models\MapperHbsiGiata;
use App\Traits\Timer;
use Illuminate\Support\Facades\Log;
use Modules\API\Tools\GiataPropertySearch;

class GiataPropertyRepository
{
    use Timer;

    private const BATCH_SIZE = 50;

    private const MIN_PERC = 65;

    private bool $availableElasticSearch;

    private GiataPropertySearch $giataPropertySearch;

    private array $batchHbsi = [];

    private array $listBatchHbsi = [];

    public function __construct(
    ) {
    }

    /**
     * @param string $hotelName
     * @param float $latitude
     * @return array
     */
    public function search(string $hotelName, float $latitude): array
    {
        $hotelNameSearch = str_replace(['&', '~', '(', ')', '@', '*', '+', ',', '-', 'The', 'Hotel', '  '], '', $hotelName);

        $latitude = bcdiv(strval($latitude), '1', 1);

        if ($this->availableElasticSearch) {
            return $this->giataPropertySearch->search($hotelName, $latitude);
        }

        return GiataProperty::where('latitude', 'like', $latitude.'%')
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

        if ($supplier == 'HBSI') {

            $hotelsIds = array_column($supplierData, 'listingID');

            $mapperHbsiGiataTable = MapperHbsiGiata::whereIn('hbsi_id', $hotelsIds)->get();
            $mapperHbsiGiata = [];
            foreach ($mapperHbsiGiataTable as $item) {
                $mapperHbsiGiata[$item->getRawOriginal('hbsi_id')] = [
                    'giata_code' => $item->getRawOriginal('giata_id'),
                    'perc' => $item->getRawOriginal('perc'),
                ];
            }

            foreach ($supplierData as &$hotel) {
                if (isset($mapperHbsiGiata[$hotel['listingID']])) {
                    $hotel['giata_id'] = $mapperHbsiGiata[$hotel['listingID']]['giata_code'];
                    $hotel['perc'] = $mapperHbsiGiata[$hotel['listingID']]['perc'];
                } else {
                    if (! isset($hotel['address']['latitude'])) {
                        continue;
                    }
                    $res = $this->getGiataCode('HBSI', (int) $hotel['listingID'], $hotel['name'], floatval($hotel['address']['latitude']));
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
     * @param bool $isElasticType
     * @return array
     */
    public function getGiataCode(string $supplier, int $id, string $hotelName, float $latitude, bool $isElasticType = true): array
    {
        $this->start();
        $giata = $this->search($hotelName, $latitude);

        $perc = 0;
        $code = 0;
        $resName = '';
        foreach ($giata as $item) {
            similar_text($item['name'], $hotelName, $perc1);
            if ($perc1 > $perc) {
                $perc = $perc1;
                $code = $item['code'];
                $resName = $item['name'];
            }
        }

        if ($supplier == 'HBSI' && ! in_array($id.'_'.$code, $this->listBatchHbsi)) {
            $this->batchHbsi[] = [
                'hbsi_id' => $id,
                'giata_id' => $code,
                'perc' => $perc,
            ];
            $this->listBatchHotels[] = $id.'_'.$code;
        }

        Log::debug('GiataPropertyRepository | getGiataCode | runtime '.$this->duration(), [
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
        if ($insertAnyway || count($this->batchHbsi) > self::BATCH_SIZE) {
            // InsertBatchData::dispatch($this->batchBooking, $this->batchHotels);
            MapperHbsiGiata::insert($this->batchHbsi);
            $this->batchHbsi = [];
        }
    }

    /**
     * @return void
     */
    public function logSearchType(): void
    {
        $searchType = match ($this->availableElasticSearch) {
            true => 'ElasticSearch/OpenSearch',
            false => 'Eloquent'
        };
    }
}
