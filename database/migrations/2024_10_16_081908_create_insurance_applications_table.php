<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_plan_id')->constrained('insurance_plans')->onDelete('cascade');
            $table->integer('room_number');
            $table->string('name');
            $table->string('location');
            $table->integer('age');
            $table->decimal('total_insurance_cost_pp', 10); // Cost per passenger
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('insurance_applications', function (Blueprint $table) {
            $table->dropForeign(['insurance_plan_id']);
        });

        Schema::dropIfExists('insurance_applications');
    }
};
