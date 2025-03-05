<?php

namespace Modules\API\Controllers\ApiHandlers\ContentSuppliers;

use App\Models\GiataGeography;
use App\Models\GiataPlace;
use App\Models\IcePortalProperty;
use App\Models\IcePortalPropertyAsset;
use App\Models\Mapping;
use App\Models\Property;
use App\Repositories\IcePortalRepository;
use App\Repositories\PropertyRepository;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\IcePortalSupplier\IcePortalClient;
use Modules\API\Suppliers\Transformers\IcePortal\IcePortalAssetTransformer;
use Modules\API\Tools\Geography;

class IcePortalHotelController
{
    private const RESULT_PER_PAGE = 500;

    private const PAGE = 1;

    private const ICE_MTYPE = 34347;

    public function __construct(
        private readonly IcePortalClient $client,
        private readonly icePortalAssetTransformer $icePortalAssetTransformer,
    ) {}

    public function search(array $filters): array
    {
        if (isset($filters['giata_ids'])) {
            $giata_id = Arr::get($filters, 'giata_ids.0', '');
            $city_id = Property::where('code', $giata_id)->first()->city_id;
            $geographyData = GiataGeography::where('city_id', $city_id)->first();
        } elseif (isset($filters['session']) || isset($filters['latitude'])) {
            return [];
        } elseif (isset($filters['place'])) {
            $tticodes = GiataPlace::where('key', $filters['place'])->first()->tticodes;
            $city_id = 0;
            foreach ($tticodes as $tticode) {
                $giataData = Property::where('code', $tticode)->first();
                if ($giataData) {
                    $city_id = $giataData->city_id;
                    break;
                }
            }
            $geographyData = GiataGeography::where('city_id', $city_id)?->first();
        } elseif (isset($filters['destination'])) {
            $geographyData = GiataGeography::where('city_id', $filters['destination'])->first();
        } else {
            $geography = new Geography;
            $minMaxCoordinate = $geography->calculateBoundingBox($filters['latitude'], $filters['longitude'], $filters['radius']);
            $city_id = IcePortalRepository::getIdByCoordinate($minMaxCoordinate);
            $geographyData = GiataGeography::where('city_id', $city_id)->first();
        }

        $propertyRepository = new PropertyRepository();

        $results = IcePortalRepository::dataByCity($geographyData?->city_name);
        if (count($results) > 0 && ! request()->supplier_data) {
            return $results;
        }

        return $this->icePortalHttpRequest($geographyData, $filters, $propertyRepository);
    }

    public function icePortalHttpRequest(GiataGeography $geographyData, array $filters, PropertyRepository $propertyRepository): array
    {
        $results = ['$results' => [], 'count' => '0'];

        $ct = microtime(true);
        $response = $this->client->get('/v1/listings', [
            'mType' => self::ICE_MTYPE,
            'countryCode' => $geographyData->country_code ?? 'US',
            'city' => $geographyData->city_name ?? 'New York',
            'info' => 'full',
            'includeSignaturePhoto' => 'true',
            'isPublished' => 'true',
            'propertyType' => $filters['type'] ?? 'hotel',
            'page' => $filters['page'] ?? self::PAGE,
            'pageSize' => isset($filters['results_per_page']) && $filters['results_per_page'] <= 500 ?
                $filters['results_per_page'] : self::RESULT_PER_PAGE,
        ]);
        Log::info('IcePortalClient | search | runtime /v1/listings', [
            'runtime' => microtime(true) - $ct.' seconds',
        ]);

        if ($response->successful()) {
            $results = $response->json();

            $ids = array_column($results['results'], 'listingID');
            $existingProperties = IcePortalProperty::whereIn('code', $ids)->get();
            $existingPropertiesIds = $existingProperties->pluck('code')->toArray();

            $resultsExistingProperties = [];
            foreach ($existingProperties as $existingProperty) {
                $resultsExistingProperties[$existingProperty->code] = $existingProperty;
            }

            $missingProperties = [];
            foreach ($results['results'] as $key => $result) {
                if (! in_array($result['listingID'], $existingPropertiesIds)) {
                    $missingProperties['results'][] = $result;
                    unset($results['results'][$key]);
                } else {
                    $results['results'][$key]['images'] = $resultsExistingProperties[$result['listingID']]->images;
                    $results['results'][$key]['amenities'] = $resultsExistingProperties[$result['listingID']]->amenities;
                }
            }

            // This is an asynchronous call to fetch the hotel assets
            $resultsFromIseAsync = ['results' => []];
            if (! empty($missingProperties)) {
                $ct = microtime(true);
                $resultsFromIseAsync = $this->fetchHotelAssets($missingProperties);
                Log::info('IcePortalClient | search | runtime fetchHotelAssets', [
                    'runtime' => microtime(true) - $ct.' seconds',
                ]);
            }

            $results['results'] = array_merge($resultsFromIseAsync['results'], $results['results']);

            $results['results'] = $propertyRepository->associateByGiata($results['results'], 'ICE_PORTAL');

        } else {
            Log::error('IcePortalClient | search | error', [
                'response' => $response->json(),
                'error' => $response->serverError(),
            ]);
        }

        return $results;
    }

