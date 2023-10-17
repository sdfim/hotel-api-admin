<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExpediaContent;
use App\Models\GiataProperty;
use App\Models\MapperExpediaGiata;

class MakeMapperExpediaGiate extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'make-mapper-expedia-giate {startId} {endId} {stepStrategy}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';
	private const BATCH_SIZE = 100;
	private const BATCH_SIZE_ADVANCED = 10;
	private $batch = 1;
	private $startId = 1;
	private $endId = 90000;
	private $stepStrategy = 4;

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$this->startId = $this->argument('startId'); // 1
		$this->endId = $this->argument('endId'); // 1
		$this->stepStrategy = $this->argument('stepStrategy');

		$mapper = [];

		// # step 1: where name = name, latitude like latitude(2 point after dot), longitude like longitude(2 point after dot)
		// $this->batch = 1;
		// $arrExpedia = $this->fetchExpediaNeedMapping();

		// foreach ($arrExpedia as $expedia) {

		//     $latitude = round($expedia['latitude'], 2);
		//     $longitude = round($expedia['longitude'], 2);

		//     $giata = GiataProperty::where('name', $expedia['name'])
		//         ->where('position', 'like', '%"' . $latitude . '%')
		//         ->where('position', 'like', '%"' . $longitude . '%')
		//         ->get()
		//         ->toArray();
		//     if ($giata) {
		//         foreach ($giata as $giataItem) {
		//             $this->info('Expedia: ' . $expedia['property_id'] . ' - ' . $expedia['name'] . ' - ' . $giataItem['code'] . ' - ' . $giataItem['name']);
		//             $this->batch++;
		//             $mapper[] = [
		//                 'expedia_id' => $expedia['property_id'],
		//                 'giata_id' => $giataItem['code'],
		//                 'step' => 1,
		//             ];
		//         }
		//     }
		//     if ($this->batch % self::BATCH_SIZE == 0) {
		//         MapperExpediaGiata::insertOrIgnore($mapper);
		//         $mapper = [];
		//     }
		// }

		# step 2 and more: where name like name, latitude like latitude(2 point after dot), longitude like longitude(2 point after dot)
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

			$latitude =   	 bcdiv($expedia['latitude'], 1, 2);
			$longitude =  	bcdiv($expedia['longitude'], 1, 2);
			$latitude0 =  	 bcdiv($expedia['latitude'], 1, 0);
			$longitude0 = 	bcdiv($expedia['longitude'], 1, 0);
			$latitude1 =  	 bcdiv($expedia['latitude'], 1, 1);
			$longitude1 = 	bcdiv($expedia['longitude'], 1, 1);

			$phone = str_replace('-', '', $expedia['phone']);
			$postCode = str_replace('-', '', $expedia['postal_code']);
			$state = $expedia['state_province_name'];
			$city = $expedia['city'];

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
				// '2' => [
				// 	'position' => '%"' . $latitude . '%',
				// 	'position ' => '%"' . $longitude . '%',
				// 	'name' => $nameHotel . '%',
				// ],
				// '3' => [
				//     'position' => '%"' . $latitude . '%',
				//     'position ' => '%"' . $longitude . '%',
				//     'name' => $expediaNameStart . '%',
				//     'phone' => '%' . $phone . '%',
				// ],
				// '4' => [
				//     'position' => '%"' . $latitude . '%',
				//     'position ' => '%"' . $longitude . '%',
				//     'phone' => '%' . $phone . '%',
				//     'address' => '%' . $postCode . '%',
				// ],
				// '5' => [
				// 	'position' => '%"' . $latitude0 . '%',
				// 	'position ' => '%"' . $longitude0 . '%',
				// 	'name' =>  $expedia['name'],
				// 	'city' =>  $expedia['city'],
				// ],
				// '6' => [
				// 	'position' => '%"' . $latitude . '%',
				// 	'position ' => '%"' . $longitude . '%',
				// 	'name' => trim(str_replace('Hotel', '', $nameHotel)) . '%',
				// ],
				// '7' => [
				// 	'position' => '%"' . $latitude1 . '%',
				// 	'position ' => '%"' . $longitude0 . '%',
				// 	'name' => $expediaName12 . '%',
				// 	'city' =>  $expedia['city'],
				// ],
				// '8' => [
				// 	'position' => '%"' . $latitude1 . '%',
				// 	'position ' => '%"' . $longitude0 . '%',
				// 	'name' => '%' . $expediaName23 . '%',
				// 	'city' =>  $expedia['city'],
				// ],
				'9' => [
					'position' => '%"' . $latitude1 . '%',
					'position ' => '%"' . $longitude0 . '%',
					'name' => '%' . $expediaName1 . '%',
					'city' =>  $expedia['city'],
					'phone' => '%' . $phone . '%',
				],

				// ],
			];

			$start = microtime(true);
			$mp = false;
			foreach ($strategy as $step => $params) {
				// if ($step > $this->stepStrategy) continue;
				$giata = $this->query($params);
				if ($giata) {
					$mapper = $this->addToMapper($mapper, $giata, $expedia, $step);
					$mp = true;
					$executionTime = (microtime(true) - $start);
					$this->info("Expedia batch = " . $this->batch . ", executionTime = $executionTime");
					break;
				}
				if (!$mp) $this->error('Expedia step=' . $step . ' = ' . $expedia['id'] . ' - ' . $expedia['property_id'] . ' - ' . $expedia['name']);
			}
			if (!$mp) $this->error('Expedia: ' . $expedia['id'] . ' - ' . $expedia['property_id'] . ' - ' . $expedia['name']);

			if ($this->batch > self::BATCH_SIZE_ADVANCED) {
				MapperExpediaGiata::insert($mapper);
				$mapper = [];
				$this->batch = 1;
				$this->info("Expedia insertOrIgnore");
			}
		}
		MapperExpediaGiata::insert($mapper);
		$this->info("Expedia insertOrIgnore");
	}

	private function query($params): array
	{
		$serch = GiataProperty::query();
		foreach ($params as $k => $param) {
			$serch->where(trim($k), 'like', $param);
			// dump(trim($k), $param);
		}

		// $query = $serch->toSql();
		// dd($query);

		return $serch->get()->toArray();
	}

	private function addToMapper($mapper, $giata, $expedia, $step): array
	{
		foreach ($giata as $giataItem) {
			$this->info("Expedia step-$step: " . $expedia['id'] . ' | ' . $expedia['property_id'] . ' | ' . $expedia['name'] .
				' - ' . $giataItem['code'] . ' | ' . $giataItem['name']);
			$this->batch++;
			$mapper[] = [
				'expedia_id' => $expedia['property_id'],
				'giata_id' => $giataItem['code'],
				'step' => $step,
			];
		}
		return $mapper;
	}

	private function fetchExpediaNeedMapping()
	{
		$query = ExpediaContent::select('expedia_contents.id', 'property_id', 'name', 'latitude', 'longitude', 'phone', 'city', 'state_province_name', 'postal_code')
			->leftJoin('mapper_expedia_giatas', 'expedia_contents.property_id', '=', 'mapper_expedia_giatas.expedia_id')
			->whereNull('mapper_expedia_giatas.giata_id')
			->where('expedia_contents.id', '>=', $this->startId)
			->where('expedia_contents.id', '<=', $this->endId)
			->cursor();

		foreach ($query as $row) {
			yield $row;
		}
	}
}
