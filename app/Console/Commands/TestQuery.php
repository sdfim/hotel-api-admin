<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ExpediaContentMain;

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
	// protected const COUNTRY_HASH = 233; // US
	protected const COUNTRY_HASH = 179; // PL
	protected const RATING_MIN = 4;

    /**
     * Execute the console command.
     */
    public function handle()
    {
		$starttime = microtime(true);
		$step = 1;
		while (true) {
			$offset = $step * self::LIMIT;
			$records = $this->getChain($offset);
			// $records = $this->getChain2($offset);

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

        $records = DB::table('expedia_content_mains')
			->leftJoin('expedia_content_slaves', 'expedia_content_slaves.property_id', '=', 'expedia_content_mains.property_id')
			->where('expedia_content_mains.country_hash', '=', self::COUNTRY_HASH)
			->where('expedia_content_mains.rating', '>=', self::RATING_MIN)
			->limit(self::LIMIT)
			->offset($offset)
			->get();

		$endtime = microtime(true);
		$duration = $endtime - $starttime;

		$this->info("The duration '$duration' ");
		$this->info("The count records " . count($records));

		return $records;
	}

	private function getChain2($offset) 
	{
		$starttime = microtime(true);

		$records = ExpediaContentMain::leftJoin('expedia_content_slaves', 'expedia_content_slaves.property_id', '=', 'expedia_content_mains.property_id')
			->where('expedia_content_mains.country_hash', '=', self::COUNTRY_HASH)
			->where('expedia_content_mains.rating', '>=', self::RATING_MIN)
			->take(self::LIMIT) // Equivalent to LIMIT in SQL
			->skip($offset) // Equivalent to OFFSET in SQL
			->get();

		$endtime = microtime(true);
		$duration = $endtime - $starttime;
	
		$this->info("The duration '$duration' ");
		$this->info("The count records " . count($records));

		return $records;
	}
}
