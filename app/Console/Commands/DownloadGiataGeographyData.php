<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GiataGeography;
use Exception;
use GuzzleHttp\Client;

class DownloadGiataGeographyData extends Command
{
	protected $signature = 'download-giata-geography-data';
	protected $description = 'Import XML data from a URL, wrtite to DB';
	protected $current_time;
	protected $db_country = [];
	protected $db_locale = [];
	protected $db_city = [];

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
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
		} catch (Exception $e) {
			$this->error('Error importing XML data: ' . $e->getMessage());
		}
	}

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
							\Log::info('DownloadGiataGeographyData parceLocale item array locale', ['locale' => $item]);
							$this->parceLocale($item, $current_country);
						}
					}
					else {
						\Log::info('DownloadGiataGeographyData parceLocale locale', ['locale' => $locale]);
						$this->parceLocale($locale, $current_country);
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

	private function parceLocale($locale, $current_country)
	{
		if (isset($locale['@attributes'])) {
			$current_locale = [
				'LocaleId' => $locale['@attributes']['LocaleId'],
				'LocaleName' => $locale['@attributes']['LocaleName'],
			];
		}
		else {
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
