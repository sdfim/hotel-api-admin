<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTask extends Model
{
    protected $fillable = [
        'name',
        'description',
        'command',
        'command_parameters',
        'frequency_type',
        'day_of_week',
        'time',
        'cron_expression',
        'is_active',
    ];

    protected $casts = [
        'command_parameters' => 'array',
        'is_active' => 'boolean',
        'day_of_week' => 'integer',
    ];

    /**
     * Get the formatted command with parameters for execution
     *
     * @return string
     */
    public function getFullCommandAttribute()
    {
        if (empty($this->command_parameters)) {
            return $this->command;
        }

        // Convert parameters array to command format
        $params = [];
        foreach ($this->command_parameters as $key => $value) {
            if (is_numeric($key)) {
                $params[] = $value;
            } else {
                $params[] = "{$key} {$value}";
            }
        }

        return $this->command . ' ' . implode(' ', $params);
    }
}
