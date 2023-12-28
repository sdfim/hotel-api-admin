<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Models\GiataGeography;
use App\Models\IcePortalPropery;
use App\Models\MapperIcePortalGiata;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\DTO\IcePortalAssetDto;
use Modules\API\Suppliers\IceSuplier\IceHBSIClient;

class IcePortalHotelApiHandler
{
    private IceHBSIClient $client;

    private const RESULT_PER_PAGE = 500;

    private const PAGE = 1;

    private const RATING = 4;

    private const ICE_MTYPE = 34347;

    public function __construct()
    {
        $this->client = new IceHBSIClient();
    }

    public function search(array $filters): array
    {
        $geografyData = GiataGeography::where('city_id', $filters['destination'])->first();

        $results = ['$results' => [], 'count' => '0'];

        $response = $this->client->get('/v1/listings', [
            'mType' => self::ICE_MTYPE,
            'countryCode' => $geografyData->country_code ?? 'US',
            'city' => $geografyData->city_name ?? 'New York',
            'info' => 'full',
            'includeSignaturePhoto' => 'true',
            'isPublished' => 'true',
            'propertyType' => $filters['type'] ?? 'hotel',
            'page' => $filters['page'] ?? self::PAGE,
            'pageSize' => isset($filters['results_per_page']) && $filters['results_per_page'] <= 500 ?
                $filters['results_per_page'] : self::RESULT_PER_PAGE,
        ]);

        if ($response->successful()) {
            $results = $response->json();

            $ids = array_column($results['results'], 'listingID');
            $existingProperties = IcePortalPropery::whereIn('code', $ids)->get();
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
                $resultsFromIseAsync = $this->fetchHotelAssets($missingProperties);
            }

            $results['results'] = array_merge($resultsFromIseAsync['results'], $results['results']);

        } else {
            Log::error('IceHBSIClient | search | error', [
                'response' => $response->json(),
                'error' => $response->serverError(),
            ]);
        }

        return $results;
    }

    /**
     * @param array $results
     * @return array
     */
    public function fetchHotelAssets(array $results): array
    {
        $responses = Http::pool(function (Pool $pool) use ($results) {
            Log::info('IceHBSIClient | search | results', $results);
            foreach ($results['results'] as $key => $result) {
                $pool->withToken($this->client->fetchToken())
                    ->get($this->client->url('/v1/listings/'.$result['listingID'].'/assets'), [
                        'includeDisabledAssets' => 'true',
                        'includeNotApprovedAssets' => 'true',
                        'page' => '1',
                        'pageSize' => '100',
                    ]);
            }
        });

        $icePortalAssetDto = new IcePortalAssetDto();
        $batch = [];
        foreach ($responses as $key => $response) {

            $responseData = $response->json();
            Log::info('IceHBSIClient | search | response', [
                'response' => $responseData,
                'key' => $key,
            ]);
            $asset = $icePortalAssetDto->IcePortalToAssets($responseData['results']);
            if (isset($results['results'][$key])) {
                if (! isset($results['results'][$key]['listingID'])) continue;

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
            IcePortalPropery::insert($batch);
        } catch (\Exception $e) {
            Log::error('IceHBSIClient | search | error', [
                'message' => $e->getMessage(),
                'error' => $e->getTraceAsString(),
            ]);
        }

        return $results;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detail(Request $request): array
    {
        $id = MapperIcePortalGiata::where('giata_id', $request->get('property_id'))->first();

        if (! $id) {
            return [];
        }

        $response = $this->client->get('/v1/listings/'.$id->ice_portal_id.'/', [
            'mType' => self::ICE_MTYPE,
        ]);

        $results = [];
        if ($response->successful()) {
            $results = $response->json();
        } else {
            Log::error('IceHBSIClient | search | error', [
                'response' => $response->json(),
                'error' => $response->serverError(),
            ]);
        }

        return $results;
    }
}
