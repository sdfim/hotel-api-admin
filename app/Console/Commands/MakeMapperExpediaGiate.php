<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Models\ExpediaContent;
use App\Models\GiataProperty;
use App\Models\MapperExpediaGiata;
use App\Models\ReportMapperExpediaGiata;

class MakeMapperExpediaGiate extends Command
{
	use BaseTrait;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'make-mapper-expedia-giate {stepStrategy}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 *
	 */
	private const BATCH_SIZE = 100;
	/**
	 *
	 */
	private const BATCH_SIZE_ADVANCED = 10;

	/**
	 * @var int
	 */
	private int $batch = 1;

	/**
	 * @var int
	 */
	private int $batchReport = 1;

	/**
	 * @var int
	 */
	private int $stepStrategy = 4;

	public function __construct()
	{
		parent::__construct();
		$this->current_time['report_mapper'] = microtime(true);
	}

	/**
	 * Execute the console command.
	 * @return void
	 */
	public function handle(): void
	{
		$this->stepStrategy = $this->argument('stepStrategy');

		$mapper = [];
		$mapperReport = [];

		# step 1: where name = name, latitude like latitude(2 point after dot), longitude like longitude(2 point after dot)
		if (str_contains($this->stepStrategy, 1)) {
			$this->batch = 1;
			$arrExpedia = $this->fetchExpediaNeedMapping();

			$this->info('start step 1');

			foreach ($arrExpedia as $expedia) {

				// $this->comment($expedia['property_id'] . ' ' . $expedia['longitude']);

				$latitude = bcdiv($expedia['latitude'], 1, 2);
				$longitude = bcdiv($expedia['longitude'], 1, 2);
				$mapper_address = $expedia['address']['line_1'];

				// $this->comment($expedia['property_id'] . ' ' . $expedia['name'] . ' | ' . 'latitude: ' . $latitude . ' | longitude: ' . $longitude);

				$this->comment($expedia['property_id'] . ' ' . $mapper_address . ' | ' . 'latitude: ' . $latitude . ' | longitude: ' . $longitude);
				
				if ($expedia['property_id'] < 1664) continue;

				$cityNmae = str_replace(['@', "'"], '', $expedia['city']);
			
				$giata = GiataProperty::where('mapper_address', $mapper_address)
					->where('latitude', 'like', $latitude . '%')
					->where('longitude', 'like', $longitude . '%')
					->get()
					->toArray();

				if ($giata) {
					foreach ($giata as $giataItem) {
						$this->info('Expedia: ' . $expedia['property_id'] . ' - ' . $expedia['name'] . ' - ' . $giataItem['code'] . ' - ' . $giataItem['name']);
						$this->batch++;
						$this->batchReport++;
						$mapper[] = [
							'expedia_id' => $expedia['property_id'],
							'giata_id' => $giataItem['code'],
							'step' => 10,
						];
						$mapperReport[] = [
							'expedia_id' => $expedia['property_id'],
							'giata_id' => $giata[0]['code'],
							'step' => 10,
							'status' => 'success',
							'created_at' => date('Y-m-d H:i:s'),
						];
					}
				}
				else {}
				if ($this->batch % self::BATCH_SIZE == 0) {
					MapperExpediaGiata::insertOrIgnore($mapper);
					$mapper = [];
				}
				if ($this->batchReport > self::BATCH_SIZE_ADVANCED) {
					ReportMapperExpediaGiata::insert($mapperReport);
					$mapperReport = [];
					$this->batchReport = 1;
				}
			}
		}

		# step 2 and more: where name like name, latitude like latitude(2 point after dot), longitude like longitude(2 point after dot)
		if (str_contains($this->stepStrategy, 2) ||
			str_contains($this->stepStrategy, 3) ||
			str_contains($this->stepStrategy, 4) ||
			str_contains($this->stepStrategy, 5) ||
			str_contains($this->stepStrategy, 6) ||
			str_contains($this->stepStrategy, 7) ||
			str_contains($this->stepStrategy, 8) ||
			str_contains($this->stepStrategy, 9)
		) {			
			$arrExpedia = $this->fetchExpediaNeedMapping();

			foreach ($arrExpedia as $expedia) {

				$nameHotel = $expedia['name'];
				$nameArr = explode(' ', $expedia['name']);
				// if (count($nameArr) == 1) continue;
				$lastItem = array_pop($nameArr);
				$expediaNameArr = [];
				foreach ($nameArr as $v) {
					if ($v == $lastItem) continue;
					$expediaNameArr[] = $v;
				}
				$expediaNameStart = implode(' ', $expediaNameArr);

				$latitude = bcdiv($expedia['latitude'], 1, 2);
				$longitude = bcdiv($expedia['longitude'], 1, 2);
				$latitude0 = bcdiv($expedia['latitude'], 1, 0);
				$longitude0 = bcdiv($expedia['longitude'], 1, 0);
				$latitude1 = bcdiv($expedia['latitude'], 1, 1);
				$longitude1 = bcdiv($expedia['longitude'], 1, 1);

				$phone = str_replace('-', '', $expedia['phone']);
				$postCode = str_replace('-', '', $expedia['postal_code']);

				$expediaName12 = $expediaName23 = $expediaName1 = 'DDDDDDDDDDDDDDDDDDDDD';

				if (isset($nameArr[0]) && isset($nameArr[1])) {
					$expediaName12 = $nameArr[0] . ' ' . $nameArr[1];
				}
				if (isset($nameArr[1]) && isset($nameArr[2])) {
					$expediaName23 = $nameArr[1] . ' ' . $nameArr[2];
				}
				if (isset($nameArr[0])) {
					$expediaName1 = $nameArr[0];
				}

				$strategy = [
					'2' => [
						'latitude' => $latitude . '%',
						'longitude ' => $longitude . '%',
						'name' => $nameHotel . '%',
					],
					'3' => [
						'latitude' => $latitude . '%',
						'longitude ' => $longitude . '%',
						'name' => $expediaNameStart . '%',
						'mapper_phone_number' => '%' . $phone . '%',
					],
					'4' => [
						'latitude' => $latitude . '%',
						'longitude ' => $longitude . '%',
						'mapper_phone_number' => '%' . $phone . '%',
						'mapper_postal_code' => '%' . $postCode . '%',
					],
					'5' => [
						'latitude' => $latitude0 . '%',
						'longitude ' => $longitude0 . '%',
						'name' =>  $nameHotel,
						'city' =>  $expedia['city'],
					],
					'6' => [
						'latitude' => $latitude . '%',
						'longitude ' => $longitude . '%',
						'name' => trim(str_replace('Hotel', '', $nameHotel)) . '%',
					],
					'7' => [
						'latitude' => $latitude1 . '%',
						'longitude ' => $longitude0 . '%',
						'name' => $expediaName12 . '%',
						'city' =>  $expedia['city'],
					],
					'8' => [
						'latitude' => $latitude1 . '%',
						'longitude ' => $longitude0 . '%',
						'name' => '%' . $expediaName23 . '%',
						'city' =>  $expedia['city'],
					],
					'9' => [
						'latitude' => $latitude1 . '%',
						'longitude ' => $longitude0 . '%',
						'name' => '%' . $expediaName1 . '%',
						'city' => $expedia['city'],
						'mapper_phone_number' => '%' . $phone . '%',
					],
				];

				$this->executionTime('report_mapper');
				$mp = false;
				$search = GiataProperty::query();
				$info = [];
				$error = [];
				foreach ($strategy as $step => $params) {
					if (!str_contains($this->stepStrategy, $step)) continue;
					$giata = $this->query($params);
					if ($giata) {
						$mapper = $this->addToMapper($mapper, $giata, $expedia, $step);
						$mp = true;
						$mapperReport[] = [
							'expedia_id' => $expedia['property_id'],
							'giata_id' => $giata[0]['code'],
							'step' => $step,
							'status' => 'success',
							'created_at' => date('Y-m-d H:i:s'),
						];
						$this->batchReport++;
						break;
					}
					if (!$mp) {
						$error[] = $step;
					}
					$search = $this->addQuery($params, $search);
				}

				if (!$mp) {
					$this->error($expedia['property_id'] . ' - ' . $expedia['name'] . ' | Steps = ' . implode(', ', $error) . ' | TIME = ' . $this->executionTime('report_mapper') . ' sec');
					$mapperReport[] = [
						'expedia_id' => $expedia['property_id'],
						'giata_id' => null,
						'step' => implode(', ', $error),
						'status' => 'error',
						'created_at' => date('Y-m-d H:i:s'),
					];
					$this->batchReport++;
				}

				// $query = $search->get()->toArray();
				// $mapper = $this->addToMapper($mapper, $query, $expedia, 33);

				if ($this->batch > self::BATCH_SIZE_ADVANCED) {
					MapperExpediaGiata::insert($mapper);
					$mapper = [];
					$this->batch = 1;
				}

				if ($this->batchReport > self::BATCH_SIZE_ADVANCED) {
					ReportMapperExpediaGiata::insert($mapperReport);
					$mapperReport = [];
					$this->batchReport = 1;
				}
			}
			MapperExpediaGiata::insert($mapper);
			ReportMapperExpediaGiata::insert($mapperReport);
		}
	}

