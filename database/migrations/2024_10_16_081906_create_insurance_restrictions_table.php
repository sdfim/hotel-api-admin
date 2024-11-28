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
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('restriction_type_id');
            $table->string('compare')->nullable(); // Nullable if needed
            $table->string('value')->nullable(); // Nullable if needed
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('pd_vendors')->onDelete('cascade');
            $table->foreign('restriction_type_id')->references('id')->on('insurance_restriction_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_restrictions');
    }
};
