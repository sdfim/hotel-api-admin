<?php

namespace App\Console\Commands\Insurance;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ExportInsuranceRateTiers extends Command
{
    protected $signature = 'export:insurance-rate-tiers {vendor_id} {insurance_type_id}';

    protected $description = 'Export insurance rate tiers to a CSV file';

    public function handle()
    {
        $vendor_id = $this->argument('vendor_id');
        $insurance_type_id = $this->argument('insurance_type_id');

        $vendor_name = DB::table('pd_vendors')->where('id', $vendor_id)->value('name');
        $insurance_type_name = DB::table('insurance_types')->where('id', $insurance_type_id)->value('name');

        $records = DB::table('insurance_rate_tiers')
            ->where('vendor_id', $vendor_id)
            ->where('insurance_type_id', $insurance_type_id)
            ->select('min_trip_cost', 'max_trip_cost', 'consumer_plan_cost', 'net_to_trip_mate')
            ->get();

        if ($records->isEmpty()) {
            $this->error('No records found.');

            return 1;
        }

        $csv = Writer::createFromString('');
        $csv->insertOne(['min_trip_cost', 'max_trip_cost', 'consumer_plan_cost', 'net_to_trip_mate']);

        foreach ($records as $record) {
            $csv->insertOne([
                $record->min_trip_cost,
                $record->max_trip_cost,
                $record->consumer_plan_cost,
                $record->net_to_trip_mate,
            ]);
        }

        $fileName = "insurance_rate_tiers_{$vendor_name}_{$insurance_type_name}.csv";
        Storage::put("public/exports/{$fileName}", $csv->toString());

        $this->info("CSV file created: storage/app/public/exports/{$fileName}");

        return 0;
    }
}
