<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Insurance\Seeders\TripMateDefaultRestrictions;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_restrictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('restriction_type_id');
            $table->string('compare')->nullable(); // Nullable if needed
            $table->string('value')->nullable(); // Nullable if needed
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('insurance_providers')->onDelete('cascade');
            $table->foreign('restriction_type_id')->references('id')->on('insurance_restriction_types')->onDelete('cascade');
        });

        (new TripMateDefaultRestrictions())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_restrictions');
    }
};
