<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait RefreshDatabaseMany
{
    use RefreshDatabase {
        RefreshDatabase::refreshDatabase as refreshTestDatabaseOriginal;
    }

    protected bool $useTransactions = true;

    /**
     * @return void
     */
    protected function refreshTestDatabase()
    {
        if (! RefreshDatabaseState::$migrated) {

            $this->fresh(config('database.active_connections.mysql_cache'));

            $this->artisan('migrate:fresh', $this->migrateFreshUsing());

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        }

        if ($this->useTransactions) {
            $this->beginDatabaseTransaction();
        }
    }

    private function fresh($connection)
    {
        Schema::connection($connection)->dropAllTables();

        DB::connection($connection)->getSchemaBuilder()->create('migrations', function ($table) {
            $table->string('migration');
        });
    }
}
