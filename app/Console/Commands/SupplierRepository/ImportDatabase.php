<?php

namespace App\Console\Commands\SupplierRepository;

use Illuminate\Console\Command;

class ImportDatabase extends Command
{
    protected $signature = 'db:import {file}';

    protected $description = 'Import the database from a specified SQL file';

    public function handle()
    {
        $file = $this->argument('file');
        if (! file_exists($file)) {
            $this->error('File not found: '.$file);

            return;
        }

        $this->importDatabase($file);
        $this->info('Database import completed successfully.');
    }

    private function importDatabase(string $file): void
    {
        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s --port=%s --protocol=TCP %s < %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            env('DB_PORT'),
            env('DB_DATABASE'),
            $file
        );

        exec($command);
    }
}
