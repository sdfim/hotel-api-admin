<?php

namespace Modules\API\Suppliers\IcePortal\Client;

use App\Models\IcePortalProperty;
use App\Models\IcePortalPropertyAsset;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\IcePortal\Transformers\IcePortalAssetTransformer;

class IceHBSIClient
{
    /**
     * Number of seconds in one day.
     */
    private const ONE_DAY_IN_SECONDS = 86400; // 24 * 60 * 60

    private string $clientId;

    private string $clientSecret;

    private string $baseUrl;

    private string $tokenUrl;

    private const ICE_MTYPE = 34347;

    /**
     * @var string|null The access token.
     */
    private ?string $token = null;

    /**
     * Constructor.
     *
     * Initializes the client with configuration values.
     */
    public function __construct(
        private readonly IcePortalAssetTransformer $icePortalAssetTransformer,
    ) {
        $namespace = 'booking-suppliers.IcePortal.credentials';
        $this->clientId = config("$namespace.client_id");
        $this->clientSecret = config("$namespace.client_secret");
        $this->baseUrl = config("$namespace.base_url");
        $this->tokenUrl = config("$namespace.token_url");
    }

    /**
     * Retrieves the access token.
     *
     * @return string The access token.
     *
     * @throws Exception If unable to retrieve the token.
     */
    private function getToken(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        if (Cache::has('ice_portal_token')) {
            $this->token = Cache::get('ice_portal_token');

            return $this->token;
        }

        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        if ($response->successful()) {
            $this->token = $response->json()['access_token'];
            Cache::put('ice_portal_token', $this->token, self::ONE_DAY_IN_SECONDS);

            return $this->token;
        }

        throw new Exception('Unable to retrieve token');
    }

    public function get(string $endpoint, array $query = []): PromiseInterface|Response
    {
        return Http::withToken($this->getToken())->get($this->baseUrl.$endpoint, $query);
    }

    public function post(string $endpoint, array $data = []): PromiseInterface|Response
    {
        return Http::withToken($this->getToken())->post($this->baseUrl.$endpoint, $data);
    }

    public function pool($callback): array
    {
        return Http::pool($callback);
    }

    /**
     * @throws Exception
     */
    public function fetchToken(): string
    {
        return $this->getToken();
    }

    public function url(string $endpoint): string
    {
        return $this->baseUrl.$endpoint;
    }