    public function fetchHotelAssets(array $results): array
    {
        $responses = Http::pool(function (Pool $pool) use ($results) {
            Log::info('IcePortalClient | search | results', $results);
            foreach ($results['results'] as $result) {
                $pool->withToken($this->client->fetchToken())
                    ->get($this->client->url('/v1/listings/'.$result['listingID'].'/assets'), [
                        'includeDisabledAssets' => 'true',
                        'includeNotApprovedAssets' => 'true',
                        'page' => '1',
                        'pageSize' => '100',
                    ]);
            }
        });

        $batch = [];
        foreach ($responses as $key => $response) {

            $responseData = $response->json();
            Log::info('IcePortalClient | search | response', [
                'response' => $responseData,
                'key' => $key,
            ]);
            $asset = $this->icePortalAssetTransformer->IcePortalToAssets($responseData['results']);
            if (isset($results['results'][$key])) {
                if (! isset($results['results'][$key]['listingID'])) {
                    continue;
                }

                $results['results'][$key]['images'] = $asset['hotelImages'];
                $results['results'][$key]['amenities'] = $asset['hotelAmenities'];

                $batch[] = [
                    'code' => $results['results'][$key]['listingID'],
                    'supplier_id' => $results['results'][$key]['supplierId'],
                    'name' => $results['results'][$key]['name'],
                    'city' => $results['results'][$key]['address']['city'] ?? null,
                    'state' => $results['results'][$key]['address']['state'] ?? null,
                    'country' => $results['results'][$key]['address']['country'] ?? null,
                    'addressLine1' => $results['results'][$key]['address']['addressLine1'] ?? null,
                    'phone' => $results['results'][$key]['phone'] ?? null,
                    'latitude' => $results['results'][$key]['address']['latitude'] ?? null,
                    'longitude' => $results['results'][$key]['address']['longitude'] ?? null,
                    'editDate' => $results['results'][$key]['editDate'] ?? null,
                    'amenities' => json_encode($asset['hotelAmenities']),
                    'images' => json_encode($asset['hotelImages']),
                ];
            }
        }
        try {
            IcePortalProperty::insert($batch);
        } catch (Exception $e) {
            Log::error('IcePortalClient | search | error', [
                'message' => $e->getMessage(),
                'error' => $e->getTraceAsString(),
            ]);
            Log::error($e->getTraceAsString());
        }

        return $results;
    }

    public function detail(Request $request): array
    {
        $id = Mapping::icePortal()->where('giata_id', $request->get('property_id'))->first();

        if (! $id) {
            return [];
        }

        $response = $this->client->get('/v1/listings/'.$id->supplier_id.'/', [
            'mType' => self::ICE_MTYPE,
        ]);

        $results = [];
        if ($response->successful()) {
            $results = $response->json();
        } else {
            Log::error('IcePortalClient | search | error', [
                'response' => $response->json(),
                'error' => $response->serverError(),
            ]);
        }

        return $results;
    }

    public function details(array $giataCodes): array
    {
        $dataMapper = Mapping::icePortal()->whereIn('giata_id', $giataCodes);
        $mapperItems = $dataMapper->get();
        $supplierIds = $dataMapper->pluck('supplier_id');

        // Find records in IcePortalPropertyAsset where listingID matches supplier_id values
        $existingRecords = IcePortalPropertyAsset::whereIn('listingID', $supplierIds);
        $existingRecordsIds = $existingRecords->pluck('listingID')->toArray();

        $existingRecordsArray = [];
        foreach ($existingRecords->get() as $record) {
            $giataCode = $dataMapper->firstWhere('supplier_id', $record->listingID)?->giata_id;
            if (! $giataCode) {
                continue;
            }
            $existingRecordsArray[$giataCode] = $record->toArray();
        }

        if ($mapperItems->isEmpty()) {
            return [];
        }

        $mapListingID = [];
        $responses = Http::pool(function (Pool $pool) use ($mapperItems, &$mapListingID, $existingRecordsIds) {
            foreach ($mapperItems as $mapperItem) {
                if (in_array($mapperItem->supplier_id, $existingRecordsIds)) {
                    continue;
                }
                $mapListingID[$mapperItem->supplier_id] = $mapperItem->giata_id;
                $pool->as($mapperItem->giata_id)
                    ->withToken($this->client->fetchToken())
                    ->get($this->client->url('/v1/listings/'.$mapperItem->supplier_id.'/'), [
                        'mType' => self::ICE_MTYPE,
                    ]);
            }
        });

        $results = [];
        $listingIDs = [];
        foreach ($responses as $giataId => $response) {
            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                $responseData = $response->json();
                $results[$giataId] = $responseData;
                $listingIDs[] = $responseData['listingID'];
            } else {
                Log::error('IcePortalClient | search | error', [
                    'giataId' => $giataId,
                    'response' => $response instanceof \Illuminate\Http\Client\Response ? $response->json() : null,
                    'error' => $response instanceof \Illuminate\Http\Client\Response ? $response->serverError() : 'Connection error',
                ]);
            }
        }

