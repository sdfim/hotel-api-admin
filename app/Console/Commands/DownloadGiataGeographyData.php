<?php

namespace App\Console\Commands;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use App\Models\GiataGeography;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class DownloadGiataGeographyData extends Command
{
    /**
     * @var string
     */
    protected $signature = 'download-giata-geography-data';

    /**
     * @var string
     */
    protected $description = 'Import XML data from a URL, write to DB';

    /**
     * @var float|string
     */
    protected float|string $current_time;
    /**
     * @var array|null
     */
    protected array|null $db_country = [];
    /**
     * @var array|null
     */
    protected array|null $db_locale = [];
    /**
     * @var array|null
     */
    protected array|null $db_city = [];

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->current_time = microtime(true);

        $url = 'http://tticodes.giatamedia.com/webservice/rest/1.0/geography';

        // Define your HTTP authentication credentials
        $username = 'tticodes@godigitaldevelopment.com';
        $password = 'aw4ZD8ky';

        // Create a Guzzle HTTP client instance
        $client = new Client([
            'auth' => [$username, $password],
        ]);

        try {
            // Send an HTTP GET request with authentication
            $response = $client->get($url);

            if ($response->getStatusCode() === 200) {
                // Get the XML content from the response body
                $textXML = $response->getBody()->getContents();

                $this->parseXMLToDb($textXML);
            } else {
                $this->error('Error importing XML data. HTTP status code: ' . $response->getStatusCode());
            }
        } catch (Exception|GuzzleException $e) {
            $this->error('Error importing XML data: ' . $e->getMessage());
        }
    }

    /**
     * @param $text
     * @return void
     */
    private function parseXMLToDb($text): void
    {
        $xmlContent = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $text);

        $xml = simplexml_load_string($xmlContent);
        $json = json_encode($xml);

        $phpObj = json_decode($json, true);

        $this->info('parseXMLToDb, count: ' . count($phpObj['Countries']));

        foreach ($phpObj['Countries'] as $data) {
            foreach ($data as $country) {
                $current_country = [
                    'CountryCode' => $country['@attributes']['CountryCode'],
                    'CountryName' => $country['@attributes']['CountryName'],
                ];
                $this->db_country[] = $current_country;

                foreach ($country['Locales'] as $locale) {
                    if (is_array($locale)) {
                        foreach ($locale as $item) {
                            Log::info('DownloadGiataGeographyData parseLocale item array locale', ['locale' => $item]);
                            $this->parseLocale($item, $current_country);
                        }
                    } else {
                        Log::info('DownloadGiataGeographyData parseLocale locale', ['locale' => $locale]);
                        $this->parseLocale($locale, $current_country);
                    }
                }
            }
        }

        GiataGeography::truncate();

        $i = 0;
        $batch = [];
        foreach ($this->db_city as $country) {
            if ($i > 1000) {
                GiataGeography::insert($batch);
                $i = 0;
                $batch = [];
                $this->info('batch to DB');
            }
            $batch[] = $country;
            $i++;
        }
        GiataGeography::insert($batch);
    }

    /**
     * @param array|null $locale
     * @param array $current_country
     * @return void
     */
    private function parseLocale(array|null $locale, array $current_country): void
    {
        if (isset($locale['@attributes'])) {
            $current_locale = [
                'LocaleId' => $locale['@attributes']['LocaleId'],
                'LocaleName' => $locale['@attributes']['LocaleName'],
            ];
        } else {
            $current_locale = [
                'LocaleId' => $locale['LocaleId'] ?? null,
                'LocaleName' => $locale['LocaleName'] ?? null,
            ];
        }
        $this->db_locale[] = $current_locale;

        if (isset($locale['Cities'])) {
            foreach ($locale['Cities']['City'] as $city) {
                if (isset($city['@attributes'])) $current_city = [
                    'city_id' => $city['@attributes']['CityId'],
                    'city_name' => $city['@attributes']['CityName'],
                ];
                else {
                    $current_city = [
                        'city_id' => $city['CityId'],
                        'city_name' => $city['CityName'],
                    ];
                }
                $current_city['country_code'] = $current_country['CountryCode'];
                $current_city['country_name'] = $current_country['CountryName'];
                $current_city['locale_id'] = $current_locale['LocaleId'];
                $current_city['locale_name'] = $current_locale['LocaleName'];

                $this->db_city[] = $current_city;
            }
        }
    }
}
