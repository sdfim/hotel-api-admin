<?php

namespace App\Console\Commands;

use App\Models\ApiSearchInspector;
use App\Models\GeneralConfiguration;
use App\Models\ApiBookingInspector;
use Illuminate\Console\Command;

class PurgeInspectors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purge-inspectors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle(): void
    {
        # delete by day config (time_inspector_retained)
        $kept_days = GeneralConfiguration::first()->time_inspector_retained;
        $kept_date = date('Y-m-d H:i:s', strtotime('-' . $kept_days . ' days'));

        ApiBookingInspector::where('created_at', '<', $kept_date)->delete();

        ApiSearchInspector::where('created_at', '<', $kept_date)->delete();
    }
}
