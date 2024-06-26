<?php

namespace App\Console\Commands;

use App\Models\GiataGeography;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DownloadGiataGeography extends Command
{
    /**
     * @var string
     */
    protected $signature = 'download-giata-geography';

    /**
     * @var string
     */
    protected $description = 'Import XML data from a URL, write to DB';

    protected float|string $current_time;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->current_time = microtime(true);

        $url = config('giata.main.base_uri').'geography';
        $username = config('giata.main.username');
        $password = config('giata.main.password');

        // Create a Guzzle HTTP client instance
        $client = new Client([
            'auth' => [$username, $password],
        ]);

        try {
            $textXML = Storage::disk('local')->get('giata_geography.xml');
            if ($textXML) {
                $this->parseXMLToDb($textXML);

                return;
            }
            // Send an HTTP GET request with authentication
            $response = $client->get($url);

            if ($response->getStatusCode() === 200) {
                $textXML = $response->getBody()->getContents();

                Storage::disk('local')->put('giata_geography.xml', $textXML);

                $this->parseXMLToDb($textXML);
            } else {
                $this->error('Error importing XML data. HTTP status code: '.$response->getStatusCode());
            }
        } catch (Exception|GuzzleException $e) {
            $this->error('Error importing XML data: '.$e->getMessage());
        }
    }

    private function parseXMLToDb($textXML): void
    {
        $xmlObject = simplexml_load_string($textXML);

        //        $xmlContent = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $text);

        GiataGeography::truncate();

        $batchSize = 1000; // Adjust the batch size as needed

        foreach ($xmlObject->Countries->Country as $country) {
            $countryCode = $country->attributes()->CountryCode;
            $countryName = $country->attributes()->CountryName;

            foreach ($country->Locales->Locale as $locale) {
                $localeId = $locale->attributes()->LocaleId;
                $localeName = $locale->attributes()->LocaleName;

                $cities = [];
                foreach ($locale->Cities->City as $city) {
                    $cityId = $city->attributes()->CityId;
                    $cityName = $city->attributes()->CityName;

                    $cities[] = [
                        'city_id' => (int) $cityId,
                        'city_name' => (string) $cityName,
                        'locale_id' => (int) $localeId,
                        'locale_name' => (string) $localeName,
                        'country_code' => (string) $countryCode,
                        'country_name' => (string) $countryName,
                    ];

                    if (count($cities) >= $batchSize) {
                        $this->insertCities($cities);
                        $cities = [];
                    }
                }

                // Insert remaining cities
                if (! empty($cities)) {
                    $this->insertCities($cities);
                }
            }
        }

        $this->info('Cities data imported successfully.');

    }

    private function insertCities(array $cities)
    {
        GiataGeography::insert($cities);
    }
}
