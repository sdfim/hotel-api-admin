<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

class PropertyContentCall
{
    // Path
    private const PROPERTY_CONTENT_PATH = 'v3/properties/content';

    // Headers
    private const LINK = 'Link';

    private const PAGINATION_TOTAL_RESULTS = 'Pagination-Total-Results';

    // Query parameters keys
    private const LANGUAGE = 'language';

    private const SUPPLY_SOURCE = 'supply_source';

    private const COUNTRY_CODE = 'country_code';

    private const CATEGORY_ID_EXCLUDE = 'category_id_exclude';

    private const TOKEN = 'token';

    private const INCLUDE = 'include';

    private const PROPERTY_RATING_MIN = 'property_rating_min';

    private const PROPERTY_RATING_MAX = 'property_rating_max';

    private const MAX_EXECUTION_COUNT = 200;

    private const MAX_EXECUTION_TIME = 120; // seconds

    // Call parameters
    private ?RapidClient $client;

    private mixed $language;

    private mixed $supplySource;

    private mixed $countryCodes;

    private mixed $categoryIdExcludes;

    /**
     * @var float|mixed
     */
    private mixed $propertyRatingMin;

    /**
     * @var float|mixed
     */
    private mixed $propertyRatingMax;

    /**
     * @var string|mixed
     */
    private mixed $token;

    public function __construct($client, $property)
    {
        $this->client = $client;
        $this->language = $property['language'];
        $this->supplySource = $property['supplySource'];
        $this->countryCodes = $property['countryCodes'];
        $this->categoryIdExcludes = $property['categoryIdExcludes'];
        $this->propertyRatingMin = $property['propertyRatingMin'] ?? 3.3;
        $this->propertyRatingMax = $property['propertyRatingMax'] ?? 5.0;
    }

    public function stream(): array
    {
        $results = [];
        $count = 0;
        $startTime = microtime(true); // Record the start time

        $ids = [];

        while (true) {
            // Make the call to Rapid.
            $response = $this->client->get(self::PROPERTY_CONTENT_PATH, $this->queryParameters());

            dump('queryParameters', $this->queryParameters());

            // Read the response to return.
            $propertyContents = $response->getBody()->getContents(); // Read the stream contents as a string

            // Store the token for pagination if we got one.
            $this->token = $this->getTokenFromLink($response->getHeaderLine(self::LINK));

            if (empty($propertyContents)) {
                break; // Exit the loop when there's no more data to fetch
            }

            $count++;
            // dump('$propertyContents', current((array)json_decode($propertyContents))->property_id, array_keys((array)json_decode($propertyContents)));

            $ids = array_merge($ids, array_keys((array) json_decode($propertyContents)));
            // $uniqueArray = array_unique($ids);
            // dump('$count', $count, count(json_decode($propertyContents, true)), $uniqueArray);
            dump('$count', $count, count(json_decode($propertyContents, true)));

            // Check the elapsed time and exit if it exceeds the maximum allowed time
            $elapsedTime = microtime(true) - $startTime;

            dump('$elapsedTime', $elapsedTime);

            if ($elapsedTime > self::MAX_EXECUTION_TIME || $count > self::MAX_EXECUTION_COUNT) {
                break;
            }

            // Append the contents to the results array
            $results[] = $propertyContents;
            // $results = array_merge($results, array_values($propertyContents));
        }

        return $results;
    }

    public function size(): int
    {
        // Make the call to Rapid.
        $queryParameters = $this->queryParameters();
        $queryParameters[self::INCLUDE] = 'property_ids';
        $response = $this->client->get(self::PROPERTY_CONTENT_PATH, $queryParameters);

        // Read the size to return.
        // Close the response since we're not reading it.
        // $response->close();

        return intval($response->getHeaderLine(self::PAGINATION_TOTAL_RESULTS));
    }

    private function queryParameters(): array
    {
        $queryParams = [];

        if ($this->token !== null) {
            $queryParams[self::TOKEN] = $this->token;
        } else {
            // Add required parameters
            $queryParams[self::LANGUAGE] = $this->language;
            $queryParams[self::SUPPLY_SOURCE] = $this->supplySource;

            // Add optional parameters
            if (! empty($this->countryCodes)) {
                $queryParams[self::COUNTRY_CODE] = $this->countryCodes;
            }
            if (! empty($this->categoryIdExcludes)) {
                $queryParams[self::CATEGORY_ID_EXCLUDE] = $this->categoryIdExcludes;
            }
            if (! empty($this->propertyRatingMin)) {
                $queryParams[self::PROPERTY_RATING_MIN] = $this->propertyRatingMin;
            }
            if (! empty($this->propertyRatingMax)) {
                $queryParams[self::PROPERTY_RATING_MAX] = $this->propertyRatingMax;
            }
        }

        return $queryParams;
    }

    private function getTokenFromLink($linkHeader): ?string
    {
        if (empty($linkHeader)) {
            return null;
        }

        $startOfToken = strpos($linkHeader, '=') + 1;
        $endOfToken = strpos($linkHeader, '>');

        // dd($linkHeader, $startOfToken, $endOfToken, substr($linkHeader, $startOfToken, $endOfToken - $startOfToken));

        return substr($linkHeader, $startOfToken, $endOfToken - $startOfToken);
    }
}
