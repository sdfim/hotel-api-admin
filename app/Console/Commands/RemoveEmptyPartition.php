<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveEmptyPartition extends Command
{
    /**
     * Execute the console command.
     */
    protected $signature = 'partition:check-and-drop-all {table}';
    protected $description = 'Check if MySQL partitions in a table are empty and drop them if empty.';

    public function handle()
    {
        $table = $this->argument('table');

        // Get a list of partitions for the specified table
        $partitions = $this->getPartitions($table);

        foreach ($partitions as $partition) {
            // Run a SQL query to count the rows in the partition
            $rowCount = DB::table($table)
                ->partition($partition)
                ->count();

            if ($rowCount === 0) {
                // The partition is empty, so we can drop it
                DB::statement("ALTER TABLE $table DROP PARTITION $partition");
                $this->info("The partition '$partition' in table '$table' was empty and has been dropped.");
            } else {
                $this->info("The partition '$partition' in table '$table' is not empty. It contains $rowCount rows.");
            }
        }
    }

    private function getPartitions($table)
    {
        $partitions = [];

        // Fetch a list of partitions for the specified table
        $results = DB::select("SELECT partition_name FROM information_schema.partitions WHERE table_name = ? AND table_schema = DATABASE()", [$table]);
		
        foreach ($results as $result) {
            $partitions[] = $result->PARTITION_NAME;
        }

        return $partitions;
    }
}
