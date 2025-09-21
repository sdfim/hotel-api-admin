<?php

// app/Jobs/ExportDatabaseJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class ExportDatabaseJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $prefixes;
    public $tables;
    public $uuid;

    public function __construct($prefixes, $tables, $uuid)
    {
        $this->prefixes = $prefixes;
        $this->tables = $tables;
        $this->uuid = $uuid;
    }

    public function handle()
    {
        Artisan::call('db:export', [
            'prefixes' => $this->prefixes,
            'tables' => $this->tables,
            'uuid' => $this->uuid,
        ]);
    }
}
