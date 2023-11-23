<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('general_configurations', function (Blueprint $table) {
            $table->id();
            $table->integer('time_supplier_requests');
            $table->integer('time_reservations_kept');
            $table->json('currently_suppliers');
            $table->integer('time_inspector_retained');
            $table->float('star_ratings', 4, 2);
            $table->integer('stop_bookings');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_configurations');
    }
};
