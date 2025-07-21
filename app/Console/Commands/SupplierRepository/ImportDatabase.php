<?php

namespace App\Console\Commands\SupplierRepository;

use Illuminate\Console\Command;

class ImportDatabase extends Command
{
    protected $signature = 'db:import {file}';

    protected $description = 'Import the database from a specified SQL file';

    public function handle(): void
    {
        $file = $this->argument('file');
        chmod($file, 0775);
        if (! file_exists($file)) {
            $this->error('File not found: '.$file);

            return;
        }

        $this->importDatabase($file);
        $this->info('Database import completed successfully.');
    }

    private function importDatabase(string $file): void
    {
        $dumpCommand = $this->getDumpCommand();
        $sslOption = $dumpCommand === 'mysqldump' ? '--ssl-mode=DISABLED' : '--ssl-verify-server-cert=OFF';
        $authOption = '--default-auth=mysql_native_password';

        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s --port=%s --protocol=TCP %s %s %s < %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            env('DB_PORT'),
            env('DB_DATABASE'),
            $sslOption,
            $authOption,
            $file
        );

        $this->info('Executing command: '.$command);

        exec($command);
    }

    private function getDumpCommand(): string
    {
        if (shell_exec('command -v mariadb-dump')) {
            return 'mariadb-dump';
        }

        return 'mysqldump';
    }
}
