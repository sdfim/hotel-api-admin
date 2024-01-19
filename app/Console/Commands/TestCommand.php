<?php

namespace App\Console\Commands;

use App\Models\ExpediaContent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle(): void
    {
        $currentTime = Carbon::now('UTC');
        $this->info('test-command successful, UTC: ' . $currentTime);

        $expediaProperty = json_encode(ExpediaContent::select('name', 'property_id')->first());
        $this->info('test-command successful, ujv_api, Expedia: ' . $expediaProperty);

        $userName = json_encode(User::select('name')->first());
        $this->info('test-command successful, ujv, User: ' . $userName);
    }
}
