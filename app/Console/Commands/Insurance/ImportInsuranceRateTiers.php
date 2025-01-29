<?php

namespace App\Console\Commands\Insurance;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use League\Csv\Reader;

class ImportInsuranceRateTiers extends Command
{
    protected $signature = 'import:insurance-rate-tiers {vendor_id} {file}';
    protected $description = 'Import insurance rate tiers from a CSV file';

    public function handle()
    {
        $file = $this->argument('file');
        $vendor_id = $this->argument('vendor_id');

        if (!file_exists($file) || !is_readable($file)) {
            $this->error('File not found or is not readable.');
            return 1;
        }

        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        DB::transaction(function () use ($vendor_id, $records) {
            DB::table('insurance_rate_tiers')->where('vendor_id', $vendor_id)->delete();

            foreach ($records as $record) {
                DB::table('insurance_rate_tiers')->insert([
                    'vendor_id' => $vendor_id,
                    'min_trip_cost' => $record['min_trip_cost'],
                    'max_trip_cost' => $record['max_trip_cost'],
                    'consumer_plan_cost' => $record['consumer_plan_cost'],
                    'ujv_retention' => $record['ujv_retention'],
                    'net_to_trip_mate' => $record['net_to_trip_mate'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->info('Insurance rate tiers imported successfully.');
        }, 1);

        return 0;
    }
}
