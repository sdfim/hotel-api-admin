<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the insurance provider
            $table->text('contact_info')->nullable(); // Contact information of the provider
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_providers');
    }
};
