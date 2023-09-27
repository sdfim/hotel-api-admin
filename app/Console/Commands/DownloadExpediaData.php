<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\API\ContentAPI\ExpediaSupplier\RapidClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\ExpediaContent;


class DownloadExpediaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download-expedia-data {type} {step}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

	private const PROPERTY_CONTENT_PATH = "v3/files/properties/";
	private const BATCH_SIZE = 100;
	private const MIN_RATING = 4;
	private $type;
	private $step;
	protected $current_time;
	protected $step_current_time;


    /**
     * Execute the console command.
     */
    public function handle()
    {	

		$this->type = $this->argument('type'); // content
		$this->step = $this->argument('step'); // 1, 2, 3, 4

		if ($this->step <= 1) {
			# get url from expedia
			$url = $this->getUrlArchive();
			$this->info('url from expedia '. $url. ' in ' . $this->executionStepTime() . ' seconds');
		}
		
		if ($this->step <= 2) {
			# download file from url and save to storage
			$success = $this->downloadArchive($url);
			if ($success) {
				$this->info('gz file downloaded.  in ' . $this->executionStepTime() . ' seconds');
			} else {
				$this->error('Failed to download or extract the zip file.');
			}
		}

		if ($this->step <= 3) {
			# unzip file
			$this->unzipFile();
			$this->info('unzip file in ' . $this->executionStepTime() . ' seconds');
		}

		if ($this->step <= 4) {
			# parse json to db
			$this->parseJsonToDb();
			$this->info('parse json to db in ' . $this->executionStepTime() . ' seconds');
		}	
    }

	function getUrlArchive() :string
	{
		$apiKey = env('EXPEDIA_RAPID_API_KEY');
		$sharedSecret = env('EXPEDIA_RAPID_SHARED_SECRET');
		
		$client = new RapidClient($apiKey, $sharedSecret);
		$queryParams = [
			'language' => 'en-US',
			'supply_source' => 'expedia',
		];
		$response = $client->get(self::PROPERTY_CONTENT_PATH.$this->type, $queryParams);
	
		// Read the response to return.
		$propertyContents = $response->getBody()->getContents(); 
		$url = json_decode($propertyContents, true)['href'];
		
		return $url;
	}

	function downloadArchive($url) : bool
	{
		$start_time = microtime(true);

		try {
			dump('start downloadAndExtractGz');
			\Log::debug('start downloadAndExtractGz', ['url' => $url, 'type' => $this->type]);

			$response = Http::timeout(3600)->get($url);

			if ($response->successful()) {
				$fileContents = $response->body();
				$fileName = 'expedia_'.$this->type.'.gz';

				$this->info('downloadAndExtractGz step 1 '. $url. ' in ' . $this->executionTime() . ' seconds');
				\Log::debug('downloadAndExtractGz step 1', ['fileName' => $fileName, 'execution_time' => $this->executionTime()]);

				Storage::put($fileName, $fileContents);
				$this->info('downloadAndExtractGz step 2 '. $url. ' in ' . $this->executionTime() . ' seconds');
				\Log::debug('downloadAndExtractGz step 2', ['fileName' => $fileName, 'execution_time' => $this->executionTime()]);

			} else {
				\Log::error('Error downloading gz file: ' . $response->status() . ' ' . $response->body());
			}

			return true;

		} catch (\Exception $e) {
			\Log::error('Error downloading gz file: ' . $e->getMessage());
			return false;
		}
    }

	function unzipFile() :void
	{
		$absolutePath = storage_path();
		$output = shell_exec('gunzip '.$absolutePath.'/app/expedia_'.$this->type.'.gz');
		\Log::debug('downloadAndExtractGz step 3 gunzip', ['absolutePath' => $absolutePath, 'output' => $output]);
	}

	function parseJsonToDb() :void
	{
		ExpediaContent::truncate();

		$filePath = storage_path().'/app/expedia_'.$this->type;

		// Open the JSONL file for reading
		$file = fopen($filePath, 'r');

		if (!$file) {
			$this->error('Unable to open the JSONL file.');
			return;
		}

		$batchSize = self::BATCH_SIZE; // Set your desired batch size
		$batchData = [];
		$batchCount = 0;
		$arr_json = [
			'address', 'ratings', 'location', 'category', 'business_model',
			'checkin', 'checkout', 'fees', 'policies', 'attributes', 'amenities',
			'images', 'onsite_payments', 'rooms', 'rates', 'dates', 'descriptions',
			'themes', 'chain', 'brand', 'statistics', 'vacation_rental_details',
			'airports', 'fax', 'spoken_languages', 'all_inclusive', 'rooms_occupancy',
		];
		$arr = [
			'property_id', 'name', 'phone', 'tax_id', 'rank', 'multi_unit',
			'payment_registration_recommended', 'supply_source',
			'city', 'state_province_code', 'state_province_name',
			'postal_code', 'country_code', 'latitude', 'longitude', 'category_name',
			'checkin_time', 'checkout_time', 'total_occupancy',
		];
		$start_time = microtime(true);

		while (($line = fgets($file)) !== false) {
			// Parse JSON from each line
			$data = json_decode($line, true);

			$output = [];
			foreach ($arr_json as $key) {
				$output[$key] = json_encode(['']);
			}
			foreach ($arr as $key) {
				$output[$key] = '';
			}

			if (!is_array($data)) break;

			$output['rating'] = 0;

			$is_write = true;
			
			foreach ($data as $key => $value) {

				if ($key == 'ratings') {
					$output['rating'] = $value['property']['rating'] ?? 0;
				}
				if ($key == 'address') {
					$output['city'] = $value['city'] ?? '';
					$output['state_province_code'] = $value['state_province_code'] ?? '';
					$output['state_province_name'] = $value['state_province_name'] ?? '';
					$output['postal_code'] = $value['postal_code'] ?? '';
					$output['country_code'] = $value['country_code'] ?? '';
				}
				if ($key == 'location') {
					$output['latitude'] = $value['coordinates']['latitude'] ?? 0;
					$output['longitude'] = $value['coordinates']['longitude'] ?? 0;
				}if ($key == 'category') {
					$output['category_name'] = $value['name'] ?? '';
				}
				if ($key == 'checkin') {
					$output['checkin_time'] = isset($value['begin_time']) ?
						date('Y-m-d H:i:s', strtotime($value['begin_time'])) :
						date('Y-m-d H:i:s', strtotime('00:00:00'));
				}
				if ($key == 'checkout') {
					$output['checkout_time'] = $value['time'] ?
						date('Y-m-d H:i:s', strtotime($value['time'])) :
						date('Y-m-d H:i:s', strtotime('00:00:00'));
				}
				$total = 0;
				if ($key == 'rooms') {
					$arr_rooms = [];
					foreach ($value as $room) {
						$arr_rooms[$room['id']] = [
							'id' => $room['id'],
							'occupancy' => $room['occupancy'],
						];
						if ($total < $room['occupancy']['max_allowed']['total']) {
							$total = $room['occupancy']['max_allowed']['total'];
						}
					}
					$output['rooms_occupancy'] = json_encode($arr_rooms);
					$output['total_occupancy'] = $total;
				}

				if (is_array($value)) {
					$value = json_encode($value);
				}
				
				$output[$key] = $value;
			}

			if ($output['rating'] < self::MIN_RATING) $is_write = false;

			if ($is_write) $batchData[] = $output;

			// Check if we have accumulated enough data to insert as a batch
			if (count($batchData) >= $batchSize) {
				try {
					ExpediaContent::insert($batchData);
				} catch (\Exception $e) {
					\Log::error('ImportJsonlData', ['error' => $e->getMessage()]);
				}
				$batchCount++;
				$this->info('Data imported batchData: ' . $batchCount. ' count =  ' . count($batchData));
				$batchData = [];
			}
			
		}

		// Insert any remaining data as the last batch
		if (!empty($batchData)) {
			try {
				ExpediaContent::insert($batchData);
			} catch (\Exception $e) {
				\Log::error('ImportJsonlData', ['error' => $e->getMessage()]);
			}
		}

		fclose($file);
		$end_time = microtime(true);
		$execution_time = ($end_time - $start_time);
		$this->info('Import completed. ' . round($execution_time, 2) . " seconds");

	}

	private function executionTime() 
	{
		$execution_time = (microtime(true) - $this->current_time);
		$this->current_time = microtime(true);

		return $execution_time;
	}

	private function executionStepTime() 
	{
		$execution_time = (microtime(true) - $this->current_time);
		$this->step_current_time = microtime(true);

		return $execution_time;
	}

}
