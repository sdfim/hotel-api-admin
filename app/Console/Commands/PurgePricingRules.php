<?php

namespace App\Console\Commands;

use App\Models\PricingRule;
use Illuminate\Console\Command;

class PurgePricingRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purge-pricing-rules';

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
        $now = date('Y-m-d H:i:s');
        PricingRule::where('rule_expiration_date', '<', $now)->delete();
    }
}