        $assetResponses = Http::pool(function (Pool $pool) use ($listingIDs, $mapListingID) {
            foreach ($listingIDs as $listingID) {
                $giataId = $mapListingID[$listingID];
                $pool->as($giataId)
                    ->withToken($this->client->fetchToken())
                    ->get($this->client->url('/v1/listings/'.$listingID.'/assets'), [
                        'includeDisabledAssets' => 'true',
                        'includeNotApprovedAssets' => 'true',
                        'page' => '1',
                        'pageSize' => '100',
                    ]);
            }
        });

        foreach ($assetResponses as $giataId => $assetResponse) {
            if ($assetResponse instanceof \Illuminate\Http\Client\Response && $assetResponse->successful()) {
                $results[$giataId]['assets'] = $assetResponse->json();
            } else {
                Log::error('IcePortalClient | search | error fetching assets', [
                    'giataId' => $giataId,
                    'response' => $assetResponse instanceof \Illuminate\Http\Client\Response ? $assetResponse->json() : null,
                    'error' => $assetResponse instanceof \Illuminate\Http\Client\Response ? $assetResponse->serverError() : 'Connection error',
                ]);
            }
        }

        if (! empty($results)) {
            $fields = [
                'listingID', 'type', 'name', 'supplierId', 'supplierChainCode', 'supplierMappedID', 'createdOn',
                'propertyLastModified', 'contentLastModified', 'makeLiveDate', 'makeLiveBy', 'editDate', 'editBy',
                'addressLine1', 'city', 'country', 'postalCode', 'latitude', 'longitude', 'listingClassName',
                'regionCode', 'phone', 'publicationStatus', 'publishedDate', 'roomTypes', 'meetingRooms',
                'iceListingQuantityScore', 'iceListingSizeScore', 'iceListingCategoryScore', 'iceListingRoomScore',
                'iceListingScore', 'bookingListingScore', 'assets', 'bookingURL', 'listingURL',
            ];
            try {
                $batch = [];
                foreach ($results as $result) {
                    if (isset($result['address'])) {
                        $toBatch = array_merge(array_fill_keys($fields, null), $result);
                        unset($toBatch['address']);
                        $toBatch['addressLine1'] = Arr::get($result, 'address.addressLine1');
                        $toBatch['city'] = Arr::get($result, 'address.city');
                        $toBatch['country'] = Arr::get($result, 'address.country');
                        $toBatch['postalCode'] = Arr::get($result, 'address.postalCode');
                        $toBatch['latitude'] = Arr::get($result, 'address.latitude');
                        $toBatch['longitude'] = Arr::get($result, 'address.longitude');
                        $toBatch['assets'] = json_encode(Arr::get($result, 'assets', []));
                        $toBatch['roomTypes'] = json_encode(Arr::get($result, 'roomTypes', []));
                        $toBatch['meetingRooms'] = json_encode(Arr::get($result, 'meetingRooms', []));
                        $toBatch = array_filter($toBatch, fn ($k) => in_array($k, $fields), ARRAY_FILTER_USE_KEY);
                        $toBatch['created_at'] = now();
                        $toBatch['updated_at'] = now();
                        $batch[] = $toBatch;
                    }
                }
                IcePortalPropertyAsset::insert($batch);
            } catch (Exception $e) {
                \Log::error('IcePortalHotelController::details 3 IcePortalPropertyAsset', [
                    'batch' => $batch,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            try {
                $batch = [];
                foreach ($results as $result) {
                    $asset = $this->icePortalAssetTransformer->IcePortalToAssets(Arr::get($result, 'assets.results', []));
                    $batch[] = [
                        'code' => $result['listingID'],
                        'supplier_id' => $result['supplierId'],
                        'name' => $result['name'],
                        'city' => $result['address']['city'] ?? null,
                        'state' => $result['address']['state'] ?? null,
                        'country' => $result['address']['country'] ?? null,
                        'addressLine1' => $result['address']['addressLine1'] ?? null,
                        'phone' => $result['phone'] ?? null,
                        'latitude' => $result['address']['latitude'] ?? null,
                        'longitude' => $result['address']['longitude'] ?? null,
                        'editDate' => $result['editDate'] ?? null,
                        'amenities' => json_encode($asset['hotelAmenities']),
                        'images' => json_encode($asset['hotelImages']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                IcePortalProperty::upsert($batch, ['code'], [
                    'supplier_id',
                    'name',
                    'city',
                    'state',
                    'country',
                    'addressLine1',
                    'phone',
                    'latitude',
                    'longitude',
                    'images',
                    'amenities',
                    'editDate',
                    'created_at',
                    'updated_at',
                ]);
            } catch (Exception $e) {
                \Log::error('IcePortalHotelController::details 3 IcePortalProperty ', [
                    'batch' => $batch,
                    'results' => $results,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $allRecords = $existingRecordsArray + $results;

        return $allRecords;
    }
}
