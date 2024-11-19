<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_provider_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('insurance_providers')->onDelete('cascade');
            $table->string('document_type');
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_provider_documentations');
    }
};
