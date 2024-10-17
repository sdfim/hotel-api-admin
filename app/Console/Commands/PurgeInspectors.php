<?php

namespace App\Console\Commands;

use App\Models\ApiBookingInspector;
use App\Models\ApiSearchInspector;
use App\Models\GeneralConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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
     */
    public function handle(): void
    {
        // delete by day config (time_inspector_retained)
        $this->info('PurgeInspectors: delete by day config (time_inspector_retained)');
        $kept_days = GeneralConfiguration::first()->time_inspector_retained;
        $kept_date = date('Y-m-d H:i:s', strtotime('-'.$kept_days.' days'));

        // clear ApiSearchInspector
        $searchInspectors = ApiSearchInspector::where('created_at', '<', $kept_date)->get();
        if ($searchInspectors->count() > 0) {
            $this->info('PurgeInspectors: clear ApiSearchInspector');
            foreach ($searchInspectors as $inspector) {
                Storage::delete($inspector->response_path);
                Storage::delete(str_replace('.json', '.original.json', $inspector->response_path));
                Storage::delete($inspector->client_response_path);
                $inspector->delete();
            }
        }

        // clear ApiBookingInspector
        $bookingInspectors = ApiBookingInspector::where('created_at', '<', $kept_date)->get();
        if ($bookingInspectors->count() > 0) {
            $this->info('PurgeInspectors: clear ApiBookingInspector');
            foreach ($bookingInspectors as $inspector) {
                Storage::delete($inspector->response_path);
                Storage::delete($inspector->client_response_path);
                $inspector->delete();
            }
        }
    }

}
