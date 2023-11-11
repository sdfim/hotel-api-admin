<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;


class TestSpeedDbCommand extends Command
{
	use BaseTrait;
    protected $signature = 'test-speed-db';
    protected $description = 'Command description';


    public function handle()
    {
		$this->info('TestSpeedDbCommand ');
		
		$this->current_time['start'] = microtime(true);

        $queryGiata = DB::select("
			select * from  ujv_api.giata_properties  order by  ujv_api.giata_properties.code  asc limit 10 offset 0
        ");

		$this->info('Giata: ' . $this->executionTime('start') . ' sec');

		$queryExpedia = DB::select("
			select * from  ujv_api.expedia_content_main  order by  rating  desc ,  ujv_api.expedia_content_main.property_id  asc limit 10 offset 0
        ");

		$this->info('Expedia: ' . $this->executionTime('start') . ' sec');
    }
	
}