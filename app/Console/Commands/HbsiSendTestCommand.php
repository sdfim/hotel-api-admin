<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;

class HbsiSendTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hbsi-send-test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make HBSI XML requests';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() : void
    {
        $hbsiClient = new HbsiClient();
        $response = $hbsiClient->price([]);

        $this->info('Response: ' . $response . ' ' . gettype($response));
    }
}
