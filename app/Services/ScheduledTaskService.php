<?php

namespace App\Services;

use App\Models\ScheduledTask;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Schedule as ScheduleFacade;

class ScheduledTaskService
{
    /**
     * Register all active scheduled tasks from the database
     *
     * @param  mixed  $schedule
     * @return void
     */
    public function registerTasks($schedule)
    {
        // Skip if the table doesn't exist yet (e.g., during migrations)
        if (! $this->tableExists('scheduled_tasks')) {
            return;
        }

        // Get all active tasks
        $tasks = ScheduledTask::where('is_active', true)->get();

        foreach ($tasks as $task) {
            $this->scheduleTask($schedule, $task);
        }
    }

    /**
     * Schedule an individual task based on its configuration
     *
     * @param  mixed  $schedule
     * @param  ScheduledTask  $task
     * @return void
     */
    protected function scheduleTask($schedule, ScheduledTask $task)
    {
        // Parse command and parameters
        $commandParts = explode(' ', $task->command, 2);
        $commandName = $commandParts[0];

        $parameters = [];
        if ($task->command_parameters) {
            $parameters = $task->command_parameters;
        }

        // Handle different schedule types/instances
        if ($schedule instanceof Schedule) {
            // Direct Schedule instance
            $event = $schedule->command($commandName, $parameters);
        } else {
            // Using app() to get the container instance of the Schedule
            $event = app(Schedule::class)->command($commandName, $parameters);
        }

        // Apply the scheduling frequency
        switch ($task->frequency_type) {
            case 'weekly':
                $event->weeklyOn($task->day_of_week, $task->time);
                break;
            case 'daily':
                $event->dailyAt($task->time);
                break;
            case 'hourly':
                $event->hourly();
                break;
            case 'custom':
                $event->cron($task->cron_expression);
                break;
        }
    }

    /**
     * Check if a table exists in the database
     *
     * @param  string  $tableName
     * @return bool
     */
    protected function tableExists($tableName)
    {
        return \Schema::hasTable($tableName);
    }
}
