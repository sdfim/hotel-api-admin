<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('command');
            $table->json('command_parameters')->nullable();
            $table->string('frequency_type')->default('weekly');
            $table->integer('day_of_week')->nullable()->comment('0-6 for weekly schedules (Sun-Sat)');
            $table->string('time')->comment('Format: HH:MM in 24h format');
            $table->string('cron_expression')->nullable()->comment('For custom cron schedules');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scheduled_tasks');
    }
};