	/**
	 * @param array $params
	 * @return array
	 */
	private function query(array $params): array
	{
		$serch = GiataProperty::query();
		foreach ($params as $k => $param) {
			$serch->where(trim($k), 'like', $param);
		}

		return $serch->get()->toArray();
	}

	/**
	 * @param array $params
	 */
	private function addQuery(array $params, $query)
	{
		$query->orWhere(function ($query) use ($params) {
			foreach ($params as $k => $param) {
				$query->where(trim($k), 'like', $param);
			}
		});

		return $query;
	}

	/**
	 * @param array $mapper
	 * @param array $giata
	 * @param  $expedia
	 * @param int $step
	 * @return array
	 */
	private function addToMapper(array $mapper, array $giata, $expedia, int $step): array
	{
		foreach ($giata as $giataItem) {
			$this->info($expedia['property_id'] . ' | ' . $expedia['name'] .
				' - ' . $giataItem['code'] . ' | ' . $giataItem['name'] . ' | STEP = ' . $step . ' | TIME = ' . $this->executionTime('report_mapper') . ' sec');
			$this->batch++;
			$mapper[] = [
				'expedia_id' => $expedia['property_id'],
				'giata_id' => $giataItem['code'],
				'step' => $step,
			];
		}
		return $mapper;
	}

	/**
	 * @return iterable
	 */
	private function fetchExpediaNeedMapping(): iterable
	{
		$query = ExpediaContent::select('property_id', 'name', 'latitude', 'longitude', 'phone', 'city', 'address')
			->leftJoin('mapper_expedia_giatas', 'expedia_content_main.property_id', '=', 'mapper_expedia_giatas.expedia_id')
			->whereNull('mapper_expedia_giatas.giata_id')
			->leftJoin('report_mapper_expedia_giata', 'report_mapper_expedia_giata.expedia_id', '=', 'expedia_content_main.property_id')
			->whereNull('report_mapper_expedia_giata.expedia_id')
			->cursor();

		foreach ($query as $row) {
			yield $row;
		}
	}
}
