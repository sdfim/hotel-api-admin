<?php

namespace App\Console\Commands\SupplierRepository;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExportFiles extends Command
{
    protected $signature = 'files:export';

    protected $description = 'Export files to a zip archive';

    public function handle(): int
    {
        /** @var ZipArchive $zip */
        $zip = new ZipArchive;
        $zipIndex = 1;
        $zipFile = storage_path('app/public/files_'.$zipIndex.'.zip');
        $maxSize = 100 * 1024 * 1024; // 100MB in bytes
        $currentSize = 0;

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $directories = ['products', 'products/thumbnails', 'images'];
            foreach ($directories as $directory) {
                $files = Storage::disk('public')->files($directory);
                foreach ($files as $file) {
                    $filePath = storage_path('app/public/'.$file);
                    $fileSize = filesize($filePath);

                    if ($currentSize + $fileSize > $maxSize) {
                        $zip->close();
                        $this->info('Files exported to '.$zipFile);

                        $zipIndex++;
                        $zipFile = storage_path('app/public/files_'.$zipIndex.'.zip');
                        $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                        $currentSize = 0;
                    }

                    $zip->addFile($filePath, $file);
                    $currentSize += $fileSize;
                }
            }
            $zip->close();
            $this->info('Files exported to '.$zipFile);

            return $zipIndex;
        } else {
            $this->error('Failed to create archive.');

            return 0;
        }
    }
}
