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
    protected $signature = 'make-mapper-expedia-giate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private const BATCH_SIZE = 100;

    /**
     * Execute the console command.
     */
    public function handle()
    {	
		$mapper = [];
/*
		# step 1: where name = name, latitude like latitude(2 point after dot), longitude like longitude(2 point after dot)
		$batch = 1;
		$arrExpedia = $this->fetchExpediaNeedMapping();

		foreach ($arrExpedia as $expedia) {

			$latitude = substr($expedia['latitude'], 0, 5);
			$longitude = substr($expedia['longitude'], 0, 5);
			
			$giata = GiataProperty::where('name', $expedia['name'])
				->where('position', 'like', '%' . $latitude . '%')
				->where('position', 'like', '%' . $longitude . '%')
				->get()
				->toArray();
			if ($giata) {
				foreach ($giata as $giataItem) {
					$this->info('Expedia: ' . $expedia['property_id'] . ' - ' . $expedia['name'] . ' - ' . $giataItem['code'] . ' - ' . $giataItem['name']);
					$batch++;
					$mapper[] = [
						'expedia_id' => $expedia['property_id'],
						'giata_id' => $giataItem['code'],
						'step' => 1,
					];
				}
			}
			if ($batch % self::BATCH_SIZE == 0) {
				MapperExpediaGiata::insertOrIgnore($mapper);
				$mapper = [];
			}
		}
*/		

		# step 2: where name like name, latitude like latitude(2 point after dot), longitude like longitude(2 point after dot)
		$batch = 1;
		$arrExpedia = $this->fetchExpediaNeedMapping();

		foreach ($arrExpedia as $expedia) {

			$nameArr = explode(' ', $expedia['name']);
			if (count($nameArr) == 1) continue;
			$lastItem = array_pop($nameArr);
			$expediaNameArr = [];
			foreach ($nameArr as $v) {
				if ($v == $lastItem) continue;
				$expediaNameArr[] = $v;
			}
			$expediaName = implode(' ', $expediaNameArr);

			$latitude = substr($expedia['latitude'], 0, 5);
			$longitude = substr($expedia['longitude'], 0, 5);
			$phone = str_replace('-', '', $expedia['phone']);

			$giata = GiataProperty::where('name', 'like', $expediaName.'%')
				->where('position', 'like', '%' . $latitude . '%')
				->where('position', 'like', '%' . $longitude . '%')
				->where('phone', 'like', '%' . $phone . '%')
				// ->toSql();
				->get()
				->toArray();

			// dd($giata, $expediaName, $latitude, $longitude, $phone);

			if ($giata) {
				foreach ($giata as $giataItem) {
					$this->info('Expedia: ' . $expedia['property_id'] . ' - ' . $expedia['name'] . ' - ' . $giataItem['code'] . ' - ' . $giataItem['name']);
					$batch++;
					$mapper[] = [
						'expedia_id' => $expedia['property_id'],
						'giata_id' => $giataItem['code'],
						'step' => 2,
					];
				}
			}
			if ($batch % self::BATCH_SIZE == 0) {
				MapperExpediaGiata::insertOrIgnore($mapper);
				$mapper = [];
			}
		}

    }

	private function fetchExpediaNeedMapping() :array
	{
		$existingMapper = MapperExpediaGiata::select('expedia_id')->get()->toArray();
        return ExpediaContent::select('property_id', 'name', 'latitude', 'longitude', 'phone')
			->whereNotIn('property_id', $existingMapper)
			->get()
			->toArray();
	}
}
