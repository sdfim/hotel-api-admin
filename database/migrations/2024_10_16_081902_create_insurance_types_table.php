<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('commission', 8, 2)->nullable();
            $table->string('sale_type')->nullable();
            $table->json('benefits');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_types');
    }
};