    public function processListings($mapperItems, $existingRecordsIds): array
    {
        $mapListingID = [];
        $responses = Http::pool(function (Pool $pool) use ($mapperItems, &$mapListingID, $existingRecordsIds) {
            foreach ($mapperItems as $mapperItem) {
                if (in_array($mapperItem->supplier_id, $existingRecordsIds)) {
                    continue;
                }
                $mapListingID[$mapperItem->supplier_id] = $mapperItem->giata_id;
                $pool->as($mapperItem->giata_id)
                    ->withToken($this->fetchToken())
                    ->get($this->url('/v1/listings/'.$mapperItem->supplier_id.'/'), [
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
                Log::error('IceHBSIClient _ search _ error', [
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
                    ->withToken($this->fetchToken())
                    ->get($this->url('/v1/listings/'.$listingID.'/assets'), [
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
                Log::error('IceHBSIClient _ search _ error fetching assets', [
                    'giataId' => $giataId,
                    'response' => $assetResponse instanceof \Illuminate\Http\Client\Response ? $assetResponse->json() : null,
                    'error' => $assetResponse instanceof \Illuminate\Http\Client\Response ? $assetResponse->serverError() : 'Connection error',
                ]);
            }
        }

        // Fetch room types for each listing
        $roomTypeResponses = Http::pool(function (Pool $pool) use ($listingIDs, $mapListingID) {
            foreach ($listingIDs as $listingID) {
                $giataId = $mapListingID[$listingID];
                $pool->as($giataId)
                    ->withToken($this->fetchToken())
                    ->get($this->url('/v1/listings/'.$listingID.'/roomtypes'), [
                        'includeAssets' => 'true',
                        'page' => '1',
                        'pageSize' => '100',
                    ]);
            }
        });

        foreach ($roomTypeResponses as $giataId => $roomTypeResponse) {
            if ($roomTypeResponse instanceof \Illuminate\Http\Client\Response && $roomTypeResponse->successful()) {
                $results[$giataId]['roomTypes'] = $roomTypeResponse->json();
            } else {
                Log::error('IceHBSIClient _ search _ error fetching room types', [
                    'giataId' => $giataId,
                    'response' => $roomTypeResponse instanceof \Illuminate\Http\Client\Response ? $roomTypeResponse->json() : null,
                    'error' => $roomTypeResponse instanceof \Illuminate\Http\Client\Response ? $roomTypeResponse->serverError() : 'Connection error',
                ]);
            }
        }

        if (! empty($results)) {
            $this->saveListings($results);
        }

        return $results;
    }

    private function saveListings(array $results): void
    {
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
                    $toBatch['postalCode'] = Arr::get($result, 'address.postalCode', '');
                    $toBatch['latitude'] = Arr::get($result, 'address.latitude', 0.0);
                    $toBatch['longitude'] = Arr::get($result, 'address.longitude', 0.0);
                    $toBatch['assets'] = json_encode(Arr::get($result, 'assets', []));
                    $toBatch['roomTypes'] = json_encode(Arr::get($result, 'roomTypes.results') ?? Arr::get($result, 'roomTypes', []));
                    $toBatch['meetingRooms'] = json_encode(Arr::get($result, 'meetingRooms', []));
                    $toBatch['iceListingCategoryScore'] = Arr::get($result, 'iceListingCategoryScore', 0); // Default to 0 if null
                    $toBatch = array_filter($toBatch, fn ($k) => in_array($k, $fields), ARRAY_FILTER_USE_KEY);
                    $toBatch['created_at'] = now();
                    $toBatch['updated_at'] = now();
                    $batch[] = $toBatch;
                }
            }

            IcePortalPropertyAsset::upsert(
                $batch,
                ['listingID'], // Unique key(s)
                [
                    'type', 'name', 'supplierId', 'supplierChainCode', 'supplierMappedID', 'createdOn',
                    'propertyLastModified', 'contentLastModified', 'makeLiveDate', 'makeLiveBy', 'editDate', 'editBy',
                    'addressLine1', 'city', 'country', 'postalCode', 'latitude', 'longitude', 'listingClassName',
                    'regionCode', 'phone', 'publicationStatus', 'publishedDate', 'roomTypes', 'meetingRooms',
                    'iceListingQuantityScore', 'iceListingSizeScore', 'iceListingCategoryScore', 'iceListingRoomScore',
                    'iceListingScore', 'bookingListingScore', 'assets', 'bookingURL', 'listingURL', 'updated_at',
                ] // Columns to update
            );

        } catch (Exception $e) {
            \Log::error('IcePortalHotelController::details 3 IcePortalPropertyAsset', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        try {
            $batch = [];
            foreach ($results as $result) {
                $asset = $this->icePortalAssetTransformer->IcePortalToAssets(Arr::get($result, 'assets.results', []));
                $batch[] = [
                    'code' => Arr::get($result, 'listingID'),
                    'supplier_id' => Arr::get($result, 'supplierId'),
                    'name' => Arr::get($result, 'name'),
                    'city' => Arr::get($result, 'address.city'),
                    'state' => Arr::get($result, 'address.state'),
                    'country' => Arr::get($result, 'address.country'),
                    'addressLine1' => Arr::get($result, 'address.addressLine1'),
                    'phone' => Arr::get($result, 'phone'),
                    'latitude' => Arr::get($result, 'address.latitude', 0.0),
                    'longitude' => Arr::get($result, 'address.longitude', 0.0),
                    'editDate' => Arr::get($result, 'editDate'),
                    'amenities' => json_encode(Arr::get($asset, 'hotelAmenities', [])),
                    'images' => json_encode(Arr::get($asset, 'hotelImages', [])),
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
                'results' => $results,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
