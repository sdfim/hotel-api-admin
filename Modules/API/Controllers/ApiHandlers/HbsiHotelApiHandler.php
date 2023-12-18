<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Models\GiataGeography;
use App\Models\MapperHbsiGiata;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\DTO\IcePortalAssetDto;
use Modules\API\Suppliers\IceSuplier\IceHBSIClient;

class HbsiHotelApiHandler
{
    private IceHBSIClient $client;

    private const RESULT_PER_PAGE = 1000;

    private const PAGE = 1;

    private const RATING = 4;

    private const BHSI_MTYPE = 34347;

    public function __construct()
    {
        $this->client = new IceHBSIClient();
    }

    /**
     * @param array $filters
     * @return array
     */
    public function search(array $filters): array
    {
        $geografyData = GiataGeography::where('city_id', $filters['destination'])->first();

        $results = ['$results' => [], 'count' => '0'];

        $response = $this->client->get('/v1/listings', [
            'mType' => self::BHSI_MTYPE,
            'countryCode' => $geografyData->country_code ?? 'US',
            'city' => $geografyData->city_name ?? 'New York',
            'info' => 'full',
            'includeSignaturePhoto' => 'true',
            'propertyType' => $filters['type'] ?? 'hotel',
            'page' => $filters['page'] ?? self::PAGE,
            'pageSize' => $filters['results_per_page'] ?? self::RESULT_PER_PAGE,
        ]);

        if ($response->successful()) {
            $results = $response->json();

        // TODO: This is an asynchronous call, we need to implement it with the RabbitMQ queue for write the data in the database
         $results = $this->fetchHotelAssets($results);

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
        foreach ($responses as $key => $response) {
            $responseData = $response->json();
            Log::info('IceHBSIClient | search | response', [
                'response' => $responseData,
                'key' => $key,
            ]);
            $asset = $icePortalAssetDto->IcePortalToAssets($responseData['results']);
            if (isset($results['results'][$key])) {
                $results['results'][$key]['images'] = $asset['hotelImages'];
                $results['results'][$key]['amenities'] = $asset['hotelAmenities'];
            }
        }

        return $results;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detail(Request $request): array
    {
        $id = MapperHbsiGiata::where('giata_id', $request->get('property_id'))->first();

        if (! $id) {
            return [];
        }

        $response = $this->client->get('/v1/listings/'.$id->hbsi_id.'/', [
            'mType' => self::BHSI_MTYPE,
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
