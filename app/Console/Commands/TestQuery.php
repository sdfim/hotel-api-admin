<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ExpediaContentMain;
use App\Models\ExpediaContentSlave;
use App\Models\ExpediaContent;

class TestQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-query';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

	protected const LIMIT = 250;
	protected const COUNTRY_CODE = 'US';
	protected const RATING_MIN = 4;

    /**
     * Execute the console command.
     */
    public function handle()
    {
		$starttime = microtime(true);
		$step = 0;
		while (true) {
			$offset = $step * self::LIMIT;
			$records = $this->getChain($offset);

			if (count($records) === 0) {
				break;
			}
			$step++;
			$this->info("The step '$step' ");
		}

		$endtime = microtime(true);
		$duration = $endtime - $starttime;

		$this->info("The TOTAL duration '$duration' ");
    }

	private function getChain($offset) 
	{
		$starttime = microtime(true);

		$city = 'Boston';

		$records = ExpediaContent::select('property_id', 'rooms_occupancy', 'rating', 'name', 'city', 'country_code', 'category_name', 'checkin_time', 'checkout_time')
		 	->where('city', '=', $city)
		 	->where('country_code', '=', self::COUNTRY_CODE)
		 	->where('rating', '>=', self::RATING_MIN)
			->where(function ($query) {
				$query->where('rooms_occupancy', 'like', '%"total": 5%')
					->orWhere('rooms_occupancy', 'like', '%"total": 4%')
					->orWhere('rooms_occupancy', 'like', '%"total": 3%');
			})
		 	->limit(self::LIMIT)
		 	->offset($offset)
		 	->get();

		$endtime = microtime(true);
		$duration = $endtime - $starttime;

		$this->info("The duration '$duration' ");
		$this->info("The count records " . count($records));

		return $records;
	}

}
