<?php

namespace App\Console\Commands\SupplierRepository;

use Illuminate\Console\Command;
use ZipArchive;

class ImportFiles extends Command
{
    protected $signature = 'files:import {file}';

    protected $description = 'Extract a zip file to the storage directory';

    public function handle(): bool
    {
        $zipFile = $this->argument('file');
        if (! file_exists($zipFile)) {
            $this->error('File not found: '.$zipFile);
            \Log::error('files:import File not found: '.$zipFile);

            return false;
        }
        /** @var ZipArchive $zip */
        $zip = app(ZipArchive::class);

        if ($zip->open($zipFile) === true) {
            $zip->extractTo(storage_path('app/public'));
            $zip->close();

            $this->info('Files extracted successfully.');
            \Log::info('files:import Files extracted successfully.');

            return true;
        } else {
            $this->error('Failed to extract archive.');
            \Log::error('files:import Failed to extract archive.  zipFile: '.$zipFile);

            return false;
        }
    }
}
