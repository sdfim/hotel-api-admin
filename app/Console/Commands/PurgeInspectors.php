<?php

namespace App\Console\Commands;

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
        $inspector = ApiSearchInspector::where('created_at', '<', $kept_date);
        if ($inspector->count() > 0) {
            $this->clear($inspector);
        }

        // test
        // $this->info('PurgeInspectors: test');
        // $kept_days = 1;
        // $kept_date = date('Y-m-d H:i:s', strtotime('+' . $kept_days . ' days'));
        // $inspector = ApiSearchInspector::where('created_at', '<', $kept_date);
        // if ($inspector->count() > 0) $this->clear($inspector);
    }

    private function clear($inspector): void
    {
        $this->info('PurgeInspectors: clear');
        $inspector->chunk(100, function ($inspectors) {
            foreach ($inspectors as $inspector) {
                Storage::delete($inspector->response_path);
                Storage::delete($inspector->client_response_path);
                $inspector->delete();
            }
        });
    }
}
