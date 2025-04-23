<?php

namespace App\Console\Commands\SupplierRepository;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshTables extends Command
{
    protected $signature = 'db:refresh-tables {prefix?}';

    protected $description = 'Refreshes the tables in the hotel content repository / supplier repository';

    public function handle()
    {
        $prefix = $this->argument('prefix');

        if (isset($prefix) && $prefix === 'pd') {
            $this->clearTables('pd_');
        }

        if (isset($prefix) && $prefix === 'config') {
            $this->clearTables('config_');
        }
        if (isset($prefix) && $prefix === 'insurance') {
            $this->clearTables('insurance_');
        }

        // Run the migrate command
        Artisan::call('migrate');
        $this->info('Migration completed.');

        // Run the db:seed command
        Artisan::call('db:seed');
        $this->info('Seeding completed.');

        if (isset($prefix) && $prefix === 'seed') {
            Artisan::call('transform:expedia-to-hotels');
            $this->info('Seeding expedia-to-hotels');
        }
    }

    protected function clearTables($prefix)
    {
        $this->info('Clearing tables with prefix '.$prefix);
        $connection = DB::connection(env('DB_CONNECTION', 'mysql'));

        // Get all tables with the prefix
        $tables = $connection->select("SHOW TABLES LIKE '".$prefix."%'");

        // Save table names in reverse order
        $tableNames = array_reverse(array_map(function ($table) {
            return array_values((array) $table)[0];
        }, $tables));

        if (! empty($tableNames)) {

            // Disable foreign key checking
            $connection->statement('SET FOREIGN_KEY_CHECKS=0');

            // Delete migration records associated with tables with the prefix
            $migrationsDeleted = DB::table('migrations')
                ->where('migration', 'LIKE', '%'.$prefix.'%')
                ->whereNot('migration', 'LIKE', '%general_configurations%')
                ->delete();

            $this->info("Deleted migration records: $migrationsDeleted");

            // Delete tables in reverse order
            foreach ($tableNames as $tableName) {
                Schema::connection(env('DB_CONNECTION', 'mysql'))->dropIfExists($tableName);
                $this->info("Table $tableName has been dropped.");
            }

            // Enable foreign key checking back
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info('All tables with the prefix '.$prefix.' have been successfully deleted.');
        } else {
            $this->info('No tables with prefix '.$prefix.' were found.');
        }
    }
}
