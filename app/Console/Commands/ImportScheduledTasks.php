<?php

namespace App\Console\Commands;

use App\Models\ScheduledTask;
use Illuminate\Console\Command;

class ImportScheduledTasks extends Command
{
    protected $signature = 'tasks:import';
    protected $description = 'Import predefined scheduled tasks into the database';

    public function handle()
    {
        $this->info('Importing scheduled tasks to database...');

        // Define the initial tasks that were in console.php
        $tasks = [
            [
                'name' => 'Fetch Expedia Properties',
                'description' => 'Content download archive, unzip, parse json, write to DB',
                'command' => 'download-expedia-data',
                'command_parameters' => ['content', '12345'],
                'frequency_type' => 'weekly',
                'day_of_week' => 0, // Sunday
                'time' => '01:00',
                'is_active' => true,
            ],
            [
                'name' => 'Fetch Hilton Properties',
                'description' => 'Hilton Content download to DB',
                'command' => 'hilton:fetch-properties',
                'command_parameters' => ['--limit' => 50],
                'frequency_type' => 'weekly',
                'day_of_week' => 6, // Saturday
                'time' => '05:00',
                'is_active' => true,
            ],
            [
                'name' => 'Fetch IcePortal Properties',
                'description' => 'Download IcePortal data to DB',
                'command' => 'download-iceportal-data',
                'command_parameters' => [],
                'frequency_type' => 'weekly',
                'day_of_week' => 4, // Thursday
                'time' => '01:00',
                'is_active' => true,
            ],
            [
                'name' => 'Download Giata Data',
                'description' => 'Download and process Giata data including Mapping Expedia, HBSI, IcePortal',
                'command' => 'download-giata-data',
                'command_parameters' => [],
                'frequency_type' => 'weekly',
                'day_of_week' => 4, // Thursday
                'time' => '04:00',
                'is_active' => true,
            ],
        ];

        $count = 0;
        foreach ($tasks as $task) {
            // Skip if the task already exists with the same name
            if (ScheduledTask::where('name', $task['name'])->exists()) {
                $this->warn("Task '{$task['name']}' already exists - skipped.");
                continue;
            }

            ScheduledTask::create($task);
            $this->info("Imported: {$task['name']}");
            $count++;
        }

        $this->info("Done! Imported {$count} tasks.");

        return Command::SUCCESS;
    }
}
