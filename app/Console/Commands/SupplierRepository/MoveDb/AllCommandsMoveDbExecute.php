<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AllCommandsMoveDbExecute extends Command
{
    protected $signature = 'move-db:all-commands';

    protected $description = 'Execute a predefined list of console commands with a progress bar';

    public function handle()
    {
        $commands = [
            //            'move-db:hotels-from-db-to-csv',
            'move-db:hotels-from-csv-to-db',
            'move-db:hotel-crm-mappings-from-csv-to-db',
            'move-db:vendors',
            'move-db:rooms',
            'move-db:hotel-images',
            'move-db:room-images',
            'process:hotel-images-thumbnails',
            'move-db:rates',
            'move-db:hotel-alerts',
            'move-db:hotel-descriptive-content',
            'move-db:consortia-amenities',
            'move-db:promotions',
            'move-db:informative-services',
            'move-db:informative-services-room-level',
            'move-db:import-contacts',
            'move-db:room-amenities',
            'move-db:hotel-commissions',
            'move-db:external-identifiers',
        ];

        $this->output->progressStart(count($commands));

        foreach ($commands as $command) {
            $this->info('');
            Artisan::call($command, [], $this->output);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("\nAll commands executed successfully.");
    }
}
