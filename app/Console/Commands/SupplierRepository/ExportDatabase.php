<?php

namespace App\Console\Commands\SupplierRepository;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportDatabase extends Command
{
    protected $signature = 'db:export {prefixes} {tables} {uuid}';

    protected $description = 'Export specified tables from the database with custom settings';

    public function handle(): void
    {
        $prefixes = explode(',', $this->argument('prefixes'));
        $tables = explode(',', $this->argument('tables'));
        $uuid = $this->argument('uuid');

        $allTables = $this->getTables($prefixes, $tables);

        $this->exportTables($allTables);

        \Cache::put('db_export_status_'.$uuid, 'done', 600);

        $this->info('Database export completed successfully.');
    }

    private function getTables(array $prefixes, array $tables): array
    {
        $dbTables = DB::select('SHOW TABLES');
        $tableNames = array_map('current', $dbTables);

        $filteredTables = array_filter($tableNames, function ($table) use ($prefixes, $tables) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($table, $prefix)) {
                    return true;
                }
            }

            return in_array($table, $tables);
        });

        return $filteredTables;
    }

    private function exportTables(array $tables): void
    {
        $tablesList = implode(' ', $tables);
        $dumpCommand = $this->getDumpCommand();
        $sslOption = $dumpCommand === 'mysqldump' ? '--ssl-mode=DISABLED' : '--ssl-verify-server-cert=OFF';
        $authOption = '--default-auth=mysql_native_password';

        $tmpFile = storage_path('app/tmp_db_dump.sql');

        $command = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s --protocol=TCP --add-drop-table --skip-add-locks --disable-keys --extended-insert --quick %s %s %s %s > %s',
            $dumpCommand,
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            env('DB_PORT'),
            env('DB_DATABASE'),
            $tablesList,
            $sslOption,
            $authOption,
            $tmpFile
        );

        $this->info('Executing command: '.$command);

        exec($command);

        \Storage::disk(config('filament.default_filesystem_disk', 'public'))->put('dump.sql', file_get_contents($tmpFile));
        unlink($tmpFile);
    }

    private function getDumpCommand(): string
    {
        if (shell_exec('command -v mariadb-dump')) {
            return 'mariadb-dump';
        }

        return 'mysqldump';
    }
}
