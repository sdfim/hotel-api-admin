<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ExpediaContent;
use Ramsey\Uuid\Uuid;


class ImportJsonlData extends Command
{	
	protected const BATCH_SIZE = 100;
	protected $signature = 'import:jsonl {file}';
	protected $description = 'Import JSONL data into the database';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$filePath = $this->argument('file');

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
			'airports', 'fax', 'spoken_languages', 'all_inclusive'
		];
		$arr = [
			'property_id', 'name', 'phone', 'tax_id', 'rank', 'multi_unit',
			'payment_registration_recommended', 'supply_source'
		];
		$arr_uuid = [];
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
			
			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$value = json_encode($value);
				}
				$output[$key] = $value;
			}

			$batchData[] = $output;

			// Check if we have accumulated enough data to insert as a batch
			if (count($batchData) >= $batchSize) {
				try {
					ExpediaContent::insert($batchData);
				} catch (\Exception $e) {
					\Log::error('ImportJsonlData', ['error' => $e->getMessage()]);
				}

				// Clear the batch data array
				
				$batchCount++;

				// Optionally, you can display a progress message
				$this->info('Data imported batchData: ' . $batchCount. ' count =  ' . count($batchData));
				// if ($batchCount >= 100) break;

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
}
