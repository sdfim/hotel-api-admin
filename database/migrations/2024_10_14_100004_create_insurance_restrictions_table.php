<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_plan_id')->constrained('insurance_plans')->onDelete('cascade');
            $table->foreignId('restriction_type_id')->constrained('insurance_restriction_types')->onDelete('cascade');
            $table->string('location')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('insurance_restrictions', function (Blueprint $table) {
            $table->dropForeign(['insurance_plan_id']);
            $table->dropForeign(['restriction_type_id']);
        });

        Schema::dropIfExists('insurance_restrictions');
    }
};
