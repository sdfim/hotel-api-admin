<?php

namespace App\Console\Commands\HotelContentRepository;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshTables extends Command
{
    protected $signature = 'db:refresh-pd-tables {prefix?}';
    protected $description = 'Refreshes the tables in the hotel content repository';

    public function handle()
    {
        $prefix = $this->argument('prefix');

        // Clear the tables LIKE 'pd_%'
        $this->clearTables('pd_');
        if (isset($prefix) && $prefix === 'config') $this->clearTables('config_');

        // Run the migrate command
        Artisan::call('migrate');
        $this->info('Migration completed.');

        // Run the db:seed command
        Artisan::call('db:seed');
        $this->info('Seeding completed.');

        Artisan::call('transform:expedia-to-hotels');
        $this->info('Seeding expedia-to-hotels');
    }

    protected function clearTables($prefix)
    {
        $this->info('Clearing tables with prefix '.$prefix);
        $connection = DB::connection(env('DB_CONNECTION', 'mysql'));

        // Получаем все таблицы с префиксом
        $tables = $connection->select("SHOW TABLES LIKE '".$prefix."%'");

        // Сохраняем имена таблиц в обратном порядке
        $tableNames = array_reverse(array_map(function($table) {
            return array_values((array)$table)[0];
        }, $tables));

        if (!empty($tableNames)) {

            // Отключаем проверку внешних ключей
            $connection->statement('SET FOREIGN_KEY_CHECKS=0');

            // Удаляем записи о миграциях, связанных с таблицами префиксом
            $migrationsDeleted = DB::table('migrations')
                ->where('migration', 'LIKE', '%'.$prefix.'%')
                ->whereNot('migration', 'LIKE', '%general_configurations%')
                ->delete();

            $this->info("Удалено записей о миграциях: $migrationsDeleted");

            // Удаляем таблицы в обратном порядке
            foreach ($tableNames as $tableName) {
                Schema::connection(env('DB_CONNECTION', 'mysql'))->dropIfExists($tableName);
                $this->info("Table $tableName has been dropped.");
            }

            // Включаем проверку внешних ключей обратно
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info('Все таблицы с префиксом '.$prefix.' были успешно удалены.');
        } else {
            $this->info('Таблиц с префиксом '.$prefix.' не найдено.');
        }
    }
}
