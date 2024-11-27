<?php

namespace App\Console\Commands\Insurance;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use League\Csv\Reader;

class ImportInsuranceRateTiers extends Command
{
    protected $signature = 'import:insurance-rate-tiers {provider_id} {file}';
    protected $description = 'Import insurance rate tiers from a CSV file';

    public function handle()
    {
        $file = $this->argument('file');
        $provider_id = $this->argument('provider_id');

        if (!file_exists($file) || !is_readable($file)) {
            $this->error('File not found or is not readable.');
            return 1;
        }

        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        DB::beginTransaction();

        try {
            DB::table('insurance_rate_tiers')->where('insurance_provider_id', $provider_id)->delete();

            foreach ($records as $record) {
                DB::table('insurance_rate_tiers')->insert([
                    'insurance_provider_id' => $provider_id,
                    'min_trip_cost' => $record['min_trip_cost'],
                    'max_trip_cost' => $record['max_trip_cost'],
                    'consumer_plan_cost' => $record['consumer_plan_cost'],
                    'ujv_retention' => $record['ujv_retention'],
                    'net_to_trip_mate' => $record['net_to_trip_mate'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            $this->info('Insurance rate tiers imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error importing insurance rate tiers: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
