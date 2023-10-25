<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\ExpediaContent;
use App\Models\Supplier;
use Modules\Inspector\ExceptionReportController;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Process;


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

    protected RapidClient $rapidClient;
    protected $apiExceptionReport;
    private const PROPERTY_CONTENT_PATH = "v3/files/properties/";
    private const BATCH_SIZE = 100;
    private const MIN_RATING = 4;
    private $type;
    private $step;
    protected $current_time;
    protected $step_current_time;
	protected $current_time_report;
	protected $expedia_id;
	protected $report_id;
	protected string $savePath;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */

	public function __construct (RapidClient $rapidClient)
    {
        parent::__construct();
        $this->rapidClient = $rapidClient;
        $this->apiExceptionReport = new ExceptionReportController();
		// TODO: get expedia_id from suppliers table
		$this->expedia_id = 1;
		$this->current_time = microtime(true);
		$this->step_current_time = microtime(true);
		$this->current_time_report = microtime(true);
		$this->savePath = storage_path() . '/app/expedia';
    }

    /**
     * Execute the console command.
     */
    public function handle ()
    {
        $this->type = $this->argument('type'); // content
        $this->step = $this->argument('step'); // 1, 2, 3, 4
		$this->report_id = Str::uuid()->toString();

		$this->executionTimeReport();

		$result = Process::run('df -h')->output();
        $this->info('DownloadExpediaData df -h: ' . $result);
		$result = Process::run('free -h')->output();
        $this->info('DownloadExpediaData free -h: ' . $result);

        if (str_contains($this->step, 1)) {
            # get url from expedia
            $url = $this->getUrlArchive();
            $this->info('url from expedia ' . $url . ' in ' . $this->executionStepTime() . ' seconds');

			$this->saveSuccessReport('DownloadExpediaData', 'Step:1 get url', json_encode([
				'url' => $url,
				'execution_time' => $this->executionTimeReport() . ' sec',
			]));
        }

		if (str_contains($this->step, 2)) {
            # download file from url and save to storage
			$this->downloadArchive($url);
        }

		if (str_contains($this->step, 3)) {
            # unzip file
            $this->unzipFile();
        }

		if (str_contains($this->step, 4)) {
            # parse json to db
            $this->parseJsonToDb();
        }
    }

	/*
	 * Get url from expedia
	 * @return string
	 * */
    function getUrlArchive (): string
    {
		$this->executionTimeReport();
        $queryParams = [
            'language' => 'en-US',
            'supply_source' => 'expedia',
        ];
        try {
            $response = $this->rapidClient->get(self::PROPERTY_CONTENT_PATH . $this->type, $queryParams);

            $propertyContents = $response->getBody()->getContents();
            $url = json_decode($propertyContents, true)['href'];
        } catch (\Exception $e) {
			$this->saveErrorReport('DownloadExpediaData', 'getUrlArchive', json_encode([
				'getMessage' => $e->getMessage(),
				'getTraceAsString' => $e->getTraceAsString(),
				'execution_time' => $this->executionTimeReport() . ' sec',
			]));
        }

        return $url;
    }

	/*
	 * Download file from url and save to storage
	 * @param string $url
	 * @return void
	 * */
    function downloadArchive ($url): void
    {
		$this->executionTimeReport();
		$this->executionStepTime();
        try {
            $response = Http::timeout(3600)->get($url);

            if ($response->successful()) {
                $fileContents = $response->body();
                $fileName = 'expedia_' . $this->type . '.gz';

				file_put_contents($this->savePath . '/' . $fileName, $fileContents);

                $this->info('Step:2 download file ' . $url . ' in ' . $this->executionStepTime() . ' seconds');

				$this->saveSuccessReport('DownloadExpediaData', 'Step:2 download file', json_encode([
					'path' => $this->savePath,
					'fileName' => $fileName,
					'execution_time' => $this->executionTimeReport() . ' sec',
				]));
            } else {
				$this->saveErrorReport('DownloadExpediaData', 'Step:2 download file', json_encode([
					'response-status' =>  $response->status(),
					'response-body' => $response->body(),
					'path' => $this->savePath,
					'execution_time' => $this->executionTimeReport() . ' sec',
				]));
				$this->error('Error downloading gz file:  ' . json_encode([
					'response-status' =>  $response->status(),
					'response-body' => $response->body(),
					'path' => $this->savePath,
					'execution_time' => $this->executionStepTime() . ' sec',
				]));
            }

        } catch (\Exception $e) {
			$this->error('Error downloading gz file:  ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
			$this->saveErrorReport('DownloadExpediaData', 'Step:2 download File Gz', json_encode([
				'getMessage' => $e->getMessage(),
				'getTraceAsString' => $e->getTraceAsString(),
				'execution_time' => $this->executionTimeReport() . ' sec',
			]));
        }
    }

	/*
	 * Unzip file
	 * @return void
	 * */
    function unzipFile (): void
    {
		$this->executionTimeReport();
		$this->executionStepTime();
		try {
			$archive = $this->savePath . '/expedia_' . $this->type . '.gz';
			$result = Process::timeout(3600)->run('gunzip -f ' . $archive);

			if ($result->successful()) {
				$this->saveSuccessReport('DownloadExpediaData', 'Step:3 unzip file', json_encode([
					'archive' => $archive,
					'result' => $result,
					'execution_time' => $this->executionTimeReport() . ' sec',
				]));
				$this->info('DownloadExpediaData Step:3 unzip file: ' . json_encode([
					'archive' => $archive,
					'result' => $result,
					'execution_time' => $this->executionStepTime() . ' sec',
				]));
			} else {
				$this->error('Error DownloadExpediaData Step:3 unzip file:  ' . json_encode([
					'result' => $result->throw(),
					'execution_time' => $this->executionStepTime() . ' sec',
				]));
				$this->saveErrorReport('DownloadExpediaData', 'Step:3 unzip file', json_encode([
					'getMessage' => $result->throw(),
					'execution_time' => $this->executionTimeReport() . ' sec',
				]));
			}
		} catch (\Exception $e) {
			$this->error('Error unzip file:  ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
			$this->saveErrorReport('DownloadExpediaData', 'Step:3 unzip File', json_encode([
				'getMessage' => $e->getMessage(),
				'getTraceAsString' => $e->getTraceAsString(),
				'execution_time' => $this->executionTimeReport() . ' sec',
			]));
		}

    }

	/*
	 * Parse json to db
	 * @return void
	 * */
    function parseJsonToDb (): void
    {
		$this->executionTimeReport();
		$this->executionStepTime();

		// Delete all existing data
        ExpediaContent::query()->delete();

		$filePath = $this->savePath . '/expedia_' . $this->type;

        // Open the JSONL file for reading
        $file = fopen($filePath, 'r');

        if (!$file) {
            $this->error('Unable to open the JSONL file.');
            return;
        }

        $batchSize = self::BATCH_SIZE; 
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

			$propertyIds = [];
            foreach ($data as $key => $value) {

				if ($key == 'property_id') {
					$propertyIds[] = $value;
				}
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
                }
                if ($key == 'category') {
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
					ExpediaContent::whereIn('property_id', $propertyIds)->delete();
                    ExpediaContent::insert($batchData);
                } catch (\Exception $e) {
					$this->error('ImportJsonlData error' .  $e->getMessage());
					$this->saveErrorReport('DownloadExpediaData', 'Import Json lData', json_encode([
						'getMessage' => $e->getMessage(),
						'getTraceAsString' => $e->getTraceAsString(),
						'execution_time' => $this->executionTimeReport() . ' sec',
					]));
                }
                $batchCount++;
                $this->info('Data imported batchData: ' . $batchCount . ' count =  ' . count($batchData));
                $batchData = [];
            }

        }

        // Insert any remaining data as the last batch
        if (!empty($batchData)) {
            try {
				ExpediaContent::whereIn('property_id', $propertyIds)->delete();
                ExpediaContent::insert($batchData);
            } catch (\Exception $e) {
                $this->error('ImportJsonlData error' .  $e->getMessage());
				$this->saveErrorReport('DownloadExpediaData', 'Step:4 Import Json to Data', json_encode([
					'getMessage' => $e->getMessage(),
					'getTraceAsString' => $e->getTraceAsString(),
					'execution_time' => $this->executionTimeReport() . ' sec',
				]));
            }
        }

		ExpediaContent::where('created_at', '<', Carbon::now())->delete();

        fclose($file);

        $this->info('Import completed. ' . $this->executionStepTime() . " seconds");

		$this->saveSuccessReport ('DownloadExpediaData', 'Step:4 Import Json to Data', json_encode([
			'execution_time' => $this->executionTimeReport() . ' sec',
		]));

    }

    private function executionTime ()
    {
        $execution_time = (microtime(true) - $this->current_time);
        $this->current_time = microtime(true);

        return $execution_time;
    }

    private function executionStepTime ()
    {
        $execution_time = round((microtime(true) - $this->current_time), 3);
        $this->step_current_time = microtime(true);

        return $execution_time;
    }

	private function executionTimeReport ()
    {
        $execution_time = round((microtime(true) - $this->current_time_report), 3);
        $this->current_time_report = microtime(true);

        return $execution_time;
    }

	private function saveSuccessReport(string $action, string $description, string $content) : void
	{
		$this->saveReport($action, $description, $content, 'success');
	}

	private function saveErrorReport(string $action, string $description, string $content) : void
	{
		$this->saveReport($action, $description, $content);
	}

	private function saveReport(string $action, string $description, string $content, string $level = 'error') : void
	{
		$this->apiExceptionReport
			->save(
				$this->report_id,
				$level,
				$this->expedia_id,
				$action,
				$description,
				$content
			);
	}

}
